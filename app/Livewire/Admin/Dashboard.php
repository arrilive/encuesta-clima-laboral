<?php

namespace App\Livewire\Admin;

use App\Models\Empresa;
use App\Models\Encuesta;
use App\Models\Respuesta;
use App\Services\ClimaScoringService;
use Livewire\Attributes\Layout;
use Livewire\Component;

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
            'estado' => 'disponible',
            'fecha_asignacion' => null,
        ]);
    }

    public function render(ClimaScoringService $scoring)
    {
        $user = auth()->user();

        $base = Encuesta::when(
            $user->role === 'admin_empresa',
            fn ($q) => $q->where('empresa_id', $user->empresa_id)
        );

        $kpis = $this->calcularKpis($base);
        $clima = $user->role === 'admin_empresa' ? $this->calcularClima($scoring, $user->empresa_id) : [];
        $rankingEmpresas = $user->role === 'super_admin' ? $this->calcularRanking($scoring) : collect();

        return view('livewire.admin.dashboard', compact('kpis', 'clima', 'rankingEmpresas'));
    }

    private function calcularKpis($base): array
    {
        $totalTokens = (clone $base)->count();
        $completadas = (clone $base)->where('estado', 'completado')->count();
        $enProgreso = (clone $base)->where('estado', 'en_progreso')->count();
        $asignados = (clone $base)->where('estado', 'asignado')->count();
        $disponibles = (clone $base)->where('estado', 'disponible')->count();
        $enAdvertencia = (clone $base)->enAdvertencia()->count();
        $enRiesgo = (clone $base)->enRiesgo()->count();

        return [
            'total_tokens' => $totalTokens,
            'completadas' => $completadas,
            'en_progreso' => $enProgreso,
            'asignados' => $asignados,
            'disponibles' => $disponibles,
            'en_advertencia' => $enAdvertencia,
            'en_riesgo' => $enRiesgo,
            'tasa_participacion' => $totalTokens > 0 ? round($completadas / $totalTokens * 100, 1) : 0.0,
            'alerta_tokens' => $totalTokens === 0 || ($disponibles / $totalTokens) < 0.10,
        ];
    }

    private function calcularClima(ClimaScoringService $scoring, int $empresaId): array
    {
        $respuestasBase = Respuesta::query()
            ->whereHas('encuesta', fn ($q) => $q
                ->where('estado', 'completado')
                ->where('empresa_id', $empresaId)
            );

        $scoresDimensiones = $scoring->scoresPorDimension($respuestasBase);
        $scoresSubdimensiones = $scoring->scoresPorSubdimension($respuestasBase);

        return [
            'promedio_general' => $scoring->promedioGeneral($respuestasBase),
            'dimension_alta' => $scoresDimensiones->sortByDesc('puntaje')->first(),
            'dimension_baja' => $scoresDimensiones->sortBy('puntaje')->first(),
            'subdimension_alta' => $scoresSubdimensiones->sortByDesc('puntaje')->first(),
            'subdimension_baja' => $scoresSubdimensiones->sortBy('puntaje')->first(),
        ];
    }

    private function calcularRanking(ClimaScoringService $scoring): \Illuminate\Support\Collection
    {
        return Empresa::orderBy('nombre')->get()
            ->map(function ($empresa) use ($scoring) {
                $base = Respuesta::query()
                    ->whereHas('encuesta', fn ($q) => $q
                        ->where('estado', 'completado')
                        ->where('empresa_id', $empresa->id)
                    );

                return [
                    'nombre' => $empresa->nombre,
                    'puntaje' => $scoring->promedioGeneral($base),
                ];
            })
            ->sortByDesc('puntaje')
            ->values();
    }
}
