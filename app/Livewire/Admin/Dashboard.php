<?php

namespace App\Livewire\Admin;

use App\Models\Corporativo;
use App\Models\Empresa;
use App\Models\Encuesta;
use App\Models\Lote;
use App\Models\Respuesta;
use App\Models\Sucursal;
use App\Services\ClimaScoringService;
use App\Traits\HasTenantScope;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin', ['heading' => 'Dashboard'])]

class Dashboard extends Component
{
    use HasTenantScope;

    public string $filtroCorporativoId = '';

    public string $filtroEmpresaId = '';

    public string $filtroSucursalId = '';

    public string $filtroLoteId = '';

    public function updatedFiltroCorporativoId(): void
    {
        $this->filtroEmpresaId = '';
        $this->filtroSucursalId = '';
        $this->filtroLoteId = '';
    }

    public function updatedFiltroEmpresaId(): void
    {
        $this->filtroSucursalId = '';
        $this->filtroLoteId = '';
    }

    public function updatedFiltroSucursalId(): void
    {
        $this->filtroLoteId = '';
    }

    public function getCorporativosProperty()
    {
        $user = auth()->user();
        if ($user->role !== \App\Enums\Role::SUPER_ADMIN->value) {
            return collect();
        }

        return Corporativo::where('activa', true)->orderBy('nombre')->get();
    }

    public function getSucursalesProperty()
    {
        $user = auth()->user();

        if ($user->role === \App\Enums\Role::ADMIN_SUCURSAL->value) {
            return collect();
        }

        if (in_array($user->role, [
            \App\Enums\Role::SUPER_ADMIN->value,
            \App\Enums\Role::ADMIN_CORPORATIVO->value,
        ]) && ! $this->filtroEmpresaId) {
            return collect();
        }

        $empresaId = $this->filtroEmpresaId ?: $user->empresa_id;

        return Sucursal::where('empresa_id', $empresaId)
            ->where('activa', true)
            ->orderBy('nombre')
            ->get();
    }

    public function getLotesProperty()
    {
        $user = auth()->user();

        if (in_array($user->role, [
            \App\Enums\Role::SUPER_ADMIN->value,
            \App\Enums\Role::ADMIN_CORPORATIVO->value,
        ]) && ! $this->filtroEmpresaId) {
            return collect();
        }

        $query = Lote::with('sucursal');
        $query = $this->scopeByRole($query);

        if ($this->filtroEmpresaId) {
            $query->where('empresa_id', $this->filtroEmpresaId);
        }

        if ($this->filtroSucursalId) {
            $query->where('sucursal_id', $this->filtroSucursalId);
        }

        return $query->orderByDesc('fecha_inicio')->get();
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

        if ($this->filtroCorporativoId && $user->role === \App\Enums\Role::SUPER_ADMIN->value) {
            $base->whereHas('lote.empresa', fn ($q) => $q->where('corporativo_id', $this->filtroCorporativoId));
        }

        if ($this->filtroEmpresaId && in_array($user->role, [
            \App\Enums\Role::SUPER_ADMIN->value,
            \App\Enums\Role::ADMIN_CORPORATIVO->value,
        ])) {
            $base->whereHas('lote', fn ($q) => $q->where('empresa_id', $this->filtroEmpresaId));
        }

        if ($this->filtroSucursalId) {
            $base->whereHas('lote', fn ($q) => $q->where('sucursal_id', $this->filtroSucursalId));
        }

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

        return view('livewire.admin.dashboard', [
            'kpis' => $kpis,
            'clima' => $clima,
            'rankingEmpresas' => $rankingEmpresas,
            'lotes' => $lotes,
            'corporativos' => $this->corporativos,
            'sucursales' => $this->sucursales,
            'empresas' => $user->role === \App\Enums\Role::SUPER_ADMIN->value
                ? ($this->filtroCorporativoId
                    ? Empresa::where('corporativo_id', $this->filtroCorporativoId)->orderBy('nombre')->get()
                    : Empresa::orderBy('nombre')->get())
                : ($user->role === \App\Enums\Role::ADMIN_CORPORATIVO->value
                    ? Empresa::where('corporativo_id', $user->corporativo_id)->orderBy('nombre')->get()
                    : collect()),
        ]);
    }

    private function calcularKpis(\Illuminate\Database\Eloquent\Builder $base): array
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

    private function calcularClima(ClimaScoringService $scoring, \App\Models\User $user): array
    {
        $respuestasBase = Respuesta::query()
            ->whereHas('encuesta', fn ($q) => $q
                ->where('estado', 'completado')
                ->whereHas('lote', fn ($loteQuery) => $this->scopeByRole($loteQuery))
            );

        if ($this->filtroLoteId) {
            $respuestasBase->whereHas('encuesta', fn ($q) => $q->where('lote_id', $this->filtroLoteId));
        }

        if ($this->filtroCorporativoId && auth()->user()->role === \App\Enums\Role::SUPER_ADMIN->value) {
            $respuestasBase->whereHas('encuesta.lote.empresa', fn ($q) => $q->where('corporativo_id', $this->filtroCorporativoId));
        }

        if ($this->filtroEmpresaId) {
            $sucursalIds = $this->sucursalIdsDeEmpresa((int) $this->filtroEmpresaId);
            $respuestasBase->whereHas('encuesta.lote', function ($q) use ($sucursalIds) {
                $q->where('empresa_id', $this->filtroEmpresaId)
                    ->orWhereIn('sucursal_id', $sucursalIds);
            });
        }

        if ($this->filtroSucursalId) {
            $respuestasBase->whereHas('encuesta.lote', fn ($q) => $q->where('sucursal_id', $this->filtroSucursalId));
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
     */
    private function calcularRanking(ClimaScoringService $scoring): \Illuminate\Support\Collection
    {
        $user = auth()->user();

        $empresas = Empresa::orderBy('nombre')
            ->when(
                $user->role === \App\Enums\Role::ADMIN_CORPORATIVO->value,
                fn ($q) => $q->where('corporativo_id', $user->corporativo_id)
            )
            ->get();

        $promedios = $scoring->promediosGeneralesPorEmpresas($empresas->pluck('id')->toArray());

        return $empresas
            ->map(fn ($empresa) => [
                'nombre' => $empresa->nombre,
                'puntaje' => $promedios->get($empresa->id, 0.0),
            ])
            ->sortByDesc('puntaje')
            ->values();
    }
}
