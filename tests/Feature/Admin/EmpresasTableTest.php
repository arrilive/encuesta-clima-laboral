<?php

use App\Livewire\Admin\EmpresasTable;
use App\Models\Empresa;
use App\Models\User;
use Livewire\Livewire;

// ── Autorización ─────────────────────────────────────────────────────────────

it('super_admin puede acceder a /admin/empresas', function () {
    $this->seed();

    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get(route('admin.empresas'))
        ->assertOk();
});

it('admin_empresa no puede acceder a /admin/empresas', function () {
    $this->seed();

    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();

    $this->actingAs($admin)
        ->get(route('admin.empresas'))
        ->assertForbidden();
});

it('usuario no autenticado es redirigido al login', function () {
    $this->seed();

    $this->get(route('admin.empresas'))
        ->assertRedirect(route('login'));
});

// ── Acciones del componente ──────────────────────────────────────────────────

it('crear() genera empresa y user admin en la base de datos', function () {
    $this->seed();

    $admin = User::factory()->superAdmin()->create();

    $component = Livewire::actingAs($admin)->test(EmpresasTable::class)
        ->set('nombre', 'Empresa Test')
        ->set('adminNombre', 'Admin Test')
        ->set('adminEmail', 'admin@empresatest.com')
        ->set('llaveMaestra', 'password1234')
        ->call('crear');

    // Verificar que la empresa fue creada
    $this->assertDatabaseHas('empresas', ['nombre' => 'Empresa Test']);

    // Verificar que el user admin fue creado con el empresa_id correcto
    $empresa = Empresa::where('nombre', 'Empresa Test')->first();
    $this->assertDatabaseHas('users', [
        'email' => 'admin@empresatest.com',
        'role' => 'admin_empresa',
        'empresa_id' => $empresa->id,
    ]);

    // Verificar que se generó la contraseña y se abrió el modal
    $component->assertSet('modalPasswordGenerada', true);
    expect($component->get('passwordGenerada'))->not->toBeNull();
});

it('crear() falla si el email del admin ya existe', function () {
    $this->seed();

    $admin = User::factory()->superAdmin()->create();

    // Crear un user con el email que vamos a intentar duplicar
    User::factory()->create(['email' => 'duplicado@test.com']);

    Livewire::actingAs($admin)->test(EmpresasTable::class)
        ->set('nombre', 'Empresa Duplicada')
        ->set('adminNombre', 'Admin Dup')
        ->set('adminEmail', 'duplicado@test.com')
        ->set('llaveMaestra', 'password1234')
        ->call('crear')
        ->assertHasErrors(['adminEmail']);
});

it('editarNombre() actualiza el nombre de la empresa', function () {
    $this->seed();

    $admin = User::factory()->superAdmin()->create();
    $empresa = Empresa::factory()->create(['nombre' => 'Nombre Original']);

    Livewire::actingAs($admin)->test(EmpresasTable::class)
        ->call('abrirEditarNombre', $empresa->id)
        ->set('nombre', 'Nombre Actualizado')
        ->call('editarNombre');

    $this->assertDatabaseHas('empresas', [
        'id' => $empresa->id,
        'nombre' => 'Nombre Actualizado',
    ]);
});

it('toggleActiva() cambia el estado de activa a inactiva y viceversa', function () {
    $this->seed();

    $admin = User::factory()->superAdmin()->create();
    $empresa = Empresa::factory()->create(['activa' => true]);

    $component = Livewire::actingAs($admin)->test(EmpresasTable::class);

    // Desactivar
    $component->call('toggleActiva', $empresa->id);
    expect($empresa->fresh()->activa)->toBeFalse();

    // Reactivar
    $component->call('toggleActiva', $empresa->id);
    expect($empresa->fresh()->activa)->toBeTrue();
});

it('cambiarPasswordAdmin() actualiza la contraseña del user admin de la empresa', function () {
    $this->seed();

    $admin = User::factory()->superAdmin()->create();
    $empresa = Empresa::factory()->create();
    $adminEmpresa = User::factory()->adminEmpresa($empresa->id)->create();

    // Guardar el hash original
    $hashOriginal = $adminEmpresa->password;

    $component = Livewire::actingAs($admin)->test(EmpresasTable::class)
        ->call('abrirPasswordAdmin', $empresa->id)
        ->call('cambiarPasswordAdmin');

    // Verificar que se generó una contraseña
    $component->assertSet('modalPasswordGenerada', true);
    expect($component->get('passwordGenerada'))->not->toBeNull();

    // Verificar que el hash cambió en BD
    expect($adminEmpresa->fresh()->password)->not->toBe($hashOriginal);
});
