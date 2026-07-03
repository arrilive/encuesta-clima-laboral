<?php

namespace App\Traits;

use App\Enums\Role;
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
}
