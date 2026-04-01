<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Encuesta;
use App\Models\Respuesta;
use App\Services\ClimaScoringService;

class DashboardController extends Controller
{
    public function index()
    {
        $user    = auth()->user();
        $scoring = app(ClimaScoringService::class);

        // Query base scoped por rol — solo encuestas de la empresa si es admin_empresa
        $base = Encuesta::when(
            $user->role === 'admin_empresa',
            fn($q) => $q->where('empresa_id', $user->empresa_id)
        );

        // ── KPIs operativos ───────────────────────────────────────────────
        $totalTokens  = (clone $base)->count();
        $completadas  = (clone $base)->where('estado', 'completado')->count();
        $enProgreso   = (clone $base)->where('estado', 'en_progreso')->count();
        $asignados    = (clone $base)->where('estado', 'asignado')->count();
        $disponibles  = (clone $base)->where('estado', 'disponible')->count();
        $enRiesgo     = (clone $base)->enRiesgo()->count();

        $tasaParticipacion = $totalTokens > 0
            ? round($completadas / $totalTokens * 100, 1)
            : 0.0;

        $alertaTokens = $totalTokens > 0 && ($disponibles / $totalTokens) < 0.10;

        $kpis = [
            'total_tokens'       => $totalTokens,
            'completadas'        => $completadas,
            'en_progreso'        => $enProgreso,
            'asignados'          => $asignados,
            'disponibles'        => $disponibles,
            'en_riesgo'          => $enRiesgo,
            'tasa_participacion' => $tasaParticipacion,
            'alerta_tokens'      => $alertaTokens,
        ];

        // ── Widget de clima (solo admin_empresa) ───────────────────────────
        $clima = [];
        if ($user->role === 'admin_empresa') {
            // Base query para scoring: solo respuestas de encuestas completadas
            $respuestasBase = Respuesta::query()
                ->whereHas('encuesta', fn($q) => $q
                    ->where('estado', 'completado')
                    ->where('empresa_id', $user->empresa_id)
                );

            $promedioGeneral       = $scoring->promedioGeneral($respuestasBase);
            $scoresDimensiones     = $scoring->scoresPorDimension($respuestasBase);
            $scoresSubdimensiones  = $scoring->scoresPorSubdimension($respuestasBase);

            $dimensionAlta    = $scoresDimensiones->sortByDesc('puntaje')->first();
            $dimensionBaja    = $scoresDimensiones->sortBy('puntaje')->first();
            $subdimensionAlta = $scoresSubdimensiones->sortByDesc('puntaje')->first();
            $subdimensionBaja = $scoresSubdimensiones->sortBy('puntaje')->first();

            $clima = [
                'promedio_general'  => $promedioGeneral,
                'dimension_alta'    => $dimensionAlta,
                'dimension_baja'    => $dimensionBaja,
                'subdimension_alta' => $subdimensionAlta,
                'subdimension_baja' => $subdimensionBaja,
            ];
        }

        // ── Ranking de empresas (solo super_admin) ────────────────────────
        $rankingEmpresas = collect();
        if ($user->role === 'super_admin') {
            $rankingEmpresas = Empresa::orderBy('nombre')->get()->map(function ($empresa) use ($scoring) {
                $base = Respuesta::query()
                    ->whereHas('encuesta', fn($q) => $q
                        ->where('estado', 'completado')
                        ->where('empresa_id', $empresa->id)
                    );
                return [
                    'nombre'  => $empresa->nombre,
                    'puntaje' => $scoring->promedioGeneral($base),
                ];
            })->sortByDesc('puntaje')->values();
        }

        return view('admin.dashboard', compact('kpis', 'clima', 'rankingEmpresas'));
    }
}
