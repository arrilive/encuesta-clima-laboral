<?php

namespace App\Livewire\Admin;

use App\Enums\Role;
use App\Models\Corporativo;
use App\Models\Empresa;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Validation\Rule;
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

    public ?int $adminId = null;

    public string $llaveMaestra = '';

    public ?string $passwordGenerada = null;

    public ?int $corporativoId = null;

    // Campos de formulario Sucursales
    public ?int $sucursalId = null;

    public string $sucursalNombre = '';

    public ?int $sucursalAdminId = null;

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
        $this->empresaId = null;
        $this->reset(['nombre', 'adminId', 'llaveMaestra', 'passwordGenerada', 'corporativoId']);
        $this->resetErrorBag();
        $this->modalCrear = true;
    }

    public function crear(): void
    {
        $this->validate([
            'nombre' => 'required|string|max:255|unique:empresas,nombre',
            'llaveMaestra' => 'required|string|min:8',
            'corporativoId' => 'nullable|exists:corporativos,id',
            'adminId' => [
                'nullable',
                Rule::exists('users', 'id')->where(
                    fn ($q) => $q->where('role', Role::ADMIN_EMPRESA->value)->whereNull('empresa_id')
                ),
            ],
        ], [
            'nombre.required' => 'El nombre de la empresa es obligatorio.',
            'nombre.unique' => 'Ya existe una empresa con ese nombre.',
            'llaveMaestra.required' => 'La llave maestra es obligatoria.',
            'llaveMaestra.min' => 'La llave maestra debe tener al menos 8 caracteres.',
            'adminId.exists' => 'Este administrador ya está asignado a otra empresa. Desasígnalo primero desde Administradores o desde esa empresa.',
        ]);

        $empresa = Empresa::create([
            'nombre' => $this->nombre,
            'password' => $this->llaveMaestra,
            'activa' => true,
            'corporativo_id' => $this->corporativoId ?: null,
        ]);

        if ($this->adminId) {
            User::where('id', $this->adminId)
                ->where('role', Role::ADMIN_EMPRESA->value)
                ->update(['empresa_id' => $empresa->id]);
        }

        $this->modalCrear = false;
    }

    // ── Editar empresa ───────────────────────────────────────────────────────

    public function abrirEditarEmpresa(int $id): void
    {
        $empresa = Empresa::findOrFail($id);
        $this->empresaId = $empresa->id;
        $this->nombre = $empresa->nombre;
        $this->corporativoId = $empresa->corporativo_id;
        $this->adminId = User::where('empresa_id', $empresa->id)
            ->where('role', Role::ADMIN_EMPRESA->value)
            ->first()?->id;
        $this->resetErrorBag();
        $this->modalEditarEmpresa = true;
    }

    public function editarEmpresa(): void
    {
        $this->validate([
            'nombre' => "required|string|max:255|unique:empresas,nombre,{$this->empresaId}",
            'corporativoId' => 'nullable|exists:corporativos,id',
            'adminId' => [
                'nullable',
                Rule::exists('users', 'id')->where(
                    fn ($q) => $q->where('role', Role::ADMIN_EMPRESA->value)
                        ->where(fn ($sub) => $sub->whereNull('empresa_id')->orWhere('empresa_id', $this->empresaId))
                ),
            ],
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique' => 'Ya existe una empresa con ese nombre.',
            'adminId.exists' => 'Este administrador ya está asignado a otra empresa. Desasígnalo primero desde Administradores o desde esa empresa.',
        ]);

        Empresa::findOrFail($this->empresaId)->update([
            'nombre' => $this->nombre,
            'corporativo_id' => $this->corporativoId ?: null,
        ]);

        $currentAdmin = User::where('empresa_id', $this->empresaId)
            ->where('role', Role::ADMIN_EMPRESA->value)
            ->first();

        if ($currentAdmin && $currentAdmin->id != $this->adminId) {
            $currentAdmin->update(['empresa_id' => null]);
        }

        if ($this->adminId) {
            User::where('id', $this->adminId)
                ->where('role', Role::ADMIN_EMPRESA->value)
                ->update(['empresa_id' => $this->empresaId]);
        }

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
        $this->sucursalId = null;
        $this->reset(['sucursalNombre', 'sucursalLlave', 'sucursalAdminId']);
        $this->resetErrorBag();
        $this->modalCrearSucursal = true;
    }

    public function crearSucursal(): void
    {
        $this->validate([
            'sucursalNombre' => "required|string|max:255|unique:sucursales,nombre,NULL,id,empresa_id,{$this->empresaSeleccionadaId}",
            'sucursalLlave' => 'required|string|min:8',
            'sucursalAdminId' => [
                'nullable',
                Rule::exists('users', 'id')->where(
                    fn ($q) => $q->where('role', Role::ADMIN_SUCURSAL->value)->whereNull('sucursal_id')
                ),
            ],
        ], [
            'sucursalNombre.required' => 'El nombre de la sucursal es obligatorio.',
            'sucursalNombre.unique' => 'Ya existe una sucursal con ese nombre en esta empresa.',
            'sucursalLlave.required' => 'La llave maestra es obligatoria.',
            'sucursalLlave.min' => 'La llave maestra debe tener al menos 8 caracteres.',
            'sucursalAdminId.exists' => 'Este administrador ya está asignado a otra sucursal. Desasígnalo primero desde Administradores o desde esa sucursal.',
        ]);

        $sucursal = Sucursal::create([
            'empresa_id' => $this->empresaSeleccionadaId,
            'nombre' => $this->sucursalNombre,
            'password' => $this->sucursalLlave, // Auto-hashed by model cast
            'activa' => true,
        ]);

        if ($this->sucursalAdminId) {
            User::where('id', $this->sucursalAdminId)
                ->where('role', Role::ADMIN_SUCURSAL->value)
                ->update(['sucursal_id' => $sucursal->id]);
        }

        $this->modalCrearSucursal = false;
        $this->reset(['sucursalNombre', 'sucursalLlave', 'sucursalAdminId']);
    }

    public function abrirEditarSucursal(int $id): void
    {
        $sucursal = Sucursal::findOrFail($id);
        $this->sucursalId = $sucursal->id;
        $this->sucursalNombre = $sucursal->nombre;
        $this->sucursalAdminId = User::where('sucursal_id', $sucursal->id)
            ->where('role', Role::ADMIN_SUCURSAL->value)
            ->first()?->id;
        $this->resetErrorBag();
        $this->modalEditarSucursal = true;
    }

    public function editarSucursal(): void
    {
        $this->validate([
            'sucursalNombre' => "required|string|max:255|unique:sucursales,nombre,{$this->sucursalId},id,empresa_id,{$this->empresaSeleccionadaId}",
            'sucursalAdminId' => [
                'nullable',
                Rule::exists('users', 'id')->where(
                    fn ($q) => $q->where('role', Role::ADMIN_SUCURSAL->value)
                        ->where(fn ($sub) => $sub->whereNull('sucursal_id')->orWhere('sucursal_id', $this->sucursalId))
                ),
            ],
        ], [
            'sucursalNombre.required' => 'El nombre de la sucursal es obligatorio.',
            'sucursalNombre.unique' => 'Ya existe una sucursal con ese nombre en esta empresa.',
            'sucursalAdminId.exists' => 'Este administrador ya está asignado a otra sucursal. Desasígnalo primero desde Administradores o desde esa sucursal.',
        ]);

        Sucursal::findOrFail($this->sucursalId)->update([
            'nombre' => $this->sucursalNombre,
        ]);

        $currentAdmin = User::where('sucursal_id', $this->sucursalId)
            ->where('role', Role::ADMIN_SUCURSAL->value)
            ->first();

        if ($currentAdmin && $currentAdmin->id != $this->sucursalAdminId) {
            $currentAdmin->update(['sucursal_id' => null]);
        }

        if ($this->sucursalAdminId) {
            User::where('id', $this->sucursalAdminId)
                ->where('role', Role::ADMIN_SUCURSAL->value)
                ->update(['sucursal_id' => $this->sucursalId]);
        }

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
            ->select('empresas.*')
            ->when($this->buscar, fn ($q) => $q->where('nombre', 'like', "%{$this->buscar}%"))
            ->addSelect([
                'completadas' => \App\Models\Encuesta::selectRaw('count(*)')
                    ->whereIn('lote_id', function ($query) {
                        $query->select('id')
                            ->from('lotes')
                            ->whereColumn('lotes.empresa_id', 'empresas.id');
                    })
                    ->where('estado', 'completado'),
                'disponibles' => \App\Models\Encuesta::selectRaw('count(*)')
                    ->whereIn('lote_id', function ($query) {
                        $query->select('id')
                            ->from('lotes')
                            ->whereColumn('lotes.empresa_id', 'empresas.id');
                    })
                    ->where('estado', 'disponible'),
            ])
            ->with(['users', 'corporativo'])
            ->orderBy('nombre')
            ->paginate(10);

        $corporativos = Corporativo::where('activa', true)->orderBy('nombre')->get();

        $adminsEmpresaDisponibles = User::where('role', Role::ADMIN_EMPRESA->value)
            ->where(function ($q) {
                $q->whereNull('empresa_id');
                if ($this->empresaId) {
                    $q->orWhere('empresa_id', $this->empresaId);
                }
            })
            ->orderBy('name')
            ->get();

        $adminsSucursalDisponibles = User::where('role', Role::ADMIN_SUCURSAL->value)
            ->where(function ($q) {
                $q->whereNull('sucursal_id');
                if ($this->sucursalId) {
                    $q->orWhere('sucursal_id', $this->sucursalId);
                }
            })
            ->orderBy('name')
            ->get();

        $sucursales = [];
        $empresaSeleccionada = null;
        if ($this->empresaSeleccionadaId) {
            $empresaSeleccionada = Empresa::find($this->empresaSeleccionadaId);
            $sucursales = Sucursal::with('users')
                ->where('empresa_id', $this->empresaSeleccionadaId)
                ->orderBy('nombre')
                ->get();
        }

        return view('livewire.admin.empresas-table', compact(
            'empresas',
            'corporativos',
            'sucursales',
            'empresaSeleccionada',
            'adminsEmpresaDisponibles',
            'adminsSucursalDisponibles'
        ));
    }
}
