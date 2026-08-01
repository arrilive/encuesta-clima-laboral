<?php

use App\Enums\Role;
use App\Livewire\Admin\CorporativosTable;
use App\Models\Corporativo;
use App\Models\Empresa;
use App\Models\User;
use Livewire\Livewire;

it('super_admin puede acceder a /admin/corporativos', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get(route('admin.corporativos'))
        ->assertOk();
});

it('admin_empresa no puede acceder a /admin/corporativos', function () {
    $admin = User::factory()->create(['role' => Role::ADMIN_EMPRESA->value]);

    $this->actingAs($admin)
        ->get(route('admin.corporativos'))
        ->assertForbidden();
});

it('muestra el conteo de empresas y el admin corporativo asignado', function () {
    $admin = User::factory()->superAdmin()->create();
    $this->actingAs($admin);

    $corp = Corporativo::create(['nombre' => 'Corp Con Datos', 'activa' => true]);
    Empresa::factory()->count(3)->create(['corporativo_id' => $corp->id]);

    $adminCorp = User::create([
        'name' => 'Admin Corp Visible',
        'email' => 'admin_corp_vis@test.com',
        'password' => 'secret',
        'role' => Role::ADMIN_CORPORATIVO->value,
        'corporativo_id' => $corp->id,
    ]);

    Livewire::test(CorporativosTable::class)
        ->assertSee('Corp Con Datos')
        ->assertSee('3')
        ->assertSee('Admin Corp Visible');
});

it('puede crear un corporativo', function () {
    $admin = User::factory()->superAdmin()->create();
    $this->actingAs($admin);

    Livewire::test(CorporativosTable::class)
        ->set('nombre', 'Corporativo Test')
        ->call('crear')
        ->assertHasNoErrors()
        ->assertDispatched('notify');

    expect(Corporativo::where('nombre', 'Corporativo Test')->exists())->toBeTrue();
});

it('falla al crear corporativo duplicado', function () {
    $admin = User::factory()->superAdmin()->create();
    $this->actingAs($admin);

    Corporativo::create(['nombre' => 'Corporativo Duplicado', 'activa' => true]);

    Livewire::test(CorporativosTable::class)
        ->set('nombre', 'Corporativo Duplicado')
        ->call('crear')
        ->assertHasErrors(['nombre']);
});

it('puede editar un corporativo', function () {
    $admin = User::factory()->superAdmin()->create();
    $this->actingAs($admin);

    $corp = Corporativo::create(['nombre' => 'Corp Inicial', 'activa' => true]);

    Livewire::test(CorporativosTable::class)
        ->call('abrirEditar', $corp->id)
        ->set('nombre', 'Corp Editado')
        ->call('editar')
        ->assertHasNoErrors()
        ->assertDispatched('notify');

    expect($corp->fresh()->nombre)->toBe('Corp Editado');
});

it('puede alternar el estado activa de un corporativo', function () {
    $admin = User::factory()->superAdmin()->create();
    $this->actingAs($admin);

    $corp = Corporativo::create(['nombre' => 'Corp Activo', 'activa' => true]);

    Livewire::test(CorporativosTable::class)
        ->call('toggleActiva', $corp->id)
        ->assertDispatched('notify');

    expect($corp->fresh()->activa)->toBeFalse();

    Livewire::test(CorporativosTable::class)
        ->call('toggleActiva', $corp->id)
        ->assertDispatched('notify');

    expect($corp->fresh()->activa)->toBeTrue();
});
