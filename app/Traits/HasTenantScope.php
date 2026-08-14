<?php

namespace App\Traits;

use App\Enums\Role;
use App\Models\Lote;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

trait HasTenantScope
{
    protected function sucursalIdsDeEmpresa(int $empresaId): array
    {
        return \App\Models\Sucursal::where('empresa_id', $empresaId)->pluck('id')->toArray();
    }

    private function scopeByRole(Builder $query): Builder
    {
        $user = auth()->user();

        if (! $user) {
            return $query;
        }

        return match (Role::tryFrom($user->role)) {
            Role::SUPER_ADMIN => $query,
            Role::ADMIN_CORPORATIVO => $query->whereHas('empresa', fn ($q) => $q->where('corporativo_id', $user->corporativo_id)),
            Role::ADMIN_EMPRESA => $query->where(function ($q) use ($user) {
                $q->where('empresa_id', $user->empresa_id)
                    ->orWhereIn('sucursal_id', $this->sucursalIdsDeEmpresa($user->empresa_id));
            }),
            Role::ADMIN_SUCURSAL => $query->where('sucursal_id', $user->sucursal_id),
            default => $query,
        };
    }

    protected function resolverLoteEstadoActual(): array
    {
        $user = auth()->user();

        // 1. Base query scoped by role
        $query = Lote::query();
        $query = $this->scopeByRole($query);

        // 2. Apply filters from consuming component if set
        if (! empty($this->filtroCorporativoId) && $user && $user->role === Role::SUPER_ADMIN->value) {
            $query->whereHas('empresa', fn ($q) => $q->where('corporativo_id', $this->filtroCorporativoId));
        }

        if (! empty($this->filtroEmpresaId)) {
            $sucursalIds = $this->sucursalIdsDeEmpresa((int) $this->filtroEmpresaId);
            $query->where(function ($q) use ($sucursalIds) {
                $q->where('empresa_id', $this->filtroEmpresaId)
                    ->orWhereIn('sucursal_id', $sucursalIds);
            });
        }

        if (! empty($this->filtroSucursalId)) {
            $query->where('sucursal_id', $this->filtroSucursalId);
        }

        // If a specific period is selected, we filter by that lote_id
        if (! empty($this->filtroLoteId)) {
            $query->where('id', $this->filtroLoteId);
        }

        // Fetch all lotes in this scope to evaluate scenarios
        $lotes = $query->get();

        return $this->resolverEstadoDesdeLotes($lotes);
    }

    protected function resolverLoteEstadoActualDesdeFiltros(array $filtros): array
    {
        $user = auth()->user();

        // 1. Base query scoped by role
        $query = Lote::query();
        $query = $this->scopeByRole($query);

        // 2. Apply filters from array
        if (! empty($filtros['corporativo_id']) && $user && $user->role === Role::SUPER_ADMIN->value) {
            $query->whereHas('empresa', fn ($q) => $q->where('corporativo_id', $filtros['corporativo_id']));
        }

        if (! empty($filtros['empresa_id'])) {
            $sucursalIds = $this->sucursalIdsDeEmpresa((int) $filtros['empresa_id']);
            $query->where(function ($q) use ($filtros, $sucursalIds) {
                $q->where('empresa_id', $filtros['empresa_id'])
                    ->orWhereIn('sucursal_id', $sucursalIds);
            });
        }

        if (! empty($filtros['sucursal_id'])) {
            $query->where('sucursal_id', $filtros['sucursal_id']);
        }

        if (! empty($filtros['lote_id'])) {
            $query->where('id', $filtros['lote_id']);
        }

        $lotes = $query->get();

        return $this->resolverEstadoDesdeLotes($lotes);
    }

    protected function getEmpresasInScope(?array $filtros = null): \Illuminate\Support\Collection
    {
        $user = auth()->user();
        if (! $user) {
            return collect();
        }

        $corpId = $filtros ? ($filtros['corporativo_id'] ?? null) : ($this->filtroCorporativoId ?? null);
        $empresaId = $filtros ? ($filtros['empresa_id'] ?? null) : ($this->filtroEmpresaId ?? null);
        $sucursalId = $filtros ? ($filtros['sucursal_id'] ?? null) : ($this->filtroSucursalId ?? null);
        $loteId = $filtros ? ($filtros['lote_id'] ?? null) : ($this->filtroLoteId ?? null);

        if (! empty($loteId)) {
            $lote = Lote::find($loteId);
            if ($lote && $lote->empresa_id) {
                return collect([$lote->empresa_id]);
            }
        }

        if (! empty($sucursalId)) {
            $sucursal = \App\Models\Sucursal::find($sucursalId);
            if ($sucursal && $sucursal->empresa_id) {
                return collect([$sucursal->empresa_id]);
            }
        }

        if (! empty($empresaId)) {
            return collect([(int) $empresaId]);
        }

        if ($user->role === Role::SUPER_ADMIN->value) {
            if (! empty($corpId)) {
                return \App\Models\Empresa::where('corporativo_id', $corpId)->pluck('id');
            }

            return collect();
        }

        if ($user->role === Role::ADMIN_CORPORATIVO->value) {
            if ($user->corporativo_id) {
                return \App\Models\Empresa::where('corporativo_id', $user->corporativo_id)->pluck('id');
            }

            return collect();
        }

        if ($user->role === Role::ADMIN_EMPRESA->value) {
            if ($user->empresa_id) {
                return collect([$user->empresa_id]);
            }

            return collect();
        }

        if ($user->role === Role::ADMIN_SUCURSAL->value) {
            if ($user->sucursal_id) {
                $empId = \App\Models\Sucursal::find($user->sucursal_id)?->empresa_id;
                if ($empId) {
                    return collect([$empId]);
                }
            }

            return collect();
        }

        return collect();
    }

    protected function resolverLotesEstadoActual(): array
    {
        return $this->resolverLotesEstadoActualDesdeFiltros([
            'corporativo_id' => $this->filtroCorporativoId ?? null,
            'empresa_id' => $this->filtroEmpresaId ?? null,
            'sucursal_id' => $this->filtroSucursalId ?? null,
            'lote_id' => $this->filtroLoteId ?? null,
        ]);
    }

    protected function resolverLotesEstadoActualDesdeFiltros(array $filtros): array
    {
        $empresasIds = $this->getEmpresasInScope($filtros);

        if ($empresasIds->isEmpty()) {
            return [
                'is_multi' => false,
                'lote' => null,
                'lotes' => collect(),
                'lote_ids' => [],
                'escenario' => 1,
                'lote_activo' => null,
                'metadata' => [
                    'total_empresas' => 0,
                    'empresas_con_activo' => 0,
                    'empresas_con_cerrado' => 0,
                    'empresas_con_lote' => 0,
                    'empresas_sin_lote' => 0,
                ],
            ];
        }

        if ($empresasIds->count() === 1) {
            $infoSingle = $this->resolverLoteEstadoActualDesdeFiltros($filtros);
            $lote = $infoSingle['lote'];

            $loteIds = $lote ? [$lote->id] : [];
            $lotesCol = $lote ? collect([$lote]) : collect();
            $hasActive = $infoSingle['escenario'] === 2 || $infoSingle['escenario'] === 4;
            $hasClosed = $infoSingle['escenario'] === 3 || $infoSingle['escenario'] === 4;

            return [
                'is_multi' => false,
                'lote' => $lote,
                'lotes' => $lotesCol,
                'lote_ids' => $loteIds,
                'escenario' => $infoSingle['escenario'],
                'lote_activo' => $infoSingle['lote_activo'],
                'metadata' => [
                    'total_empresas' => $empresasIds->count(),
                    'empresas_con_activo' => $hasActive ? 1 : 0,
                    'empresas_con_cerrado' => $hasClosed ? 1 : 0,
                    'empresas_con_lote' => $lote ? 1 : 0,
                    'empresas_sin_lote' => $lote ? 0 : $empresasIds->count(),
                ],
            ];
        }

        $lotesCombinados = collect();
        $loteIds = [];

        $empresasConActivoCount = 0;
        $empresasConCerradoCount = 0;
        $empresasConLoteCount = 0;

        foreach ($empresasIds as $empresaId) {
            $queryLotesEmpresa = Lote::query()->where('empresa_id', $empresaId);

            if (! empty($filtros['sucursal_id'])) {
                $queryLotesEmpresa->where('sucursal_id', $filtros['sucursal_id']);
            }
            if (! empty($filtros['lote_id'])) {
                $queryLotesEmpresa->where('id', $filtros['lote_id']);
            }

            $lotesEmpresa = $queryLotesEmpresa->get();

            $infoEmpresa = $this->resolverEstadoDesdeLotes($lotesEmpresa);

            if ($infoEmpresa['lote']) {
                $lotesCombinados->push($infoEmpresa['lote']);
                $loteIds[] = $infoEmpresa['lote']->id;
                $empresasConLoteCount++;
            }

            $tieneActivo = $infoEmpresa['escenario'] === 2 || $infoEmpresa['escenario'] === 4;
            $tieneCerrado = $infoEmpresa['escenario'] === 3 || $infoEmpresa['escenario'] === 4;

            if ($tieneActivo) {
                $empresasConActivoCount++;
            }
            if ($tieneCerrado) {
                $empresasConCerradoCount++;
            }
        }

        $totalEmpresas = $empresasIds->count();
        $empresasSinLoteCount = max(0, $totalEmpresas - $empresasConLoteCount);

        return [
            'is_multi' => true,
            'lote' => $lotesCombinados->first(),
            'lotes' => $lotesCombinados,
            'lote_ids' => $loteIds,
            'escenario' => null,
            'lote_activo' => null,
            'metadata' => [
                'total_empresas' => $totalEmpresas,
                'empresas_con_activo' => $empresasConActivoCount,
                'empresas_con_cerrado' => $empresasConCerradoCount,
                'empresas_con_lote' => $empresasConLoteCount,
                'empresas_sin_lote' => $empresasSinLoteCount,
            ],
        ];
    }

    protected function resolverEstadoDesdeLotes(\Illuminate\Support\Collection $lotes): array
    {
        $hoy = Carbon::today()->toDateString();

        // Limitación conocida y aceptada: al consolidar empresa + sucursales, cada una puede tener su "lote de estado actual"
        // con fecha_fin distinta entre sí (ej. Sucursal Norte cerrada en marzo, Sucursal Sur cerrada en junio).
        // El sistema tomará el lote más reciente dentro del conjunto combinado sin distinguir cuál sucursal aporta qué fecha.
        // Esto se resuelve formalmente en Issue M (comparativas históricas) con el concepto de "tanda/familia de lotes",
        // que aún no existe en el modelo de datos.

        // Closed lotes: fecha_fin is in the past
        $lotesCerrados = $lotes->filter(fn ($l) => $l->fecha_fin && $l->fecha_fin->toDateString() < $hoy)
            ->sortByDesc('fecha_fin');

        // Active lotes: activo = true AND (fecha_fin is null OR fecha_fin >= hoy)
        $lotesActivos = $lotes->filter(fn ($l) => $l->activo && (! $l->fecha_fin || $l->fecha_fin->toDateString() >= $hoy))
            ->sortByDesc('fecha_inicio');

        $loteEstadoActual = null;
        $escenario = 1;

        if ($lotesCerrados->isNotEmpty() && $lotesActivos->isNotEmpty()) {
            $escenario = 4;
            $loteEstadoActual = $lotesCerrados->first();
        } elseif ($lotesCerrados->isNotEmpty() && $lotesActivos->isEmpty()) {
            $escenario = 3;
            $loteEstadoActual = $lotesCerrados->first();
        } elseif ($lotesCerrados->isEmpty() && $lotesActivos->isNotEmpty()) {
            $escenario = 2;
            $loteEstadoActual = $lotesActivos->first();
        } else {
            $escenario = 1;
            $loteEstadoActual = null;
        }

        return [
            'lote' => $loteEstadoActual,
            'escenario' => $escenario,
            'lote_activo' => $lotesActivos->first(),
        ];
    }
}
