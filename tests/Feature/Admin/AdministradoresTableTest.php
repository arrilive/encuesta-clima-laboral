<?php

use App\Enums\Role;
use App\Livewire\Admin\AdministradoresTable;
use App\Models\Corporativo;
use App\Models\Empresa;
use App\Models\Sucursal;
use App\Models\User;
use Livewire\Livewire;

it('super_admin puede acceder a /admin/administradores', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get(route('admin.administradores'))
        ->assertOk();
});

it('admin_empresa no puede acceder a /admin/administradores', function () {
    $admin = User::factory()->create(['role' => Role::ADMIN_EMPRESA->value]);

    $this->actingAs($admin)
        ->get(route('admin.administradores'))
        ->assertForbidden();
});

it('puede crear un administrador corporativo y requiere corporativoId', function () {
    $admin = User::factory()->superAdmin()->create();
    $this->actingAs($admin);

    $corp = Corporativo::create(['nombre' => 'Corporativo A', 'activa' => true]);

    // Intentar crear sin corporativoId debe fallar
    Livewire::test(AdministradoresTable::class)
        ->set('nombre', 'Admin Corp')
        ->set('email', 'admincorp@test.com')
        ->set('rol', Role::ADMIN_CORPORATIVO->value)
        ->call('crear')
        ->assertHasErrors(['corporativoId']);

    // Con corporativoId debe tener éxito
    Livewire::test(AdministradoresTable::class)
        ->set('nombre', 'Admin Corp')
        ->set('email', 'admincorp@test.com')
        ->set('rol', Role::ADMIN_CORPORATIVO->value)
        ->set('corporativoId', $corp->id)
        ->call('crear')
        ->assertHasNoErrors();

    expect(User::where('email', 'admincorp@test.com')->first())
        ->not->toBeNull()
        ->role->toBe(Role::ADMIN_CORPORATIVO->value)
        ->corporativo_id->toBe($corp->id);
});

it('puede crear un administrador empresa y requiere empresaId', function () {
    $admin = User::factory()->superAdmin()->create();
    $this->actingAs($admin);

    $empresa = Empresa::factory()->create(['activa' => true]);

    Livewire::test(AdministradoresTable::class)
        ->set('nombre', 'Admin Emp')
        ->set('email', 'adminemp@test.com')
        ->set('rol', Role::ADMIN_EMPRESA->value)
        ->call('crear')
        ->assertHasErrors(['empresaId']);

    Livewire::test(AdministradoresTable::class)
        ->set('nombre', 'Admin Emp')
        ->set('email', 'adminemp@test.com')
        ->set('rol', Role::ADMIN_EMPRESA->value)
        ->set('empresaId', $empresa->id)
        ->call('crear')
        ->assertHasNoErrors();

    expect(User::where('email', 'adminemp@test.com')->first())
        ->not->toBeNull()
        ->role->toBe(Role::ADMIN_EMPRESA->value)
        ->empresa_id->toBe($empresa->id);
});

it('puede crear un administrador sucursal y requiere sucursalId', function () {
    $admin = User::factory()->superAdmin()->create();
    $this->actingAs($admin);

    $empresa = Empresa::factory()->create();
    $sucursal = Sucursal::create([
        'empresa_id' => $empresa->id,
        'nombre' => 'Sucursal Central',
        'password' => 'password123',
        'activa' => true,
    ]);

    Livewire::test(AdministradoresTable::class)
        ->set('nombre', 'Admin Suc')
        ->set('email', 'adminsuc@test.com')
        ->set('rol', Role::ADMIN_SUCURSAL->value)
        ->call('crear')
        ->assertHasErrors(['sucursalId']);

    Livewire::test(AdministradoresTable::class)
        ->set('nombre', 'Admin Suc')
        ->set('email', 'adminsuc@test.com')
        ->set('rol', Role::ADMIN_SUCURSAL->value)
        ->set('sucursalId', $sucursal->id)
        ->call('crear')
        ->assertHasNoErrors();

    expect(User::where('email', 'adminsuc@test.com')->first())
        ->not->toBeNull()
        ->role->toBe(Role::ADMIN_SUCURSAL->value)
        ->sucursal_id->toBe($sucursal->id);
});

it('limpia las FKs que no corresponden al cambiar de rol en edicion', function () {
    $admin = User::factory()->superAdmin()->create();
    $this->actingAs($admin);

    $corp = Corporativo::create(['nombre' => 'Corp X', 'activa' => true]);
    $empresa = Empresa::factory()->create(['activa' => true]);

    // Usuario inicial es admin_corporativo
    $targetUser = User::create([
        'name' => 'Test User',
        'email' => 'test@test.com',
        'password' => 'secret',
        'role' => Role::ADMIN_CORPORATIVO->value,
        'corporativo_id' => $corp->id,
    ]);

    // Editar y cambiar a admin_empresa
    Livewire::test(AdministradoresTable::class)
        ->call('abrirEditar', $targetUser->id)
        ->set('rol', Role::ADMIN_EMPRESA->value)
        ->set('empresaId', $empresa->id)
        ->call('editar')
        ->assertHasNoErrors();

    $fresh = $targetUser->fresh();
    expect($fresh->role)->toBe(Role::ADMIN_EMPRESA->value)
        ->and($fresh->empresa_id)->toBe($empresa->id)
        ->and($fresh->corporativo_id)->toBeNull()
        ->and($fresh->sucursal_id)->toBeNull();
});

it('bloquea la eliminacion si es el ultimo admin de una empresa activa', function () {
    $admin = User::factory()->superAdmin()->create();
    $this->actingAs($admin);

    $empresa = Empresa::factory()->create(['activa' => true]);
    $user = User::create([
        'name' => 'Unico Admin',
        'email' => 'unico@test.com',
        'password' => 'secret',
        'role' => Role::ADMIN_EMPRESA->value,
        'empresa_id' => $empresa->id,
    ]);

    Livewire::test(AdministradoresTable::class)
        ->call('abrirEliminar', $user->id)
        ->call('eliminar')
        ->assertSee('No se puede eliminar al único administrador de la empresa activa');

    expect($user->fresh())->not->toBeNull();
});

it('permite la eliminacion si es el unico admin de una empresa inactiva', function () {
    $admin = User::factory()->superAdmin()->create();
    $this->actingAs($admin);

    $empresa = Empresa::factory()->create(['activa' => false]);
    $user = User::create([
        'name' => 'Unico Admin Inactivo',
        'email' => 'unicoinactivo@test.com',
        'password' => 'secret',
        'role' => Role::ADMIN_EMPRESA->value,
        'empresa_id' => $empresa->id,
    ]);

    Livewire::test(AdministradoresTable::class)
        ->call('abrirEliminar', $user->id)
        ->call('eliminar');

    expect(User::find($user->id))->toBeNull();
});

it('permite la eliminacion si hay otros admins en la empresa activa', function () {
    $admin = User::factory()->superAdmin()->create();
    $this->actingAs($admin);

    $empresa = Empresa::factory()->create(['activa' => true]);
    $user1 = User::create([
        'name' => 'Admin 1',
        'email' => 'admin1@test.com',
        'password' => 'secret',
        'role' => Role::ADMIN_EMPRESA->value,
        'empresa_id' => $empresa->id,
    ]);
    $user2 = User::create([
        'name' => 'Admin 2',
        'email' => 'admin2@test.com',
        'password' => 'secret',
        'role' => Role::ADMIN_EMPRESA->value,
        'empresa_id' => $empresa->id,
    ]);

    Livewire::test(AdministradoresTable::class)
        ->call('abrirEliminar', $user1->id)
        ->call('eliminar');

    expect(User::find($user1->id))->toBeNull();
});

it('puede regenerar la contraseña de un administrador', function () {
    $admin = User::factory()->superAdmin()->create();
    $this->actingAs($admin);

    $targetUser = User::create([
        'name' => 'Test User',
        'email' => 'test@test.com',
        'password' => 'old_password',
        'role' => Role::ADMIN_CORPORATIVO->value,
    ]);

    Livewire::test(AdministradoresTable::class)
        ->call('regenerarPassword', $targetUser->id)
        ->assertHasNoErrors();

    $targetUser = $targetUser->fresh();
    expect($targetUser->password)->not->toBe('old_password');
});
