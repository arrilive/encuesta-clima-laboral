<?php

namespace App\Livewire\Admin;

use App\Models\Corporativo;
use App\Models\Empresa;
use App\Models\Sucursal;
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
    public string $buscar = '';

    // ── Modales ──────────────────────────────────────────────────────────────
    public bool $modalCrear = false;

    public bool $modalEditarEmpresa = false;

    public bool $modalLlaveMaestra = false;

    public bool $modalPasswordGenerada = false;

    // Modales de Sucursales
    public bool $modalSucursales = false;

    public bool $modalCrearSucursal = false;

    public bool $modalEditarSucursal = false;

    public bool $modalLlaveSucursal = false;

    // ── Campos de formulario ─────────────────────────────────────────────────
    public ?int $empresaId = null;

    public string $nombre = '';

    public string $adminNombre = '';

    public string $adminEmail = '';

    public string $llaveMaestra = '';

    public ?string $passwordGenerada = null;

    public ?int $corporativoId = null;

    // Campos de formulario Sucursales
    public ?int $sucursalId = null;

    public string $sucursalNombre = '';

    public string $sucursalLlave = '';

    public ?int $empresaSeleccionadaId = null;

    // Reset paginación al buscar
    public function updatingBuscar(): void
    {
        $this->resetPage();
    }

    // ── Crear empresa ────────────────────────────────────────────────────────

    public function abrirModalCrear(): void
    {
        $this->reset(['nombre', 'adminNombre', 'adminEmail', 'llaveMaestra', 'passwordGenerada', 'corporativoId']);
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
            'corporativoId' => 'nullable|exists:corporativos,id',
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
                'corporativo_id' => $this->corporativoId ?: null,
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

    // ── Editar empresa ───────────────────────────────────────────────────────

    public function abrirEditarEmpresa(int $id): void
    {
        $empresa = Empresa::findOrFail($id);
        $this->empresaId = $empresa->id;
        $this->nombre = $empresa->nombre;
        $this->corporativoId = $empresa->corporativo_id;
        $this->resetErrorBag();
        $this->modalEditarEmpresa = true;
    }

    public function editarEmpresa(): void
    {
        $this->validate([
            'nombre' => "required|string|max:255|unique:empresas,nombre,{$this->empresaId}",
            'corporativoId' => 'nullable|exists:corporativos,id',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique' => 'Ya existe una empresa con ese nombre.',
        ]);

        Empresa::findOrFail($this->empresaId)->update([
            'nombre' => $this->nombre,
            'corporativo_id' => $this->corporativoId ?: null,
        ]);

        $this->modalEditarEmpresa = false;
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

    // ── CRUD Sucursales ──────────────────────────────────────────────────────

    public function abrirModalSucursales(int $empresaId): void
    {
        $this->empresaSeleccionadaId = $empresaId;
        $this->modalSucursales = true;
    }

    public function abrirCrearSucursal(): void
    {
        $this->reset(['sucursalNombre', 'sucursalLlave']);
        $this->resetErrorBag();
        $this->modalCrearSucursal = true;
    }

    public function crearSucursal(): void
    {
        $this->validate([
            'sucursalNombre' => "required|string|max:255|unique:sucursales,nombre,NULL,id,empresa_id,{$this->empresaSeleccionadaId}",
            'sucursalLlave' => 'required|string|min:8',
        ], [
            'sucursalNombre.required' => 'El nombre de la sucursal es obligatorio.',
            'sucursalNombre.unique' => 'Ya existe una sucursal con ese nombre en esta empresa.',
            'sucursalLlave.required' => 'La llave maestra es obligatoria.',
            'sucursalLlave.min' => 'La llave maestra debe tener al menos 8 caracteres.',
        ]);

        Sucursal::create([
            'empresa_id' => $this->empresaSeleccionadaId,
            'nombre' => $this->sucursalNombre,
            'password' => $this->sucursalLlave, // Auto-hashed by model cast
            'activa' => true,
        ]);

        $this->modalCrearSucursal = false;
        $this->reset(['sucursalNombre', 'sucursalLlave']);
    }

    public function abrirEditarSucursal(int $id): void
    {
        $sucursal = Sucursal::findOrFail($id);
        $this->sucursalId = $sucursal->id;
        $this->sucursalNombre = $sucursal->nombre;
        $this->resetErrorBag();
        $this->modalEditarSucursal = true;
    }

    public function editarSucursal(): void
    {
        $this->validate([
            'sucursalNombre' => "required|string|max:255|unique:sucursales,nombre,{$this->sucursalId},id,empresa_id,{$this->empresaSeleccionadaId}",
        ], [
            'sucursalNombre.required' => 'El nombre de la sucursal es obligatorio.',
            'sucursalNombre.unique' => 'Ya existe una sucursal con ese nombre en esta empresa.',
        ]);

        Sucursal::findOrFail($this->sucursalId)->update([
            'nombre' => $this->sucursalNombre,
        ]);

        $this->modalEditarSucursal = false;
    }

    public function abrirLlaveSucursal(int $id): void
    {
        $this->sucursalId = $id;
        $this->sucursalLlave = '';
        $this->resetErrorBag();
        $this->modalLlaveSucursal = true;
    }

    public function cambiarLlaveSucursal(): void
    {
        $this->validate([
            'sucursalLlave' => 'required|string|min:8',
        ], [
            'sucursalLlave.required' => 'La nueva llave maestra es obligatoria.',
            'sucursalLlave.min' => 'La llave maestra debe tener al menos 8 caracteres.',
        ]);

        Sucursal::findOrFail($this->sucursalId)->update([
            'password' => $this->sucursalLlave, // Auto-hashed by model cast
        ]);

        $this->modalLlaveSucursal = false;
    }

    public function toggleActivaSucursal(int $id): void
    {
        $sucursal = Sucursal::findOrFail($id);
        $sucursal->update(['activa' => ! $sucursal->activa]);
    }

    // ── Render ───────────────────────────────────────────────────────────────

    public function render()
    {
        $empresas = Empresa::query()
            ->when($this->buscar, fn ($q) => $q->where('nombre', 'like', "%{$this->buscar}%"))
            ->withCount([
                'encuestas as completadas' => fn ($q) => $q->where('estado', 'completado'),
                'encuestas as disponibles' => fn ($q) => $q->where('estado', 'disponible'),
            ])
            ->with(['users', 'corporativo'])
            ->orderBy('nombre')
            ->paginate(10);

        $corporativos = Corporativo::where('activa', true)->orderBy('nombre')->get();

        $sucursales = [];
        $empresaSeleccionada = null;
        if ($this->empresaSeleccionadaId) {
            $empresaSeleccionada = Empresa::find($this->empresaSeleccionadaId);
            $sucursales = Sucursal::where('empresa_id', $this->empresaSeleccionadaId)
                ->orderBy('nombre')
                ->get();
        }

        return view('livewire.admin.empresas-table', compact('empresas', 'corporativos', 'sucursales', 'empresaSeleccionada'));
    }
}
