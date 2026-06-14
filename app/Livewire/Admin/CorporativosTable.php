<?php

namespace App\Livewire\Admin;

use App\Models\Corporativo;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin', ['heading' => 'Corporativos'])]
class CorporativosTable extends Component
{
    use WithPagination;

    // ── Búsqueda ─────────────────────────────────────────────────────────────
    public string $buscar = '';

    // ── Modales ──────────────────────────────────────────────────────────────
    public bool $modalCrear = false;

    public bool $modalEditar = false;

    // ── Campos de formulario ─────────────────────────────────────────────────
    public ?int $corporativoId = null;

    public string $nombre = '';

    // Reset paginación al buscar
    public function updatingBuscar(): void
    {
        $this->resetPage();
    }

    // ── Crear Corporativo ────────────────────────────────────────────────────

    public function abrirModalCrear(): void
    {
        $this->reset(['nombre']);
        $this->resetErrorBag();
        $this->modalCrear = true;
    }

    public function crear(): void
    {
        $this->validate([
            'nombre' => 'required|string|max:255|unique:corporativos,nombre',
        ], [
            'nombre.required' => 'El nombre del corporativo es obligatorio.',
            'nombre.unique' => 'Ya existe un corporativo con ese nombre.',
        ]);

        Corporativo::create([
            'nombre' => $this->nombre,
            'activa' => true,
        ]);

        $this->modalCrear = false;
    }

    // ── Editar Corporativo ───────────────────────────────────────────────────

    public function abrirEditar(int $id): void
    {
        $corporativo = Corporativo::findOrFail($id);
        $this->corporativoId = $corporativo->id;
        $this->nombre = $corporativo->nombre;
        $this->resetErrorBag();
        $this->modalEditar = true;
    }

    public function editar(): void
    {
        $this->validate([
            'nombre' => "required|string|max:255|unique:corporativos,nombre,{$this->corporativoId}",
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique' => 'Ya existe un corporativo con ese nombre.',
        ]);

        Corporativo::findOrFail($this->corporativoId)->update([
            'nombre' => $this->nombre,
        ]);

        $this->modalEditar = false;
    }

    // ── Toggle activa ────────────────────────────────────────────────────────

    public function toggleActiva(int $id): void
    {
        $corporativo = Corporativo::findOrFail($id);
        $corporativo->update(['activa' => ! $corporativo->activa]);
    }

    // ── Render ───────────────────────────────────────────────────────────────

    public function render()
    {
        $corporativos = Corporativo::query()
            ->when($this->buscar, fn ($q) => $q->where('nombre', 'like', "%{$this->buscar}%"))
            ->orderBy('nombre')
            ->paginate(10);

        return view('livewire.admin.corporativos-table', compact('corporativos'));
    }
}
