<?php

namespace App\Livewire\Admin;

use App\Models\Empresa;
use App\Models\Encuesta;
use App\Models\Respuesta;
use App\Services\ClimaScoringService;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.admin', ['heading' => 'Dashboard'])]


class Dashboard extends Component
{
    public function liberarTokens(): void
    {
        $user = auth()->user();

        $query = Encuesta::enRiesgo();

        if ($user->role === 'admin_empresa') {
            $query->where('empresa_id', $user->empresa_id);
        }

        $query->update([
            'estado'           => 'disponible',
            'fecha_asignacion' => null,
        ]);
    }

    public function render(ClimaScoringService $scoring)
    {
        $user = auth()->user();

        $base = Encuesta::when(
            $user->role === 'admin_empresa',
            fn($q) => $q->where('empresa_id', $user->empresa_id)
        );

        // ── KPIs operativos ───────────────────────────────────────────────
        $totalTokens = (clone $base)->count();
        $completadas = (clone $base)->where('estado', 'completado')->count();
        $enProgreso  = (clone $base)->where('estado', 'en_progreso')->count();
        $asignados   = (clone $base)->where('estado', 'asignado')->count();
        $disponibles = (clone $base)->where('estado', 'disponible')->count();
        $enAdvertencia = (clone $base)->enAdvertencia()->count();
        $enRiesgo    = (clone $base)->enRiesgo()->count();

        $tasaParticipacion = $totalTokens > 0
            ? round($completadas / $totalTokens * 100, 1)
            : 0.0;

        $alertaTokens = $totalTokens === 0 || ($disponibles / $totalTokens) < 0.10;

        $kpis = [
            'total_tokens'       => $totalTokens,
            'completadas'        => $completadas,
            'en_progreso'        => $enProgreso,
            'asignados'          => $asignados,
            'disponibles'        => $disponibles,
            'en_advertencia'     => $enAdvertencia,
            'en_riesgo'          => $enRiesgo,
            'tasa_participacion' => $tasaParticipacion,
            'alerta_tokens'      => $alertaTokens,
        ];

        // ── Widget de clima (solo admin_empresa) ──────────────────────────
        $clima = [];
        if ($user->role === 'admin_empresa') {
            $respuestasBase = Respuesta::query()
                ->whereHas('encuesta', fn($q) => $q
                    ->where('estado', 'completado')
                    ->where('empresa_id', $user->empresa_id)
                );

            $scoresDimensiones    = $scoring->scoresPorDimension($respuestasBase);
            $scoresSubdimensiones = $scoring->scoresPorSubdimension($respuestasBase);

            $clima = [
                'promedio_general'  => $scoring->promedioGeneral($respuestasBase),
                'dimension_alta'    => $scoresDimensiones->sortByDesc('puntaje')->first(),
                'dimension_baja'    => $scoresDimensiones->sortBy('puntaje')->first(),
                'subdimension_alta' => $scoresSubdimensiones->sortByDesc('puntaje')->first(),
                'subdimension_baja' => $scoresSubdimensiones->sortBy('puntaje')->first(),
            ];
        }

        // ── Ranking de empresas (solo super_admin) ────────────────────────
        $rankingEmpresas = collect();
        if ($user->role === 'super_admin') {
            $rankingEmpresas = Empresa::orderBy('nombre')->get()
                ->map(function ($empresa) use ($scoring) {
                    $base = Respuesta::query()
                        ->whereHas('encuesta', fn($q) => $q
                            ->where('estado', 'completado')
                            ->where('empresa_id', $empresa->id)
                        );
                    return [
                        'nombre'  => $empresa->nombre,
                        'puntaje' => $scoring->promedioGeneral($base),
                    ];
                })
                ->sortByDesc('puntaje')
                ->values();
        }

        return view('livewire.admin.dashboard', compact('kpis', 'clima', 'rankingEmpresas'));
    }
}
