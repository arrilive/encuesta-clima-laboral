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
        $hoy = Carbon::today()->toDateString();

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
