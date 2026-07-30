<?php

namespace App\Livewire\Admin;

use App\Enums\Role;
use App\Models\Corporativo;
use App\Models\Empresa;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin', ['heading' => 'Administradores'])]
class AdministradoresTable extends Component
{
    use WithPagination;

    // ── Búsqueda ─────────────────────────────────────────────────────────────
    public string $buscar = '';

    // ── Modales ──────────────────────────────────────────────────────────────
    public bool $modalCrear = false;

    public bool $modalEditar = false;

    public bool $modalEliminar = false;

    public bool $modalPasswordGenerada = false;

    // ── Campos de formulario ─────────────────────────────────────────────────
    public ?int $userId = null;

    public string $nombre = '';

    public string $email = '';

    public string $rol = '';

    public ?int $corporativoId = null;

    public ?int $empresaId = null;

    public ?int $sucursalId = null;

    public ?string $passwordGenerada = null;

    public ?string $errorEliminar = null;

    // Reset paginación al buscar
    public function updatingBuscar(): void
    {
        $this->resetPage();
    }

    // ── Crear Administrador ──────────────────────────────────────────────────

    public function abrirModalCrear(): void
    {
        $this->reset([
            'nombre',
            'email',
            'rol',
            'corporativoId',
            'empresaId',
            'sucursalId',
            'passwordGenerada',
            'userId',
            'errorEliminar',
        ]);
        $this->resetErrorBag();
        $this->modalCrear = true;
    }

    public function crear(): void
    {
        $rules = [
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'rol' => 'required|in:'.implode(',', [
                Role::ADMIN_CORPORATIVO->value,
                Role::ADMIN_EMPRESA->value,
                Role::ADMIN_SUCURSAL->value,
            ]),
        ];

        if ($this->rol === Role::ADMIN_CORPORATIVO->value) {
            $rules['corporativoId'] = 'nullable|exists:corporativos,id';
        } elseif ($this->rol === Role::ADMIN_EMPRESA->value) {
            $rules['empresaId'] = 'nullable|exists:empresas,id';
        } elseif ($this->rol === Role::ADMIN_SUCURSAL->value) {
            $rules['sucursalId'] = 'nullable|exists:sucursales,id';
        }

        $this->validate($rules, [
            'nombre.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Ingresa un correo electrónico válido.',
            'email.unique' => 'Este correo ya está registrado.',
            'rol.required' => 'El rol es obligatorio.',
            'rol.in' => 'El rol seleccionado no es válido.',
            'corporativoId.required' => 'El corporativo es obligatorio.',
            'corporativoId.exists' => 'El corporativo seleccionado no es válido.',
            'empresaId.required' => 'La empresa es obligatoria.',
            'empresaId.exists' => 'La empresa seleccionada no es válida.',
            'sucursalId.required' => 'La sucursal es obligatoria.',
            'sucursalId.exists' => 'La sucursal seleccionada no es válida.',
        ]);

        $passwordPlana = Str::password(12);

        User::create([
            'name' => $this->nombre,
            'email' => $this->email,
            'password' => $passwordPlana, // Auto-hashed by cast
            'role' => $this->rol,
            'corporativo_id' => ($this->rol === Role::ADMIN_CORPORATIVO->value) ? $this->corporativoId : null,
            'empresa_id' => ($this->rol === Role::ADMIN_EMPRESA->value) ? $this->empresaId : null,
            'sucursal_id' => ($this->rol === Role::ADMIN_SUCURSAL->value) ? $this->sucursalId : null,
        ]);

        $this->passwordGenerada = $passwordPlana;
        $this->modalCrear = false;
        $this->modalPasswordGenerada = true;
        $this->dispatch('notify', mensaje: 'Administrador <b>'.e($this->nombre).'</b> creado correctamente.', tipo: 'success');
    }

    // ── Editar Administrador ─────────────────────────────────────────────────

    public function abrirEditar(int $id): void
    {
        $user = User::findOrFail($id);
        $this->userId = $user->id;
        $this->nombre = $user->name;
        $this->email = $user->email;
        $this->rol = $user->role;
        $this->corporativoId = $user->corporativo_id;
        $this->empresaId = $user->empresa_id;
        $this->sucursalId = $user->sucursal_id;
        $this->resetErrorBag();
        $this->modalEditar = true;
    }

    public function editar(): void
    {
        $rules = [
            'nombre' => 'required|string|max:255',
            'email' => "required|email|max:255|unique:users,email,{$this->userId}",
            'rol' => 'required|in:'.implode(',', [
                Role::ADMIN_CORPORATIVO->value,
                Role::ADMIN_EMPRESA->value,
                Role::ADMIN_SUCURSAL->value,
            ]),
        ];

        if ($this->rol === Role::ADMIN_CORPORATIVO->value) {
            $rules['corporativoId'] = 'nullable|exists:corporativos,id';
        } elseif ($this->rol === Role::ADMIN_EMPRESA->value) {
            $rules['empresaId'] = 'nullable|exists:empresas,id';
        } elseif ($this->rol === Role::ADMIN_SUCURSAL->value) {
            $rules['sucursalId'] = 'nullable|exists:sucursales,id';
        }

        $this->validate($rules, [
            'nombre.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Ingresa un correo electrónico válido.',
            'email.unique' => 'Este correo ya está registrado.',
            'rol.required' => 'El rol es obligatorio.',
            'rol.in' => 'El rol seleccionado no es válido.',
            'corporativoId.required' => 'El corporativo es obligatorio.',
            'corporativoId.exists' => 'El corporativo seleccionado no es válido.',
            'empresaId.required' => 'La empresa es obligatoria.',
            'empresaId.exists' => 'La empresa seleccionada no es válida.',
            'sucursalId.required' => 'La sucursal es obligatoria.',
            'sucursalId.exists' => 'La sucursal seleccionada no es válida.',
        ]);

        $user = User::findOrFail($this->userId);
        $user->update([
            'name' => $this->nombre,
            'email' => $this->email,
            'role' => $this->rol,
            'corporativo_id' => ($this->rol === Role::ADMIN_CORPORATIVO->value) ? $this->corporativoId : null,
            'empresa_id' => ($this->rol === Role::ADMIN_EMPRESA->value) ? $this->empresaId : null,
            'sucursal_id' => ($this->rol === Role::ADMIN_SUCURSAL->value) ? $this->sucursalId : null,
        ]);

        $this->modalEditar = false;
        $this->dispatch('notify', mensaje: 'Administrador <b>'.e($this->nombre).'</b> actualizado correctamente.', tipo: 'success');
    }

    // ── Eliminar Administrador ───────────────────────────────────────────────

    public function abrirEliminar(int $id): void
    {
        $user = User::findOrFail($id);
        $this->userId = $user->id;
        $this->errorEliminar = null;
        $this->modalEliminar = true;
    }

    public function eliminar(): void
    {
        $user = User::findOrFail($this->userId);

        if ($user->role === Role::ADMIN_EMPRESA->value) {
            $count = User::where('empresa_id', $user->empresa_id)
                ->where('role', Role::ADMIN_EMPRESA->value)
                ->count();

            $empresaActiva = false;
            if ($user->empresa_id) {
                $empresaActiva = Empresa::where('id', $user->empresa_id)
                    ->where('activa', true)
                    ->exists();
            }

            if ($count === 1 && $empresaActiva) {
                $this->errorEliminar = "No se puede eliminar al único administrador de la empresa activa '{$user->empresa->nombre}'. Asigne otro administrador o desactive la empresa primero.";

                return;
            }
        }

        $nombreEliminado = $user->name;
        $user->delete();
        $this->modalEliminar = false;
        $this->dispatch('notify', mensaje: 'Administrador <b>'.e($nombreEliminado).'</b> eliminado.', tipo: 'success');
    }

    // ── Cerrar modal contraseña generada ─────────────────────────────────────

    public function cerrarPasswordGenerada(): void
    {
        $this->passwordGenerada = null;
        $this->modalPasswordGenerada = false;
    }

    // ── Regenerar contraseña ──────────────────────────────────────────────────

    public function regenerarPassword(int $id): void
    {
        $user = User::findOrFail($id);
        $passwordPlana = Str::password(12);

        $user->update([
            'password' => $passwordPlana, // Auto-hashed by cast
        ]);

        $this->passwordGenerada = $passwordPlana;
        $this->modalPasswordGenerada = true;
        $this->dispatch('notify', mensaje: 'Contraseña de <b>'.e($user->name).'</b> regenerada correctamente.', tipo: 'success');
    }

    // ── Render ───────────────────────────────────────────────────────────────

    public function render()
    {
        $users = User::query()
            ->where('role', '!=', Role::SUPER_ADMIN->value)
            ->when($this->buscar, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('name', 'like', "%{$this->buscar}%")
                        ->orWhere('email', 'like', "%{$this->buscar}%");
                });
            })
            ->with(['empresa', 'corporativo', 'sucursal'])
            ->orderBy('name')
            ->paginate(10);

        $corporativos = Corporativo::where('activa', true)->orderBy('nombre')->get();
        $empresas = Empresa::where('activa', true)->orderBy('nombre')->get();
        $sucursales = Sucursal::where('activa', true)->orderBy('nombre')->get();

        return view('livewire.admin.administradores-table', compact('users', 'corporativos', 'empresas', 'sucursales'));
    }
}
