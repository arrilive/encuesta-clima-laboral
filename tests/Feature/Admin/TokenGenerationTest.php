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
        ->call('generar')
        ->assertDispatched('notify');

    expect(Encuesta::whereHas('lote', fn ($q) => $q->where('empresa_id', $empresa->id))->count())->toBe(10);

    // Verificar formato de token TK-XXXX-XXXX
    $encuestas = Encuesta::all();
    expect($encuestas)->not->toBeEmpty();
    foreach ($encuestas as $encuesta) {
        expect($encuesta->token)->toMatch('/^TK-[A-Z0-9]{4}-[A-Z0-9]{4}$/');
    }
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

it('inyectar() agrega tokens a lote existente y actualiza tokens_total', function () {
    $empresa = Empresa::factory()->create();
    $lote = \App\Models\Lote::create([
        'empresa_id' => $empresa->id,
        'user_id' => User::factory()->superAdmin()->create()->id,
        'tokens_total' => 10,
        'nombre' => 'Lote original',
        'fecha_inicio' => now()->toDateString(),
        'fecha_fin' => now()->addDays(10)->toDateString(),
        'activo' => true,
    ]);

    $admin = User::factory()->superAdmin()->create();

    Livewire::actingAs($admin)
        ->test(GenerarTokens::class)
        ->set('modo', 'b')
        ->set('empresaIdModoB', (string) $empresa->id)
        ->set('loteId', (string) $lote->id)
        ->set('cantidadModoB', '5')
        ->call('inyectar')
        ->assertDispatched('notify');

    $lote->refresh();
    expect($lote->tokens_total)->toBe(15);
    expect(Encuesta::where('lote_id', $lote->id)->count())->toBe(5);

    // Verificar formato de token TK-XXXX-XXXX en inyección
    $encuestas = Encuesta::where('lote_id', $lote->id)->get();
    expect($encuestas)->not->toBeEmpty();
    foreach ($encuestas as $encuesta) {
        expect($encuesta->token)->toMatch('/^TK-[A-Z0-9]{4}-[A-Z0-9]{4}$/');
    }
});

it('inyectar() actualiza fecha_fin si se provee nuevaFechaFin valida', function () {
    $empresa = Empresa::factory()->create();
    $lote = \App\Models\Lote::create([
        'empresa_id' => $empresa->id,
        'user_id' => User::factory()->superAdmin()->create()->id,
        'tokens_total' => 10,
        'nombre' => 'Lote original',
        'fecha_inicio' => now()->toDateString(),
        'fecha_fin' => now()->addDays(10)->toDateString(),
        'activo' => true,
    ]);

    $admin = User::factory()->superAdmin()->create();
    $nuevaFecha = now()->addDays(20)->toDateString();

    Livewire::actingAs($admin)
        ->test(GenerarTokens::class)
        ->set('modo', 'b')
        ->set('empresaIdModoB', (string) $empresa->id)
        ->set('loteId', (string) $lote->id)
        ->set('cantidadModoB', '5')
        ->set('nuevaFechaFin', $nuevaFecha)
        ->call('inyectar');

    $lote->refresh();
    expect($lote->fecha_fin->toDateString())->toBe($nuevaFecha);
});

it('inyectar() no actualiza fecha_fin si nuevaFechaFin esta vacio', function () {
    $empresa = Empresa::factory()->create();
    $fechaOriginal = now()->addDays(10)->toDateString();
    $lote = \App\Models\Lote::create([
        'empresa_id' => $empresa->id,
        'user_id' => User::factory()->superAdmin()->create()->id,
        'tokens_total' => 10,
        'nombre' => 'Lote original',
        'fecha_inicio' => now()->toDateString(),
        'fecha_fin' => $fechaOriginal,
        'activo' => true,
    ]);

    $admin = User::factory()->superAdmin()->create();

    Livewire::actingAs($admin)
        ->test(GenerarTokens::class)
        ->set('modo', 'b')
        ->set('empresaIdModoB', (string) $empresa->id)
        ->set('loteId', (string) $lote->id)
        ->set('cantidadModoB', '5')
        ->set('nuevaFechaFin', '')
        ->call('inyectar');

    $lote->refresh();
    expect($lote->fecha_fin->toDateString())->toBe($fechaOriginal);
});

it('inyectar() falla si loteId no existe', function () {
    $admin = User::factory()->superAdmin()->create();

    Livewire::actingAs($admin)
        ->test(GenerarTokens::class)
        ->set('modo', 'b')
        ->set('loteId', '999999')
        ->set('cantidadModoB', '5')
        ->call('inyectar')
        ->assertHasErrors(['loteId']);

    expect(Encuesta::count())->toBe(0);
});

it('inyectar() falla si cantidadModoB excede 500', function () {
    $empresa = Empresa::factory()->create();
    $lote = \App\Models\Lote::create([
        'empresa_id' => $empresa->id,
        'user_id' => User::factory()->superAdmin()->create()->id,
        'tokens_total' => 10,
        'nombre' => 'Lote original',
        'fecha_inicio' => now()->toDateString(),
        'fecha_fin' => now()->addDays(10)->toDateString(),
        'activo' => true,
    ]);

    $admin = User::factory()->superAdmin()->create();

    Livewire::actingAs($admin)
        ->test(GenerarTokens::class)
        ->set('modo', 'b')
        ->set('empresaIdModoB', (string) $empresa->id)
        ->set('loteId', (string) $lote->id)
        ->set('cantidadModoB', '501')
        ->call('inyectar')
        ->assertHasErrors(['cantidadModoB']);

    expect(Encuesta::count())->toBe(0);
});

it('inyectar() falla si nuevaFechaFin es anterior a today', function () {
    $empresa = Empresa::factory()->create();
    $lote = \App\Models\Lote::create([
        'empresa_id' => $empresa->id,
        'user_id' => User::factory()->superAdmin()->create()->id,
        'tokens_total' => 10,
        'nombre' => 'Lote original',
        'fecha_inicio' => now()->toDateString(),
        'fecha_fin' => now()->addDays(10)->toDateString(),
        'activo' => true,
    ]);

    $admin = User::factory()->superAdmin()->create();

    Livewire::actingAs($admin)
        ->test(GenerarTokens::class)
        ->set('modo', 'b')
        ->set('empresaIdModoB', (string) $empresa->id)
        ->set('loteId', (string) $lote->id)
        ->set('cantidadModoB', '5')
        ->set('nuevaFechaFin', now()->subDay()->toDateString())
        ->call('inyectar')
        ->assertHasErrors(['nuevaFechaFin']);

    expect(Encuesta::count())->toBe(0);
});

it('inyectar() no ejecuta si modo !== b', function () {
    $empresa = Empresa::factory()->create();
    $lote = \App\Models\Lote::create([
        'empresa_id' => $empresa->id,
        'user_id' => User::factory()->superAdmin()->create()->id,
        'tokens_total' => 10,
        'nombre' => 'Lote original',
        'fecha_inicio' => now()->toDateString(),
        'fecha_fin' => now()->addDays(10)->toDateString(),
        'activo' => true,
    ]);

    $admin = User::factory()->superAdmin()->create();

    Livewire::actingAs($admin)
        ->test(GenerarTokens::class)
        ->set('modo', 'a')
        ->set('empresaIdModoB', (string) $empresa->id)
        ->set('loteId', (string) $lote->id)
        ->set('cantidadModoB', '5')
        ->call('inyectar');

    $lote->refresh();
    expect($lote->tokens_total)->toBe(10);
    expect(Encuesta::count())->toBe(0);
});

it('inyectar() no ejecuta si rol !== super_admin', function () {
    $empresa = Empresa::factory()->create();
    $lote = \App\Models\Lote::create([
        'empresa_id' => $empresa->id,
        'user_id' => User::factory()->superAdmin()->create()->id,
        'tokens_total' => 10,
        'nombre' => 'Lote original',
        'fecha_inicio' => now()->toDateString(),
        'fecha_fin' => now()->addDays(10)->toDateString(),
        'activo' => true,
    ]);

    $admin = User::factory()->adminEmpresa($empresa->id)->create();

    Livewire::actingAs($admin)
        ->test(GenerarTokens::class)
        ->set('modo', 'b')
        ->set('empresaIdModoB', (string) $empresa->id)
        ->set('loteId', (string) $lote->id)
        ->set('cantidadModoB', '5')
        ->call('inyectar');

    $lote->refresh();
    expect($lote->tokens_total)->toBe(10);
    expect(Encuesta::count())->toBe(0);
});

it('generar() crea lote con sucursal_id cuando se selecciona sucursal', function () {
    $empresa = Empresa::factory()->create();
    $sucursal = \App\Models\Sucursal::factory()->create(['empresa_id' => $empresa->id, 'activa' => true]);
    $admin = User::factory()->superAdmin()->create();

    Livewire::actingAs($admin)
        ->test(GenerarTokens::class)
        ->set('empresaId', (string) $empresa->id)
        ->set('sucursalId', (string) $sucursal->id)
        ->set('tokensTotal', '5')
        ->set('fechaInicio', now()->toDateString())
        ->set('fechaFin', now()->addDays(30)->toDateString())
        ->call('generar');

    $lote = \App\Models\Lote::first();
    expect($lote)->not->toBeNull();
    expect($lote->sucursal_id)->toBe($sucursal->id);
    expect($lote->empresa_id)->toBe($empresa->id);
    expect(Encuesta::where('lote_id', $lote->id)->count())->toBe(5);
});

it('generar() falla si la sucursal no pertenece a la empresa seleccionada', function () {
    $empresa1 = Empresa::factory()->create();
    $empresa2 = Empresa::factory()->create();
    $sucursalDeOtraEmpresa = \App\Models\Sucursal::factory()->create(['empresa_id' => $empresa2->id, 'activa' => true]);
    $admin = User::factory()->superAdmin()->create();

    Livewire::actingAs($admin)
        ->test(GenerarTokens::class)
        ->set('empresaId', (string) $empresa1->id)
        ->set('sucursalId', (string) $sucursalDeOtraEmpresa->id)
        ->set('tokensTotal', '5')
        ->set('fechaInicio', now()->toDateString())
        ->set('fechaFin', now()->addDays(30)->toDateString())
        ->call('generar')
        ->assertHasErrors(['sucursalId']);

    expect(\App\Models\Lote::count())->toBe(0);
});

it('generar() falla si la sucursal está inactiva', function () {
    $empresa = Empresa::factory()->create();
    $sucursal = \App\Models\Sucursal::factory()->create(['empresa_id' => $empresa->id, 'activa' => false]);
    $admin = User::factory()->superAdmin()->create();

    Livewire::actingAs($admin)
        ->test(GenerarTokens::class)
        ->set('empresaId', (string) $empresa->id)
        ->set('sucursalId', (string) $sucursal->id)
        ->set('tokensTotal', '5')
        ->set('fechaInicio', now()->toDateString())
        ->set('fechaFin', now()->addDays(30)->toDateString())
        ->call('generar')
        ->assertHasErrors(['sucursalId']);

    expect(\App\Models\Lote::count())->toBe(0);
});

it('generar() crea lote general cuando sucursalId está vacío', function () {
    $empresa = Empresa::factory()->create();
    $admin = User::factory()->superAdmin()->create();

    Livewire::actingAs($admin)
        ->test(GenerarTokens::class)
        ->set('empresaId', (string) $empresa->id)
        ->set('sucursalId', '')
        ->set('tokensTotal', '5')
        ->set('fechaInicio', now()->toDateString())
        ->set('fechaFin', now()->addDays(30)->toDateString())
        ->call('generar');

    $lote = \App\Models\Lote::first();
    expect($lote)->not->toBeNull();
    expect($lote->sucursal_id)->toBeNull();
    expect($lote->empresa_id)->toBe($empresa->id);
});

it('lotesVigentes incluye nombre de sucursal en modo B', function () {
    $empresa = Empresa::factory()->create();
    $sucursal = \App\Models\Sucursal::factory()->create(['empresa_id' => $empresa->id, 'activa' => true]);
    $admin = User::factory()->superAdmin()->create();

    \App\Models\Lote::create([
        'empresa_id' => $empresa->id,
        'sucursal_id' => $sucursal->id,
        'user_id' => $admin->id,
        'tokens_total' => 10,
        'nombre' => 'Lote Sucursal Norte',
        'fecha_inicio' => now()->toDateString(),
        'fecha_fin' => now()->addDays(30)->toDateString(),
        'activo' => true,
    ]);

    \App\Models\Lote::create([
        'empresa_id' => $empresa->id,
        'sucursal_id' => null,
        'user_id' => $admin->id,
        'tokens_total' => 20,
        'nombre' => 'Lote General',
        'fecha_inicio' => now()->toDateString(),
        'fecha_fin' => now()->addDays(30)->toDateString(),
        'activo' => true,
    ]);

    $component = Livewire::actingAs($admin)
        ->test(GenerarTokens::class)
        ->set('modo', 'b')
        ->set('empresaIdModoB', (string) $empresa->id);

    $lotesVigentes = $component->get('lotesVigentes');

    expect($lotesVigentes)->toHaveCount(2);

    $loteConSucursal = $lotesVigentes->firstWhere('sucursal_id', $sucursal->id);
    expect($loteConSucursal->sucursal)->not->toBeNull();
    expect($loteConSucursal->sucursal->nombre)->toBe($sucursal->nombre);

    $loteGeneral = $lotesVigentes->firstWhere('sucursal_id', null);
    expect($loteGeneral->sucursal)->toBeNull();
});

it('lotesVigentes ordena generales primero y sucursales alfabéticamente', function () {
    $empresa = Empresa::factory()->create();
    $admin = User::factory()->superAdmin()->create();

    $sucursales = [
        'Sucursal Zeta' => 5,
        'Sucursal Mango' => 4,
        'Sucursal Bravo' => 3,
        'Sucursal Alpha' => 2,
        'Sucursal Tango' => 1,
    ];

    $sucursalIds = [];
    foreach ($sucursales as $nombre => $orden) {
        $sucursalIds[$nombre] = \App\Models\Sucursal::factory()->create([
            'empresa_id' => $empresa->id,
            'activa' => true,
            'nombre' => $nombre,
        ])->id;
    }

    // Orden de creación deliberadamente revuelto (no alfabético), igual que en la verificación empírica
    $ordenCreacion = ['Sucursal Zeta', 'Sucursal Mango', null, 'Sucursal Bravo', 'Sucursal Alpha', 'Sucursal Tango'];

    foreach ($ordenCreacion as $i => $nombreSucursal) {
        \App\Models\Lote::create([
            'empresa_id' => $empresa->id,
            'sucursal_id' => $nombreSucursal ? $sucursalIds[$nombreSucursal] : null,
            'user_id' => $admin->id,
            'tokens_total' => 10,
            'nombre' => 'Lote '.($i + 1),
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addDays(30)->toDateString(),
            'activo' => true,
        ]);
    }

    $component = Livewire::actingAs($admin)
        ->test(GenerarTokens::class)
        ->set('modo', 'b')
        ->set('empresaIdModoB', (string) $empresa->id);

    $nombresSucursalOrdenados = $component->get('lotesVigentes')
        ->values()
        ->map(fn ($l) => $l->sucursal?->nombre ?? 'GENERAL')
        ->toArray();

    expect($nombresSucursalOrdenados)->toBe([
        'GENERAL',
        'Sucursal Alpha',
        'Sucursal Bravo',
        'Sucursal Mango',
        'Sucursal Tango',
        'Sucursal Zeta',
    ]);
});
