<?php

namespace App\Http\Controllers;

use App\Models\DatoDemografico;
use App\Models\Dimension;
use App\Models\Empresa;
use App\Models\Encuesta;
use App\Models\EncuestaHash;
use App\Models\Lote;
use App\Models\OtpVerificacion;
use App\Models\Pregunta;
use App\Models\Respuesta;
use App\Models\Sucursal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EncuestaController extends Controller
{
    public function bienvenida()
    {
        return view('encuesta.bienvenida');
    }

    // ---------------------------------------------------------------------------
    // Flujo OTP v1.1
    // ---------------------------------------------------------------------------

    public function verificarLlave(Request $request): JsonResponse
    {
        // 1. Validar campo de entrada
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        // 2. Buscar entidad activa cuya llave maestra coincida (sucursal primero, luego empresa)
        $vigenteFilter = function ($query) {
            $query->where('activo', true)
                ->where(fn ($q) => $q->whereNull('fecha_inicio')->orWhereDate('fecha_inicio', '<=', now()))
                ->where(fn ($q) => $q->whereNull('fecha_fin')->orWhereDate('fecha_fin', '>=', now()));
        };

        // Camino rápido: Buscar únicamente entre las que tienen un lote vigente
        $sucursal = Sucursal::where('activa', true)
            ->whereHas('lotes', $vigenteFilter)
            ->select('id', 'empresa_id', 'nombre', 'password')
            ->get()
            ->first(fn ($s) => Hash::check($request->password, $s->password));

        $empresa = $sucursal ? null : Empresa::where('activa', true)
            ->whereHas('lotes', function ($query) use ($vigenteFilter) {
                $query->whereNull('sucursal_id');
                $vigenteFilter($query);
            })
            ->select('id', 'nombre', 'password')
            ->get()
            ->first(fn ($e) => Hash::check($request->password, $e->password));

        if (! $sucursal && ! $empresa) {
            return response()->json(['error' => 'llave_invalida'], 422);
        }

        // 3. Buscar lote activo y vigente para la entidad encontrada
        $query = Lote::where('activo', true)
            ->where(fn ($q) => $q->whereNull('fecha_inicio')->orWhereDate('fecha_inicio', '<=', now()))
            ->where(fn ($q) => $q->whereNull('fecha_fin')->orWhereDate('fecha_fin', '>=', now()));

        if ($sucursal) {
            $query->where('sucursal_id', $sucursal->id);
        } else {
            $query->where('empresa_id', $empresa->id)
                ->whereNull('sucursal_id');
        }

        $lote = $query->first();

        if (! $lote) {
            return response()->json(['error' => 'sin_lote_activo'], 422);
        }

        // 4. Devolver lote_id y nombre de la entidad al frontend
        return response()->json([
            'status' => 'llave_valida',
            'lote_id' => $lote->id,
            'nombre_entidad' => $sucursal?->nombre ?? $empresa->nombre,
        ]);
    }

    public function solicitarOtp(Request $request): JsonResponse
    {
        // 1. Validar campos de entrada
        $request->validate([
            'numero_e164' => ['required', 'string', 'regex:/^\+[0-9]+$/'],
            'lote_id' => ['required', 'integer', 'exists:lotes,id'],
        ]);

        $numero_e164 = $request->numero_e164;
        $lote_id = $request->lote_id;

        // 2. Buscar entidad dueña del lote (sucursal primero, luego empresa)
        //    y verificar que el lote esté activo y vigente.
        $lote = null;
        $empresa_id = null;

        $sucursal = Sucursal::where('activa', true)
            ->whereHas('lotes', fn ($q) => $q
                ->where('id', $lote_id)
                ->where('activo', true)
                ->where(fn ($q2) => $q2->whereNull('fecha_inicio')->orWhereDate('fecha_inicio', '<=', now()))
                ->where(fn ($q2) => $q2->whereNull('fecha_fin')->orWhereDate('fecha_fin', '>=', now())))
            ->select('id', 'empresa_id')
            ->first();

        if ($sucursal) {
            $lote = $sucursal->lotes()->where('lotes.id', $lote_id)->first();
            $empresa_id = $sucursal->empresa_id;
        } else {
            $empresa = Empresa::where('activa', true)
                ->whereHas('lotes', fn ($q) => $q
                    ->where('id', $lote_id)
                    ->where('activo', true)
                    ->where(fn ($q2) => $q2->whereNull('fecha_inicio')->orWhereDate('fecha_inicio', '<=', now()))
                    ->where(fn ($q2) => $q2->whereNull('fecha_fin')->orWhereDate('fecha_fin', '>=', now())))
                ->select('id')
                ->first();

            if ($empresa) {
                $lote = $empresa->lotes()->where('lotes.id', $lote_id)->first();
                $empresa_id = $empresa->id;
            }
        }

        if (! $lote) {
            return response()->json(['error' => 'acceso_invalido'], 422);
        }

        // 3. Verificar unicidad por hash de teléfono
        $hash_phone = hash('sha256', $numero_e164.$lote->id.config('app.phone_hash_salt'));

        if (EncuestaHash::where('phone_hash', $hash_phone)->where('lote_id', $lote->id)->exists()) {
            return response()->json(['error' => 'ya_participaste'], 422);
        }

        // 4. Eliminar OTPs previos del mismo número en el mismo lote
        OtpVerificacion::where('lote_id', $lote->id)
            ->where('numero_e164', $numero_e164)
            ->delete();

        // 5. Generar y guardar OTP
        $otp = random_int(100000, 999999);

        OtpVerificacion::create([
            'numero_e164' => $numero_e164,
            'otp_hash' => hash('sha256', (string) $otp),
            'lote_id' => $lote->id,
            'empresa_id' => $empresa_id,
            'intentos' => 0,
            'expira_en' => now()->addMinutes(10),
        ]);

        // 6. Simular envío (nunca loggear el número real)
        Log::info('OTP generado', ['lote_id' => $lote->id, 'otp' => $otp]);

        return response()->json(['status' => 'otp_enviado'], 200);
    }

    public function verificarOtp(Request $request): JsonResponse
    {
        // 1. Validar campos de entrada
        $request->validate([
            'numero_e164' => ['required', 'string'],
            'otp' => ['required', 'string', 'digits:6'],
            'lote_id' => ['required', 'integer'],
        ]);

        $numero_e164 = $request->numero_e164;
        $lote_id = $request->lote_id;

        // 2. Buscar registro OTP
        $otpRecord = OtpVerificacion::where('numero_e164', $numero_e164)
            ->where('lote_id', $lote_id)
            ->first();

        if (! $otpRecord) {
            return response()->json(['error' => 'otp_invalido'], 422);
        }

        // 3. Verificar vigencia
        if (! $otpRecord->estaVigente()) {
            $otpRecord->delete();

            return response()->json(['error' => 'otp_expirado'], 422);
        }

        // 4. Verificar intentos agotados
        if ($otpRecord->agotaronIntentos()) {
            return response()->json(['error' => 'intentos_agotados'], 422);
        }

        // 5. Validar el OTP
        if (! hash_equals($otpRecord->otp_hash, hash('sha256', $request->otp))) {
            $otpRecord->intentos++;
            $otpRecord->save();

            return response()->json([
                'error' => 'otp_invalido',
                'intentos_restantes' => 3 - $otpRecord->intentos,
            ], 422);
        }

        // 6. OTP válido — orden estricto
        // 6a. Guardar hash de unicidad
        EncuestaHash::create([
            'phone_hash' => hash('sha256', $numero_e164.$lote_id.config('app.phone_hash_salt')),
            'lote_id' => $lote_id,
        ]);

        // 6b. Eliminar registro OTP
        $otpRecord->delete();

        // 6c. Generar token
        $token = 'TK-'.Str::upper(Str::random(4)).'-'.Str::upper(Str::random(4));

        // 6d. Buscar encuesta disponible
        $encuesta = Encuesta::where('lote_id', $lote_id)
            ->where('estado', 'disponible')
            ->first();

        if (! $encuesta) {
            return response()->json(['error' => 'sin_tokens'], 422);
        }

        // 6e. Asignar token
        $encuesta->update([
            'estado' => 'asignado',
            'fecha_asignacion' => now(),
            'token' => $token,
        ]);

        Log::info('Token asignado', ['lote_id' => $lote_id, 'token' => $token]);

        return response()->json(['status' => 'token_asignado', 'token' => $token], 200);
    }

    private function obtenerEncuestaValida(string $token, array $estados = ['asignado', 'en_progreso']): Encuesta
    {
        return Encuesta::whereIn('estado', $estados)
            ->where('token', $token)
            ->firstOrFail();
    }

    public function demograficos(string $token)
    {
        $this->obtenerEncuestaValida($token, ['asignado', 'en_progreso']);

        return view('encuesta.demografico', compact('token'));
    }

    public function bloque(string $token, int $dimension)
    {
        $encuesta = $this->obtenerEncuestaValida($token, ['asignado', 'en_progreso']);

        if ($dimension < 1 || $dimension > 6) {
            abort(404);
        }

        // Verificar que demográficos estén completos
        $tieneDemograficos = DatoDemografico::where('encuesta_id', $encuesta->id)->exists();
        if (! $tieneDemograficos) {
            return redirect()->route('encuesta.demograficos', $token);
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
            $total = Pregunta::whereHas('subdimension', fn ($q) => $q->where('dimension_id', $dim->id)
            )->count();

            $respondidas = Respuesta::whereHas('pregunta.subdimension', fn ($q) => $q->where('dimension_id', $dim->id)
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
        $encuesta = $this->obtenerEncuestaValida($token, ['en_progreso']);

        if ($this->ultimaDimensionCompletada($encuesta) !== Dimension::count()) {
            return redirect()->route('encuesta.dimensiones', $token);
        }

        return view('encuesta.abiertas', compact('token'));
    }

    public function gracias(string $token)
    {
        $this->obtenerEncuestaValida($token, ['completado']);

        return view('encuesta.gracias');
    }

    public function dimensiones(string $token)
    {
        $encuesta = $this->obtenerEncuestaValida($token, ['asignado', 'en_progreso']);

        if (! DatoDemografico::where('encuesta_id', $encuesta->id)->exists()) {
            return redirect()->route('encuesta.demograficos', $token);
        }

        $dimensiones = Dimension::with('subdimensiones')->orderBy('orden')->get()->map(function ($dimension) use ($encuesta) {
            $totalPreguntas = Pregunta::whereHas('subdimension', fn ($q) => $q->where('dimension_id', $dimension->id)
            )->count();

            $respondidas = Respuesta::whereHas('pregunta.subdimension', fn ($q) => $q->where('dimension_id', $dimension->id)
            )->where('encuesta_id', $encuesta->id)->count();

            $dimension->total = $totalPreguntas;
            $dimension->respondidas = $respondidas;
            $dimension->completada = $respondidas >= $totalPreguntas && $totalPreguntas > 0;

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

        $todasDimensionesCompletas = $disponibleOrden > $dimensiones->count();

        return response()
            ->view('encuesta.dimensiones', compact('token', 'dimensiones', 'disponibleOrden', 'todasDimensionesCompletas'))
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function completado(string $token, int $dimension)
    {
        $encuesta = $this->obtenerEncuestaValida($token, ['asignado', 'en_progreso']);

        if ($dimension < 1 || $dimension > 6) {
            abort(404);
        }

        if ($this->ultimaDimensionCompletada($encuesta) !== $dimension) {
            return redirect()->route('encuesta.dimensiones', $token);
        }

        $dimensionActual = Dimension::where('orden', $dimension)->firstOrFail();
        $siguienteDimension = Dimension::where('orden', $dimension + 1)->first();

        return view('encuesta.completado', compact(
            'token',
            'dimension',
            'dimensionActual',
            'siguienteDimension'
        ));
    }
}
