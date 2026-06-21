<?php

use App\Livewire\Admin\EmpresasTable;
use App\Models\Corporativo;
use App\Models\Empresa;
use App\Models\Sucursal;
use App\Models\User;
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

it('crear() genera empresa y user admin en la base de datos con corporativo opcional', function () {
    $admin = User::factory()->superAdmin()->create();
    $this->actingAs($admin);

    $corp = Corporativo::create(['nombre' => 'Corp Test', 'activa' => true]);

    Livewire::test(EmpresasTable::class)
        ->set('nombre', 'Empresa Test')
        ->set('adminNombre', 'Admin Test')
        ->set('adminEmail', 'admin_test@empresa.com')
        ->set('llaveMaestra', 'llave1234')
        ->set('corporativoId', $corp->id)
        ->call('crear')
        ->assertHasNoErrors();

    $empresa = Empresa::where('nombre', 'Empresa Test')->first();
    expect($empresa)->not->toBeNull()
        ->and($empresa->corporativo_id)->toBe($corp->id);

    $user = User::where('email', 'admin_test@empresa.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->empresa_id)->toBe($empresa->id)
        ->and($user->role)->toBe('admin_empresa');
});

it('crear() falla si el email del admin ya existe', function () {
    $admin = User::factory()->superAdmin()->create();
    $this->actingAs($admin);

    User::create([
        'name' => 'Existing',
        'email' => 'existing@admin.com',
        'password' => 'secret',
        'role' => 'admin_empresa',
    ]);

    Livewire::test(EmpresasTable::class)
        ->set('nombre', 'Empresa B')
        ->set('adminNombre', 'Admin B')
        ->set('adminEmail', 'existing@admin.com')
        ->set('llaveMaestra', 'llave1234')
        ->call('crear')
        ->assertHasErrors(['adminEmail']);
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

it('puede crear una sucursal para una empresa seleccionada', function () {
    $admin = User::factory()->superAdmin()->create();
    $this->actingAs($admin);

    $empresa = Empresa::factory()->create();

    Livewire::test(EmpresasTable::class)
        ->call('abrirModalSucursales', $empresa->id)
        ->call('abrirCrearSucursal')
        ->set('sucursalNombre', 'Sucursal A')
        ->set('sucursalLlave', 'llavesuc123')
        ->call('crearSucursal')
        ->assertHasNoErrors();

    $suc = Sucursal::where('empresa_id', $empresa->id)->where('nombre', 'Sucursal A')->first();
    expect($suc)->not->toBeNull()
        ->and($suc->activa)->toBeTrue();
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
