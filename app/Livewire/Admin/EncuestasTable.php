<?php

namespace App\Livewire\Admin;

use App\Models\Encuesta;
use App\Traits\HasTenantScope;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin', ['heading' => 'Encuestas'])]
class EncuestasTable extends Component
{
    use HasTenantScope, WithPagination;

    public string $buscar = '';

    public string $filtroEmpresa = '';

    public string $filtroEstado = '';

    public string $filtroDesde = '';

    public string $filtroHasta = '';

    public function updated(): void
    {
        $this->resetPage();
    }

    protected function onEachSide(): int
    {
        return 1;
    }

    public function render()
    {
        $user = auth()->user();

        $encuestas = Encuesta::query()
            ->with('lote.empresa')
            ->whereHas('lote', fn ($q) => $this->scopeByRole($q))
            ->when($this->buscar, fn ($q) => $q->where('token', 'like', '%'.$this->buscar.'%'))
            ->when(in_array($user->role, [
                \App\Enums\Role::SUPER_ADMIN->value,
                \App\Enums\Role::ADMIN_CORPORATIVO->value,
            ]) && $this->filtroEmpresa, fn ($q) => $q->whereHas('lote', fn ($q2) => $q2->where('empresa_id', $this->filtroEmpresa)))
            ->when($this->filtroEstado, fn ($q) => $q->where('estado', $this->filtroEstado))
            ->when($this->filtroDesde, fn ($q) => $q->whereDate('fecha_asignacion', '>=', $this->filtroDesde))
            ->when($this->filtroHasta, fn ($q) => $q->whereDate('fecha_asignacion', '<=', $this->filtroHasta))
            ->orderByDesc('fecha_asignacion')
            ->paginate(14);

        return view('livewire.admin.encuestas-table', compact('encuestas'));
    }
}
