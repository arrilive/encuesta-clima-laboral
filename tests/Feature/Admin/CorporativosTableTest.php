<?php

use App\Livewire\Admin\CorporativosTable;
use App\Models\Corporativo;
use App\Models\User;
use Livewire\Livewire;

it('super_admin puede acceder a /admin/corporativos', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get(route('admin.corporativos'))
        ->assertOk();
});

it('admin_empresa no puede acceder a /admin/corporativos', function () {
    $admin = User::factory()->create(['role' => \App\Enums\Role::ADMIN_EMPRESA->value]);

    $this->actingAs($admin)
        ->get(route('admin.corporativos'))
        ->assertForbidden();
});

it('puede crear un corporativo', function () {
    $admin = User::factory()->superAdmin()->create();
    $this->actingAs($admin);

    Livewire::test(CorporativosTable::class)
        ->set('nombre', 'Corporativo Test')
        ->call('crear')
        ->assertHasNoErrors();

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
        ->assertHasNoErrors();

    expect($corp->fresh()->nombre)->toBe('Corp Editado');
});

it('puede alternar el estado activa de un corporativo', function () {
    $admin = User::factory()->superAdmin()->create();
    $this->actingAs($admin);

    $corp = Corporativo::create(['nombre' => 'Corp Activo', 'activa' => true]);

    Livewire::test(CorporativosTable::class)
        ->call('toggleActiva', $corp->id);

    expect($corp->fresh()->activa)->toBeFalse();

    Livewire::test(CorporativosTable::class)
        ->call('toggleActiva', $corp->id);

    expect($corp->fresh()->activa)->toBeTrue();
});
