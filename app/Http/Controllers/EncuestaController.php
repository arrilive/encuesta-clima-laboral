<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Encuesta;
use Illuminate\Http\Request;
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
}
