<?php

namespace App\Livewire\Admin;

use App\Models\Empresa;
use App\Models\Encuesta;
use App\Models\Lote;
use App\Models\Respuesta;
use App\Services\ClimaScoringService;
use App\Traits\HasTenantScope;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin', ['heading' => 'Dashboard'])]

class Dashboard extends Component
{
    use HasTenantScope;

    public string $filtroLoteId = '';

    public function getLotesProperty()
    {
        $query = Lote::with('sucursal');

        return $this->scopeByRole($query)->orderByDesc('fecha_inicio')->get();
    }

    public function liberarTokens(): void
    {
        $query = Encuesta::enRiesgo();

        $query->whereHas('lote', fn ($q) => $this->scopeByRole($q));

        $query->update([
            'estado' => 'disponible',
            'fecha_asignacion' => null,
        ]);
    }

    public function render(ClimaScoringService $scoring)
    {
        $user = auth()->user();

        $base = Encuesta::when(
            in_array($user->role, [
                \App\Enums\Role::ADMIN_EMPRESA->value,
                \App\Enums\Role::ADMIN_CORPORATIVO->value,
                \App\Enums\Role::ADMIN_SUCURSAL->value,
            ]),
            fn ($q) => $q->whereHas('lote', fn ($loteQuery) => $this->scopeByRole($loteQuery))
        );

        if ($this->filtroLoteId) {
            $base->where('lote_id', $this->filtroLoteId);
        }

        $kpis = $this->calcularKpis($base);
        $clima = in_array($user->role, [
            \App\Enums\Role::ADMIN_EMPRESA->value,
            \App\Enums\Role::ADMIN_CORPORATIVO->value,
            \App\Enums\Role::ADMIN_SUCURSAL->value,
        ]) ? $this->calcularClima($scoring, $user) : [];
        $rankingEmpresas = in_array($user->role, [
            \App\Enums\Role::SUPER_ADMIN->value,
            \App\Enums\Role::ADMIN_CORPORATIVO->value,
        ]) ? $this->calcularRanking($scoring) : collect();

        $lotes = $this->lotes;

        return view('livewire.admin.dashboard', compact('kpis', 'clima', 'rankingEmpresas', 'lotes'));
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

    private function calcularClima(ClimaScoringService $scoring, $user): array
    {
        $respuestasBase = Respuesta::query()
            ->whereHas('encuesta', fn ($q) => $q
                ->where('estado', 'completado')
                ->whereHas('lote', fn ($loteQuery) => $this->scopeByRole($loteQuery))
            );

        if ($this->filtroLoteId) {
            $respuestasBase->whereHas('encuesta', fn ($q) => $q->where('lote_id', $this->filtroLoteId));
        }

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

    /**
     * Calcula el ranking de empresas por promedio de clima laboral.
     *
     * @warning Este método produce N+1 queries complejas — una por empresa registrada.
     * Es aceptable con volúmenes pequeños de tenants (<20 empresas). Si el volumen
     * escala, debe rediseñarse con caché (Redis) o procesamiento en segundo plano.
     * Ver backlog: optimización de calcularRanking() post-v1.2.0.
     */
    private function calcularRanking(ClimaScoringService $scoring): \Illuminate\Support\Collection
    {
        $user = auth()->user();

        $empresas = Empresa::orderBy('nombre')
            ->when($user->role === \App\Enums\Role::ADMIN_CORPORATIVO->value, fn ($q) => $q->where('corporativo_id', $user->corporativo_id))
            ->get();

        return $empresas
            ->map(function ($empresa) use ($scoring) {
                $base = Respuesta::query()
                    ->whereHas('encuesta', fn ($q) => $q
                        ->where('estado', 'completado')
                        ->whereHas('lote', fn ($q) => $q->where('empresa_id', $empresa->id))
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
