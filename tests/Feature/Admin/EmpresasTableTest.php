<?php

use App\Enums\Role;
use App\Livewire\Admin\EmpresasTable;
use App\Models\Corporativo;
use App\Models\Empresa;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

// ── Autorización ─────────────────────────────────────────────────────────────

it('super_admin puede acceder a /admin/empresas', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get(route('admin.empresas'))
        ->assertOk();
});

it('admin_empresa no puede acceder a /admin/empresas', function () {
    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();

    $this->actingAs($admin)
        ->get(route('admin.empresas'))
        ->assertForbidden();
});

it('usuario no autenticado es redirigido al login', function () {
    $this->get(route('admin.empresas'))
        ->assertRedirect(route('login'));
});

// ── Acciones del componente Empresas ──────────────────────────────────────────

it('crear() genera empresa sin crear User y asigna adminId opcional', function () {
    $admin = User::factory()->superAdmin()->create();
    $this->actingAs($admin);

    $corp = Corporativo::create(['nombre' => 'Corp Test', 'activa' => true]);
    $adminEmp = User::create([
        'name' => 'Admin Pendiente',
        'email' => 'admin_pendiente@empresa.com',
        'password' => 'secret',
        'role' => Role::ADMIN_EMPRESA->value,
    ]);

    $initialUserCount = User::count();

    Livewire::test(EmpresasTable::class)
        ->set('nombre', 'Empresa Test')
        ->set('llaveMaestra', 'llave1234')
        ->set('corporativoId', $corp->id)
        ->set('adminId', $adminEmp->id)
        ->call('crear')
        ->assertHasNoErrors();

    // No se crea ningún nuevo usuario
    expect(User::count())->toBe($initialUserCount);

    $empresa = Empresa::where('nombre', 'Empresa Test')->first();
    expect($empresa)->not->toBeNull()
        ->and($empresa->corporativo_id)->toBe($corp->id);

    // El admin existente queda asignado a la nueva empresa
    expect($adminEmp->fresh()->empresa_id)->toBe($empresa->id);
});

it('falla la validación si se intenta asignar un admin con un rol no compatible a adminId', function () {
    $admin = User::factory()->superAdmin()->create();
    $this->actingAs($admin);

    $adminCorp = User::create([
        'name' => 'Admin Corp',
        'email' => 'corp_invalid@test.com',
        'password' => 'secret',
        'role' => Role::ADMIN_CORPORATIVO->value,
    ]);

    Livewire::test(EmpresasTable::class)
        ->set('nombre', 'Empresa Invalida')
        ->set('llaveMaestra', 'llave1234')
        ->set('adminId', $adminCorp->id)
        ->call('crear')
        ->assertHasErrors(['adminId']);
});

it('falla con error en adminId si se intenta asignar un admin_empresa que ya pertenece a otra empresa', function () {
    $admin = User::factory()->superAdmin()->create();
    $this->actingAs($admin);

    $empresaA = Empresa::factory()->create(['nombre' => 'Empresa A']);
    $empresaB = Empresa::factory()->create(['nombre' => 'Empresa B']);

    $adminEmpA = User::create([
        'name' => 'Admin Empresa A',
        'email' => 'admin_emp_a@test.com',
        'password' => 'secret',
        'role' => Role::ADMIN_EMPRESA->value,
        'empresa_id' => $empresaA->id,
    ]);

    // Crear nueva empresa con admin ya asignado a Empresa A -> Falla
    Livewire::test(EmpresasTable::class)
        ->set('nombre', 'Nueva Empresa')
        ->set('llaveMaestra', 'llave1234')
        ->set('adminId', $adminEmpA->id)
        ->call('crear')
        ->assertHasErrors(['adminId']);

    // Editar Empresa B intentando asignarle el admin de Empresa A -> Falla
    Livewire::test(EmpresasTable::class)
        ->call('abrirEditarEmpresa', $empresaB->id)
        ->set('adminId', $adminEmpA->id)
        ->call('editarEmpresa')
        ->assertHasErrors(['adminId']);
});

it('permite guardar al editar si se mantiene el mismo adminId que la empresa ya tenia asignado', function () {
    $admin = User::factory()->superAdmin()->create();
    $this->actingAs($admin);

    $empresa = Empresa::factory()->create(['nombre' => 'Empresa Con Admin']);
    $adminEmp = User::create([
        'name' => 'Admin Asignado',
        'email' => 'admin_asignado@test.com',
        'password' => 'secret',
        'role' => Role::ADMIN_EMPRESA->value,
        'empresa_id' => $empresa->id,
    ]);

    Livewire::test(EmpresasTable::class)
        ->call('abrirEditarEmpresa', $empresa->id)
        ->set('nombre', 'Empresa Renombrada')
        ->set('adminId', $adminEmp->id)
        ->call('editarEmpresa')
        ->assertHasNoErrors();

    expect($adminEmp->fresh()->empresa_id)->toBe($empresa->id);
});

it('editarEmpresa() actualiza el nombre y el corporativo de la empresa', function () {
    $admin = User::factory()->superAdmin()->create();
    $this->actingAs($admin);

    $empresa = Empresa::factory()->create();
    $corp = Corporativo::create(['nombre' => 'Corp Edit', 'activa' => true]);

    Livewire::test(EmpresasTable::class)
        ->call('abrirEditarEmpresa', $empresa->id)
        ->set('nombre', 'Nombre Editado')
        ->set('corporativoId', $corp->id)
        ->call('editarEmpresa')
        ->assertHasNoErrors();

    $fresh = $empresa->fresh();
    expect($fresh->nombre)->toBe('Nombre Editado')
        ->and($fresh->corporativo_id)->toBe($corp->id);
});

it('toggleActiva() cambia el estado de activa a inactiva y viceversa', function () {
    $admin = User::factory()->superAdmin()->create();
    $this->actingAs($admin);

    $empresa = Empresa::factory()->create(['activa' => true]);

    Livewire::test(EmpresasTable::class)
        ->call('toggleActiva', $empresa->id);
    expect($empresa->fresh()->activa)->toBeFalse();

    Livewire::test(EmpresasTable::class)
        ->call('toggleActiva', $empresa->id);
    expect($empresa->fresh()->activa)->toBeTrue();
});

// ── Acciones del componente Sucursales ────────────────────────────────────────

it('puede crear una sucursal para una empresa seleccionada y asignar un admin_sucursal', function () {
    $admin = User::factory()->superAdmin()->create();
    $this->actingAs($admin);

    $empresa = Empresa::factory()->create();
    $adminSuc = User::create([
        'name' => 'Admin Sucursal Test',
        'email' => 'adminsuc@test.com',
        'password' => 'secret',
        'role' => Role::ADMIN_SUCURSAL->value,
    ]);

    Livewire::test(EmpresasTable::class)
        ->call('abrirModalSucursales', $empresa->id)
        ->call('abrirCrearSucursal')
        ->set('sucursalNombre', 'Sucursal A')
        ->set('sucursalLlave', 'llavesuc123')
        ->set('sucursalAdminId', $adminSuc->id)
        ->call('crearSucursal')
        ->assertHasNoErrors();

    $suc = Sucursal::where('empresa_id', $empresa->id)->where('nombre', 'Sucursal A')->first();
    expect($suc)->not->toBeNull()
        ->and($suc->activa)->toBeTrue();

    expect($adminSuc->fresh()->sucursal_id)->toBe($suc->id);
});

it('falla la validación si se intenta asignar un admin con un rol no compatible a sucursalAdminId', function () {
    $admin = User::factory()->superAdmin()->create();
    $this->actingAs($admin);

    $empresa = Empresa::factory()->create();
    $adminEmp = User::create([
        'name' => 'Admin Empresa',
        'email' => 'emp_invalid@test.com',
        'password' => 'secret',
        'role' => Role::ADMIN_EMPRESA->value,
    ]);

    Livewire::test(EmpresasTable::class)
        ->call('abrirModalSucursales', $empresa->id)
        ->call('abrirCrearSucursal')
        ->set('sucursalNombre', 'Sucursal Invalida')
        ->set('sucursalLlave', 'llavesuc123')
        ->set('sucursalAdminId', $adminEmp->id)
        ->call('crearSucursal')
        ->assertHasErrors(['sucursalAdminId']);
});

it('falla con error en sucursalAdminId si se intenta asignar un admin_sucursal que ya pertenece a otra sucursal', function () {
    $admin = User::factory()->superAdmin()->create();
    $this->actingAs($admin);

    $empresa = Empresa::factory()->create();
    $sucA = Sucursal::create([
        'empresa_id' => $empresa->id,
        'nombre' => 'Sucursal A',
        'password' => 'secret123',
        'activa' => true,
    ]);
    $sucB = Sucursal::create([
        'empresa_id' => $empresa->id,
        'nombre' => 'Sucursal B',
        'password' => 'secret123',
        'activa' => true,
    ]);

    $adminSucA = User::create([
        'name' => 'Admin Suc A',
        'email' => 'adminsuc_a@test.com',
        'password' => 'secret',
        'role' => Role::ADMIN_SUCURSAL->value,
        'sucursal_id' => $sucA->id,
    ]);

    // Crear sucursal intentando asignarle el admin de Sucursal A -> Falla
    Livewire::test(EmpresasTable::class)
        ->call('abrirModalSucursales', $empresa->id)
        ->call('abrirCrearSucursal')
        ->set('sucursalNombre', 'Sucursal C')
        ->set('sucursalLlave', 'llavesuc123')
        ->set('sucursalAdminId', $adminSucA->id)
        ->call('crearSucursal')
        ->assertHasErrors(['sucursalAdminId']);

    // Editar Sucursal B intentando asignarle el admin de Sucursal A -> Falla
    Livewire::test(EmpresasTable::class)
        ->set('empresaSeleccionadaId', $empresa->id)
        ->call('abrirEditarSucursal', $sucB->id)
        ->set('sucursalAdminId', $adminSucA->id)
        ->call('editarSucursal')
        ->assertHasErrors(['sucursalAdminId']);
});

it('permite guardar al editar sucursal si se mantiene el mismo sucursalAdminId que la sucursal ya tenia asignado', function () {
    $admin = User::factory()->superAdmin()->create();
    $this->actingAs($admin);

    $empresa = Empresa::factory()->create();
    $suc = Sucursal::create([
        'empresa_id' => $empresa->id,
        'nombre' => 'Sucursal Con Admin',
        'password' => 'secret123',
        'activa' => true,
    ]);

    $adminSuc = User::create([
        'name' => 'Admin Suc Asignado',
        'email' => 'adminsuc_asignado@test.com',
        'password' => 'secret',
        'role' => Role::ADMIN_SUCURSAL->value,
        'sucursal_id' => $suc->id,
    ]);

    Livewire::test(EmpresasTable::class)
        ->set('empresaSeleccionadaId', $empresa->id)
        ->call('abrirEditarSucursal', $suc->id)
        ->set('sucursalNombre', 'Sucursal Renombrada')
        ->set('sucursalAdminId', $adminSuc->id)
        ->call('editarSucursal')
        ->assertHasNoErrors();

    expect($adminSuc->fresh()->sucursal_id)->toBe($suc->id);
});

it('puede editar una sucursal', function () {
    $admin = User::factory()->superAdmin()->create();
    $this->actingAs($admin);

    $empresa = Empresa::factory()->create();
    $suc = Sucursal::create([
        'empresa_id' => $empresa->id,
        'nombre' => 'Sucursal Original',
        'password' => 'secret123',
        'activa' => true,
    ]);

    Livewire::test(EmpresasTable::class)
        ->set('empresaSeleccionadaId', $empresa->id)
        ->call('abrirEditarSucursal', $suc->id)
        ->set('sucursalNombre', 'Sucursal Renombrada')
        ->call('editarSucursal')
        ->assertHasNoErrors();

    expect($suc->fresh()->nombre)->toBe('Sucursal Renombrada');
});

it('puede cambiar la llave maestra de la sucursal', function () {
    $admin = User::factory()->superAdmin()->create();
    $this->actingAs($admin);

    $empresa = Empresa::factory()->create();
    $suc = Sucursal::create([
        'empresa_id' => $empresa->id,
        'nombre' => 'Sucursal Key',
        'password' => 'secret123',
        'activa' => true,
    ]);

    Livewire::test(EmpresasTable::class)
        ->call('abrirLlaveSucursal', $suc->id)
        ->set('sucursalLlave', 'nuevallave123')
        ->call('cambiarLlaveSucursal')
        ->assertHasNoErrors();

    expect(Hash::check('nuevallave123', $suc->fresh()->password))->toBeTrue();
});

it('puede alternar el estado activa de una sucursal', function () {
    $admin = User::factory()->superAdmin()->create();
    $this->actingAs($admin);

    $empresa = Empresa::factory()->create();
    $suc = Sucursal::create([
        'empresa_id' => $empresa->id,
        'nombre' => 'Sucursal Active',
        'password' => 'secret123',
        'activa' => true,
    ]);

    Livewire::test(EmpresasTable::class)
        ->call('toggleActivaSucursal', $suc->id);
    expect($suc->fresh()->activa)->toBeFalse();

    Livewire::test(EmpresasTable::class)
        ->call('toggleActivaSucursal', $suc->id);
    expect($suc->fresh()->activa)->toBeTrue();
});
