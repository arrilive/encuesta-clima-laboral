<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Encuesta;
use Illuminate\Http\Request;
use App\Models\Dimension;
use App\Models\Pregunta;
use App\Models\Respuesta;
use App\Models\DatoDemografico;
use Illuminate\Support\Facades\Hash;

class EncuestaController extends Controller
{
    public function bienvenida()
    {
        return view('encuesta.bienvenida');
    }

    public function acceso(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $empresa = Empresa::where('activa', true)
            ->get()
            ->first(fn ($e) => Hash::check($request->password, $e->password));

        if (! $empresa) {
            return back()->withErrors(['password' => 'Contraseña incorrecta.']);
        }

        // Guardamos empresa en sesión para usarla en generar()
        session(['empresa_id' => $empresa->id]);

        return redirect()->route('encuesta.mostrar-acceso');
    }

    // Muestra la pantalla de elección (continuar con token vs generar nuevo)
    public function mostrarAcceso()
    {
        return view('encuesta.acceso');
    }

    // Opción A: el participante ya tiene un token y quiere retomarlo
    public function reanudar(Request $request)
    {
        $request->validate(['token' => ['required', 'string']]);

        $encuesta = Encuesta::whereIn('estado', ['asignado', 'en_progreso'])
            ->where('token', $request->token)
            ->where('empresa_id', session('empresa_id'))
            ->first();

        if (! $encuesta) {
            return back()->withErrors(['token' => 'Código no encontrado, favor verificar que sea correcto.']);
        }

        return redirect()->route('encuesta.demograficos', $encuesta->token);
    }

    // Opción B: primera vez — asignar un token nuevo y mostrarlo
    public function generar(Request $request)
    {
        $empresaId = session('empresa_id');

        if (! $empresaId) {
            return redirect()->route('encuesta.bienvenida')
                ->withErrors(['password' => 'Sesión expirada. Vuelve a ingresar.']);
        }

        $encuesta = Encuesta::where('empresa_id', $empresaId)
            ->where('estado', 'disponible')
            ->first();

        if (! $encuesta) {
            return back()->withErrors(['generar' => 'No hay tokens disponibles. Favor de contactar con el administrador.']);
        }

        $encuesta->asignar();

        return view('encuesta.token-asignado', compact('encuesta'));
    }

    public function demograficos(string $token)
    {
        Encuesta::whereIn('estado', ['asignado', 'en_progreso'])
            ->where('token', $token)
            ->firstOrFail();

        return view('encuesta.demografico', compact('token'));
    }

    public function bloque(string $token, int $dimension)
    {
        $encuesta = Encuesta::whereIn('estado', ['asignado', 'en_progreso'])
            ->where('token', $token)
            ->firstOrFail();

        if ($dimension < 1 || $dimension > 6) {
            abort(404);
        }

        // Verificar que demográficos estén completos
        $tieneDemograficos = DatoDemografico::where('encuesta_id', $encuesta->id)->exists();
        if (! $tieneDemograficos) {
            return redirect()->route('encuesta.mostrar-acceso');
        }

        // Verificar orden secuencial — no puede adelantarse
        $ultimaCompletada = $this->ultimaDimensionCompletada($encuesta);
        if ($dimension > $ultimaCompletada + 1) {
            return redirect()->route('encuesta.dimensiones', $token);
        }

        // Marcar como en_progreso si aún está asignado
        if ($encuesta->estado === 'asignado') {
            $encuesta->marcarEnProgreso();
        }

        return view('encuesta.bloque', compact('token', 'dimension'));
    }

    private function ultimaDimensionCompletada(Encuesta $encuesta): int
    {
        $dimensiones = Dimension::with('subdimensiones')->orderBy('orden')->get();
        $ultima = 0;

        foreach ($dimensiones as $dim) {
            $total = Pregunta::whereHas('subdimension', fn ($q) =>
                $q->where('dimension_id', $dim->id)
            )->count();

            $respondidas = Respuesta::whereHas('pregunta.subdimension', fn ($q) =>
                $q->where('dimension_id', $dim->id)
            )->where('encuesta_id', $encuesta->id)->count();

            if ($respondidas >= $total && $total > 0) {
                $ultima = $dim->orden;
            } else {
                break;
            }
        }

        return $ultima;
    }

    public function abiertas(string $token)
    {
        $encuesta = Encuesta::where('estado', 'en_progreso')
            ->where('token', $token)
            ->firstOrFail();

        return view('encuesta.abiertas', compact('token'));
    }

    public function gracias(string $token)
    {
        Encuesta::whereIn('estado', ['en_progreso', 'completado'])
            ->where('token', $token)
            ->firstOrFail();

        return view('encuesta.gracias');
    }

   public function dimensiones(string $token)
    {
        $encuesta = Encuesta::whereIn('estado', ['asignado', 'en_progreso'])
            ->where('token', $token)
            ->firstOrFail();

        $dimensiones = Dimension::with('subdimensiones')->orderBy('orden')->get()->map(function ($dimension) use ($encuesta) {
            $totalPreguntas = Pregunta::whereHas('subdimension', fn ($q) =>
                $q->where('dimension_id', $dimension->id)
            )->count();

            $respondidas = Respuesta::whereHas('pregunta.subdimension', fn ($q) =>
                $q->where('dimension_id', $dimension->id)
            )->where('encuesta_id', $encuesta->id)->count();

            $dimension->total    = $totalPreguntas;
            $dimension->respondidas = $respondidas;
            $dimension->completada  = $respondidas >= $totalPreguntas && $totalPreguntas > 0;

            return $dimension;
        });

        // Calcular cuál dimensión está disponible
        // La primera no completada y cuya anterior sí está completada
        $disponibleOrden = 1;
        foreach ($dimensiones as $dim) {
            if ($dim->completada) {
                $disponibleOrden = $dim->orden + 1;
            } else {
                break;
            }
        }

        return response()
            ->view('encuesta.dimensiones', compact('token', 'dimensiones', 'disponibleOrden'))
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function completado(string $token, int $dimension)
    {
        $encuesta = Encuesta::whereIn('estado', ['asignado', 'en_progreso'])
            ->where('token', $token)
            ->firstOrFail();

        if ($dimension < 1 || $dimension > 6) {
            abort(404);
        }

        $dimensionActual  = Dimension::where('orden', $dimension)->firstOrFail();
        $siguienteDimension = Dimension::where('orden', $dimension + 1)->first();

        return view('encuesta.completado', compact(
            'token',
            'dimension',
            'dimensionActual',
            'siguienteDimension'
        ));
    }
}
