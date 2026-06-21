<?php

namespace App\Traits;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Builder;

trait HasTenantScope
{
    private function scopeByRole(Builder $query): Builder
    {
        $user = auth()->user();

        if (! $user) {
            return $query;
        }

        return match (Role::tryFrom($user->role)) {
            Role::SUPER_ADMIN => $query,
            Role::ADMIN_CORPORATIVO => $query->whereHas('empresa', fn ($q) => $q->where('corporativo_id', $user->corporativo_id)),
            Role::ADMIN_EMPRESA => $query->where('empresa_id', $user->empresa_id),
            Role::ADMIN_SUCURSAL => $query->where('sucursal_id', $user->sucursal_id),
            default => $query,
        };
    }
}
