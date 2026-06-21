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

    public string $filtroCorporativo = '';

    public string $filtroEmpresa = '';

    public string $filtroSucursal = '';

    public string $filtroLote = '';

    public string $filtroEstado = '';

    public string $filtroDesde = '';

    public string $filtroHasta = '';

    public function updated(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroCorporativo(): void
    {
        $this->filtroEmpresa = '';
        $this->filtroSucursal = '';
        $this->filtroLote = '';
        $this->resetPage();
    }

    public function updatedFiltroEmpresa(): void
    {
        $this->filtroSucursal = '';
        $this->filtroLote = '';
        $this->resetPage();
    }

    public function updatedFiltroSucursal(): void
    {
        $this->filtroLote = '';
        $this->resetPage();
    }

    public function updatedFiltroLote(): void
    {
        $this->resetPage();
    }

    public function getCorporativosProperty()
    {
        $user = auth()->user();
        if ($user->role !== \App\Enums\Role::SUPER_ADMIN->value) {
            return collect();
        }

        return \App\Models\Corporativo::where('activa', true)->orderBy('nombre')->get();
    }

    public function getEmpresasProperty()
    {
        $user = auth()->user();
        if (! in_array($user->role, [
            \App\Enums\Role::SUPER_ADMIN->value,
            \App\Enums\Role::ADMIN_CORPORATIVO->value,
        ])) {
            return collect();
        }

        return match ($user->role) {
            \App\Enums\Role::SUPER_ADMIN->value => $this->filtroCorporativo
                ? \App\Models\Empresa::where('corporativo_id', $this->filtroCorporativo)->orderBy('nombre')->get()
                : \App\Models\Empresa::orderBy('nombre')->get(),
            \App\Enums\Role::ADMIN_CORPORATIVO->value => \App\Models\Empresa::where('corporativo_id', $user->corporativo_id)->orderBy('nombre')->get(),
            default => collect(),
        };
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
        ]) && ! $this->filtroEmpresa) {
            return collect();
        }

        $empresaId = $this->filtroEmpresa ?: $user->empresa_id;

        return \App\Models\Sucursal::where('empresa_id', $empresaId)
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
        ]) && ! $this->filtroEmpresa) {
            return collect();
        }

        $query = \App\Models\Lote::with('sucursal');
        $query = $this->scopeByRole($query);

        if ($this->filtroEmpresa) {
            $query->where('empresa_id', $this->filtroEmpresa);
        }

        if ($this->filtroSucursal) {
            $query->where('sucursal_id', $this->filtroSucursal);
        }

        return $query->orderByDesc('fecha_inicio')->get();
    }

    protected function onEachSide(): int
    {
        return 1;
    }

    public function limpiarFiltros(): void
    {
        $this->buscar = '';
        $this->filtroCorporativo = '';
        $this->filtroEmpresa = '';
        $this->filtroSucursal = '';
        $this->filtroLote = '';
        $this->filtroEstado = '';
        $this->filtroDesde = '';
        $this->filtroHasta = '';
        $this->resetPage();
    }

    public function render()
    {
        $user = auth()->user();

        $encuestas = Encuesta::query()
            ->with('lote.empresa')
            ->whereHas('lote', fn ($q) => $this->scopeByRole($q))
            ->when($this->buscar, fn ($q) => $q->where('token', 'like', '%'.$this->buscar.'%'))
            ->when($user->role === \App\Enums\Role::SUPER_ADMIN->value && $this->filtroCorporativo, fn ($q) => $q->whereHas('lote.empresa', fn ($q2) => $q2->where('corporativo_id', $this->filtroCorporativo)))
            ->when(in_array($user->role, [
                \App\Enums\Role::SUPER_ADMIN->value,
                \App\Enums\Role::ADMIN_CORPORATIVO->value,
            ]) && $this->filtroEmpresa, fn ($q) => $q->whereHas('lote', fn ($q2) => $q2->where('empresa_id', $this->filtroEmpresa)))
            ->when($this->filtroSucursal, fn ($q) => $q->whereHas('lote', fn ($q2) => $q2->where('sucursal_id', $this->filtroSucursal)))
            ->when($this->filtroLote, fn ($q) => $q->where('lote_id', $this->filtroLote))
            ->when($this->filtroEstado, fn ($q) => $q->where('estado', $this->filtroEstado))
            ->when($this->filtroDesde, fn ($q) => $q->whereDate('fecha_asignacion', '>=', $this->filtroDesde))
            ->when($this->filtroHasta, fn ($q) => $q->whereDate('fecha_asignacion', '<=', $this->filtroHasta))
            ->orderByDesc('fecha_asignacion')
            ->paginate(14);

        return view('livewire.admin.encuestas-table', [
            'encuestas' => $encuestas,
            'corporativos' => $this->corporativos,
            'empresas' => $this->empresas,
            'sucursales' => $this->sucursales,
            'lotes' => $this->lotes,
        ]);
    }
}
