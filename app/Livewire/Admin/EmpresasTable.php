<?php

namespace App\Livewire\Admin;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin', ['heading' => 'Empresas'])]
class EmpresasTable extends Component
{
    use WithPagination;

    // ── Búsqueda ─────────────────────────────────────────────────────────────
    public string $search = '';

    // ── Modales ──────────────────────────────────────────────────────────────
    public bool $modalCrear = false;

    public bool $modalEditarNombre = false;

    public bool $modalLlaveMaestra = false;

    public bool $modalPasswordAdmin = false;

    public bool $modalPasswordGenerada = false;

    // ── Campos de formulario ─────────────────────────────────────────────────
    public ?int $empresaId = null;

    public string $nombre = '';

    public string $adminNombre = '';

    public string $adminEmail = '';

    public string $llaveMaestra = '';

    public string $passwordAdmin = '';

    public ?string $passwordGenerada = null;

    // Reset paginación al buscar
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    // ── Crear empresa ────────────────────────────────────────────────────────

    public function abrirModalCrear(): void
    {
        $this->reset(['nombre', 'adminNombre', 'adminEmail', 'llaveMaestra', 'passwordGenerada']);
        $this->resetErrorBag();
        $this->modalCrear = true;
    }

    public function crear(): void
    {
        $this->validate([
            'nombre' => 'required|string|max:255|unique:empresas,nombre',
            'adminNombre' => 'required|string|max:255',
            'adminEmail' => 'required|email|unique:users,email',
            'llaveMaestra' => 'required|string|min:8',
        ], [
            'nombre.required' => 'El nombre de la empresa es obligatorio.',
            'nombre.unique' => 'Ya existe una empresa con ese nombre.',
            'adminNombre.required' => 'El nombre del administrador es obligatorio.',
            'adminEmail.required' => 'El correo electrónico es obligatorio.',
            'adminEmail.email' => 'Ingresa un correo electrónico válido.',
            'adminEmail.unique' => 'Este correo ya está registrado.',
            'llaveMaestra.required' => 'La llave maestra es obligatoria.',
            'llaveMaestra.min' => 'La llave maestra debe tener al menos 8 caracteres.',
        ]);

        $passwordPlana = Str::password(12);

        DB::transaction(function () use ($passwordPlana) {
            $empresa = Empresa::create([
                'nombre' => $this->nombre,
                'password' => $this->llaveMaestra,
                'activa' => true,
            ]);

            User::create([
                'name' => $this->adminNombre,
                'email' => $this->adminEmail,
                'password' => $passwordPlana,
                'role' => 'admin_empresa',
                'empresa_id' => $empresa->id,
            ]);
        });

        $this->passwordGenerada = $passwordPlana;
        $this->modalCrear = false;
        $this->modalPasswordGenerada = true;
    }

    // ── Editar nombre ────────────────────────────────────────────────────────

    public function abrirEditarNombre(int $id): void
    {
        $empresa = Empresa::findOrFail($id);
        $this->empresaId = $empresa->id;
        $this->nombre = $empresa->nombre;
        $this->resetErrorBag();
        $this->modalEditarNombre = true;
    }

    public function editarNombre(): void
    {
        $this->validate([
            'nombre' => "required|string|max:255|unique:empresas,nombre,{$this->empresaId}",
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique' => 'Ya existe una empresa con ese nombre.',
        ]);

        Empresa::findOrFail($this->empresaId)->update(['nombre' => $this->nombre]);

        $this->modalEditarNombre = false;
    }

    // ── Cambiar llave maestra ────────────────────────────────────────────────

    public function abrirLlaveMaestra(int $id): void
    {
        $this->empresaId = $id;
        $this->llaveMaestra = '';
        $this->resetErrorBag();
        $this->modalLlaveMaestra = true;
    }

    public function cambiarLlave(): void
    {
        $this->validate([
            'llaveMaestra' => 'required|string|min:8',
        ], [
            'llaveMaestra.required' => 'La nueva llave maestra es obligatoria.',
            'llaveMaestra.min' => 'La llave maestra debe tener al menos 8 caracteres.',
        ]);

        Empresa::findOrFail($this->empresaId)->update(['password' => $this->llaveMaestra]);

        $this->modalLlaveMaestra = false;
    }

    // ── Cambiar password del admin ───────────────────────────────────────────

    public function abrirPasswordAdmin(int $id): void
    {
        $this->empresaId = $id;
        $this->resetErrorBag();
        $this->modalPasswordAdmin = true;
    }

    public function cambiarPasswordAdmin(): void
    {
        $passwordPlana = Str::password(12);

        User::where('empresa_id', $this->empresaId)
            ->where('role', 'admin_empresa')
            ->firstOrFail()
            ->update(['password' => $passwordPlana]);

        $this->passwordGenerada = $passwordPlana;
        $this->modalPasswordAdmin = false;
        $this->modalPasswordGenerada = true;
    }

    // ── Toggle activa ────────────────────────────────────────────────────────

    public function toggleActiva(int $id): void
    {
        $empresa = Empresa::findOrFail($id);
        $empresa->update(['activa' => ! $empresa->activa]);
    }

    // ── Cerrar modal contraseña generada ─────────────────────────────────────

    public function cerrarPasswordGenerada(): void
    {
        $this->passwordGenerada = null;
        $this->modalPasswordGenerada = false;
    }

    // ── Render ───────────────────────────────────────────────────────────────

    public function render()
    {
        $empresas = Empresa::query()
            ->when($this->search, fn ($q) => $q->where('nombre', 'like', "%{$this->search}%"))
            ->withCount([
                'encuestas as completadas' => fn ($q) => $q->where('estado', 'completado'),
                'encuestas as disponibles' => fn ($q) => $q->where('estado', 'disponible'),
            ])
            ->with('users')
            ->orderBy('nombre')
            ->paginate(10);

        return view('livewire.admin.empresas-table', compact('empresas'));
    }
}
