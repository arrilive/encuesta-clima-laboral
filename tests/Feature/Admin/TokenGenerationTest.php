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
        ->set('tokensTotal', '10')
        ->set('fechaFin', now()->addDays(30)->toDateString())
        ->call('generar');

    expect(Encuesta::whereHas('lote', fn ($q) => $q->where('empresa_id', $empresa->id))->count())->toBe(10);
});

it('admin_empresa no puede generar tokens para su propia empresa', function () {
    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();

    Livewire::actingAs($admin)
        ->test(GenerarTokens::class)
        ->set('tokensTotal', '5')
        ->set('fechaFin', now()->addDays(30)->toDateString())
        ->call('generar');

    expect(Encuesta::count())->toBe(0);
    expect(\App\Models\Lote::count())->toBe(0);
});

it('admin_empresa no puede generar tokens para otra empresa', function () {
    $empresa1 = Empresa::factory()->create();
    $empresa2 = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa1->id)->create();

    Livewire::actingAs($admin)
        ->test(GenerarTokens::class)
        ->set('empresaId', (string) $empresa2->id)
        ->set('tokensTotal', '5')
        ->set('fechaFin', now()->addDays(30)->toDateString())
        ->call('generar');

    expect(Encuesta::count())->toBe(0);
    expect(\App\Models\Lote::count())->toBe(0);
});

it('super_admin no puede generar tokens para empresa inactiva', function () {
    $empresa = Empresa::factory()->create(['activa' => false]);
    $admin = User::factory()->superAdmin()->create();

    Livewire::actingAs($admin)
        ->test(GenerarTokens::class)
        ->set('empresaId', (string) $empresa->id)
        ->set('tokensTotal', '10')
        ->set('fechaFin', now()->addDays(30)->toDateString())
        ->call('generar')
        ->assertHasErrors(['empresaId']);

    expect(Encuesta::whereHas('lote', fn ($q) => $q->where('empresa_id', $empresa->id))->count())->toBe(0);
});

it('admin_empresa no puede generar tokens si su empresa está inactiva', function () {
    $empresa = Empresa::factory()->create(['activa' => false]);
    $admin = User::factory()->adminEmpresa($empresa->id)->create();

    Livewire::actingAs($admin)
        ->test(GenerarTokens::class)
        ->set('empresaId', (string) $empresa->id)
        ->set('tokensTotal', '10')
        ->set('fechaFin', now()->addDays(30)->toDateString())
        ->call('generar');

    expect(Encuesta::count())->toBe(0);
    expect(\App\Models\Lote::count())->toBe(0);
});

it('valida campos de fecha con mensajes en español personalizados', function () {
    $empresa = Empresa::factory()->create();
    $admin = User::factory()->superAdmin()->create();

    $component = Livewire::actingAs($admin)
        ->test(GenerarTokens::class)
        ->set('empresaId', (string) $empresa->id)
        ->set('fechaInicio', '')
        ->set('fechaFin', '')
        ->call('generar');

    expect($component->errors()->get('fechaInicio'))->toContain('La fecha de inicio es obligatoria.');
    expect($component->errors()->get('fechaFin'))->toContain('La fecha de fin es obligatoria.');

    $component2 = Livewire::actingAs($admin)
        ->test(GenerarTokens::class)
        ->set('empresaId', (string) $empresa->id)
        ->set('fechaInicio', now()->subDay()->toDateString())
        ->set('fechaFin', now()->subDays(2)->toDateString())
        ->call('generar');

    expect($component2->errors()->get('fechaInicio'))->toContain('La fecha de inicio debe ser hoy o una fecha futura.');
    expect($component2->errors()->get('fechaFin'))->toContain('La fecha de cierre debe ser posterior a la fecha de inicio.');
});
