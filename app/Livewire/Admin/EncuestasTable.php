<?php

namespace App\Livewire\Admin;

use App\Models\Encuesta;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class EncuestasTable extends Component
{
    use WithPagination;

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
            ->with('empresa')
            ->when($user->role === 'admin_empresa', fn($q) =>
                $q->where('empresa_id', $user->empresa_id)
            )
            ->when($this->buscar, fn($q) =>
                $q->where('token', 'like', '%' . $this->buscar . '%')
            )
            ->when($this->filtroEmpresa, fn($q) =>
                $q->where('empresa_id', $this->filtroEmpresa)
            )
            ->when($this->filtroEstado, fn($q) =>
                $q->where('estado', $this->filtroEstado)
            )
            ->when($this->filtroDesde, fn($q) =>
                $q->whereDate('fecha_asignacion', '>=', $this->filtroDesde)
            )
            ->when($this->filtroHasta, fn($q) =>
                $q->whereDate('fecha_asignacion', '<=', $this->filtroHasta)
            )
            ->orderByDesc('fecha_asignacion')
            ->paginate(14);

        return view('livewire.admin.encuestas-table', compact('encuestas'));
    }
}
