<?php

use App\Livewire\Admin\GenerarTokens;
use App\Models\Empresa;
use App\Models\Encuesta;
use App\Models\User;
use Livewire\Livewire;

it('super_admin puede generar tokens para cualquier empresa', function () {
    $empresa = Empresa::factory()->create();
    $admin = User::factory()->superAdmin()->create();

    Livewire::actingAs($admin)
        ->test(GenerarTokens::class)
        ->set('empresaId', (string) $empresa->id)
        ->set('tokens_total', '10')
        ->call('generar');

    expect(Encuesta::where('empresa_id', $empresa->id)->count())->toBe(10);
});

it('admin_empresa puede generar tokens para su propia empresa', function () {
    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();

    Livewire::actingAs($admin)
        ->test(GenerarTokens::class)
        ->set('tokens_total', '5')
        ->call('generar');

    expect(Encuesta::where('empresa_id', $empresa->id)->count())->toBe(5);
});

it('admin_empresa no puede generar tokens para otra empresa', function () {
    $empresa1 = Empresa::factory()->create();
    $empresa2 = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa1->id)->create();

    Livewire::actingAs($admin)
        ->test(GenerarTokens::class)
        ->set('empresaId', (string) $empresa2->id)
        ->set('tokens_total', '5')
        ->call('generar')
        ->assertHasErrors(['empresaId']);

    expect(Encuesta::where('empresa_id', $empresa2->id)->count())->toBe(0);
});

it('super_admin no puede generar tokens para empresa inactiva', function () {
    $empresa = Empresa::factory()->create(['activa' => false]);
    $admin = User::factory()->superAdmin()->create();

    Livewire::actingAs($admin)
        ->test(GenerarTokens::class)
        ->set('empresaId', (string) $empresa->id)
        ->set('tokens_total', '10')
        ->call('generar')
        ->assertHasErrors(['empresaId']);

    expect(Encuesta::where('empresa_id', $empresa->id)->count())->toBe(0);
});

it('admin_empresa no puede generar tokens si su empresa está inactiva', function () {
    $empresa = Empresa::factory()->create(['activa' => false]);
    $admin = User::factory()->adminEmpresa($empresa->id)->create();

    Livewire::actingAs($admin)
        ->test(GenerarTokens::class)
        ->set('empresaId', (string) $empresa->id)
        ->set('tokens_total', '10')
        ->call('generar')
        ->assertHasErrors(['empresaId']);

    expect(Encuesta::where('empresa_id', $empresa->id)->count())->toBe(0);
});
