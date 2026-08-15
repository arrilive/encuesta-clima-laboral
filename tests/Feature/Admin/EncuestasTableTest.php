<?php

use App\Livewire\Admin\EncuestasTable;
use App\Models\Empresa;
use App\Models\Encuesta;
use App\Models\Lote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('puede limpiar filtros correctamente', function () {
    $admin = User::factory()->superAdmin()->create();
    $this->actingAs($admin);

    Livewire::test(EncuestasTable::class)
        ->set('filtroCorporativo', '1')
        ->set('filtroEmpresa', '2')
        ->set('filtroSucursal', '3')
        ->set('filtroLote', '4')
        ->set('filtroEstado', 'completado')
        ->set('filtroDesde', '2026-06-01')
        ->set('filtroHasta', '2026-06-30')
        ->call('gotoPage', 2)
        ->call('limpiarFiltros')
        ->assertSet('filtroCorporativo', '')
        ->assertSet('filtroEmpresa', '')
        ->assertSet('filtroSucursal', '')
        ->assertSet('filtroLote', '')
        ->assertSet('filtroEstado', '')
        ->assertSet('filtroDesde', '')
        ->assertSet('filtroHasta', '');
});

it('super_admin puede eliminar tokens disponibles y decrementar tokens_total del lote', function () {
    $empresa = Empresa::factory()->create();
    $superAdmin = User::factory()->superAdmin()->create();

    $lote = Lote::create([
        'empresa_id' => $empresa->id,
        'user_id' => $superAdmin->id,
        'tokens_total' => 5,
        'nombre' => 'Lote Test',
        'fecha_inicio' => now()->toDateString(),
        'fecha_fin' => now()->addDays(10)->toDateString(),
        'activo' => true,
    ]);

    $encuestas = [];
    for ($i = 0; $i < 5; $i++) {
        $encuestas[] = Encuesta::create([
            'token' => 'TK-TEST-'.Str::upper(Str::random(4)),
            'estado' => 'disponible',
            'lote_id' => $lote->id,
        ]);
    }

    $seleccion = [(string) $encuestas[0]->id, (string) $encuestas[1]->id];

    Livewire::actingAs($superAdmin)
        ->test(EncuestasTable::class)
        ->set('selectedTokens', $seleccion)
        ->call('confirmarEliminacion')
        ->assertSet('confirmandoEliminacion', true)
        ->call('eliminarTokensSeleccionados')
        ->assertDispatched('notify')
        ->assertSet('selectedTokens', [])
        ->assertSet('confirmandoEliminacion', false);

    $lote->refresh();
    expect($lote->tokens_total)->toBe(3);
    expect(Encuesta::where('lote_id', $lote->id)->count())->toBe(3);
    expect(Encuesta::find($encuestas[0]->id))->toBeNull();
    expect(Encuesta::find($encuestas[1]->id))->toBeNull();
    expect(Encuesta::find($encuestas[2]->id))->not->toBeNull();
});

it('omite tokens que cambien de estado a no-disponible antes de confirmar eliminación', function () {
    $empresa = Empresa::factory()->create();
    $superAdmin = User::factory()->superAdmin()->create();

    $lote = Lote::create([
        'empresa_id' => $empresa->id,
        'user_id' => $superAdmin->id,
        'tokens_total' => 3,
        'nombre' => 'Lote Omitidos',
        'fecha_inicio' => now()->toDateString(),
        'fecha_fin' => now()->addDays(10)->toDateString(),
        'activo' => true,
    ]);

    $enc1 = Encuesta::create(['token' => 'TK-TEST-0001', 'estado' => 'disponible', 'lote_id' => $lote->id]);
    $enc2 = Encuesta::create(['token' => 'TK-TEST-0002', 'estado' => 'completado', 'lote_id' => $lote->id]);

    Livewire::actingAs($superAdmin)
        ->test(EncuestasTable::class)
        ->set('selectedTokens', [(string) $enc1->id, (string) $enc2->id])
        ->call('confirmarEliminacion')
        ->call('eliminarTokensSeleccionados')
        ->assertDispatched('notify', fn ($name, $params) => str_contains($params['mensaje'], '1 token(s) eliminado(s) correctamente') && str_contains($params['mensaje'], '1 token(s) omitido(s) por no estar disponible(s)'));

    $lote->refresh();
    expect($lote->tokens_total)->toBe(2);
    expect(Encuesta::find($enc1->id))->toBeNull();
    expect(Encuesta::find($enc2->id))->not->toBeNull();
});

it('omite tokens disponibles que tengan datos demográficos u otras relaciones asociadas por inconsistencia', function () {
    $this->seed([
        \Database\Seeders\AntiguedadesSeeder::class,
        \Database\Seeders\CargosSeeder::class,
        \Database\Seeders\EdadesSeeder::class,
        \Database\Seeders\GradosAcademicosSeeder::class,
        \Database\Seeders\LugaresTrabajoSeeder::class,
        \Database\Seeders\SexosSeeder::class,
    ]);

    $empresa = Empresa::factory()->create();
    $superAdmin = User::factory()->superAdmin()->create();

    $lote = Lote::create([
        'empresa_id' => $empresa->id,
        'user_id' => $superAdmin->id,
        'tokens_total' => 2,
        'nombre' => 'Lote Inconsistente',
        'fecha_inicio' => now()->toDateString(),
        'fecha_fin' => now()->addDays(10)->toDateString(),
        'activo' => true,
    ]);

    $enc1 = Encuesta::create(['token' => 'TK-TEST-8888', 'estado' => 'disponible', 'lote_id' => $lote->id]);
    $enc2 = Encuesta::create(['token' => 'TK-TEST-9999', 'estado' => 'disponible', 'lote_id' => $lote->id]);

    $datoDemo = \App\Models\DatoDemografico::factory()->create([
        'encuesta_id' => $enc1->id,
    ]);

    Livewire::actingAs($superAdmin)
        ->test(EncuestasTable::class)
        ->set('selectedTokens', [(string) $enc1->id, (string) $enc2->id])
        ->call('confirmarEliminacion')
        ->call('eliminarTokensSeleccionados')
        ->assertDispatched('notify', fn ($name, $params) => str_contains($params['mensaje'], '1 token(s) eliminado(s) correctamente') &&
            str_contains($params['mensaje'], '1 token(s) omitido(s) por tener datos asociados inconsistentes (contacta soporte técnico para revisarlos manualmente)')
        );

    expect(Encuesta::find($enc1->id))->not->toBeNull();
    expect(\App\Models\DatoDemografico::find($datoDemo->id))->not->toBeNull();
    expect(Encuesta::find($enc2->id))->toBeNull();

    $lote->refresh();
    expect($lote->tokens_total)->toBe(1);
});

it('elimina automáticamente el lote cuando se eliminan todos sus tokens', function () {
    $empresa = Empresa::factory()->create();
    $superAdmin = User::factory()->superAdmin()->create();

    $lote = Lote::create([
        'empresa_id' => $empresa->id,
        'user_id' => $superAdmin->id,
        'tokens_total' => 2,
        'nombre' => 'Lote a vaciar',
        'fecha_inicio' => now()->toDateString(),
        'fecha_fin' => now()->addDays(10)->toDateString(),
        'activo' => true,
    ]);

    $enc1 = Encuesta::create(['token' => 'TK-TEST-1111', 'estado' => 'disponible', 'lote_id' => $lote->id]);
    $enc2 = Encuesta::create(['token' => 'TK-TEST-2222', 'estado' => 'disponible', 'lote_id' => $lote->id]);

    Livewire::actingAs($superAdmin)
        ->test(EncuestasTable::class)
        ->set('selectedTokens', [(string) $enc1->id, (string) $enc2->id])
        ->call('confirmarEliminacion')
        ->call('eliminarTokensSeleccionados');

    expect(Encuesta::where('lote_id', $lote->id)->count())->toBe(0);
    expect(Lote::find($lote->id))->toBeNull();
});

it('un rol distinto a super_admin no puede eliminar tokens ni ver checkboxes', function () {
    $empresa = Empresa::factory()->create();
    $adminEmpresa = User::factory()->adminEmpresa($empresa->id)->create();

    $lote = Lote::create([
        'empresa_id' => $empresa->id,
        'user_id' => $adminEmpresa->id,
        'tokens_total' => 3,
        'nombre' => 'Lote Protegido',
        'fecha_inicio' => now()->toDateString(),
        'fecha_fin' => now()->addDays(10)->toDateString(),
        'activo' => true,
    ]);

    $enc1 = Encuesta::create(['token' => 'TK-TEST-3333', 'estado' => 'disponible', 'lote_id' => $lote->id]);

    Livewire::actingAs($adminEmpresa)
        ->test(EncuestasTable::class)
        ->set('selectedTokens', [(string) $enc1->id])
        ->call('confirmarEliminacion')
        ->assertSet('confirmandoEliminacion', false);

    expect(Encuesta::find($enc1->id))->not->toBeNull();
    $lote->refresh();
    expect($lote->tokens_total)->toBe(3);
});

it('permite la eliminación masiva de tokens pertenecientes a múltiples lotes distintos decrementando independientemente cada lote', function () {
    $empresa = Empresa::factory()->create();
    $superAdmin = User::factory()->superAdmin()->create();

    $loteA = Lote::create([
        'empresa_id' => $empresa->id,
        'user_id' => $superAdmin->id,
        'tokens_total' => 5,
        'nombre' => 'Lote A',
        'fecha_inicio' => now()->toDateString(),
        'fecha_fin' => now()->addDays(10)->toDateString(),
        'activo' => true,
    ]);

    $loteB = Lote::create([
        'empresa_id' => $empresa->id,
        'user_id' => $superAdmin->id,
        'tokens_total' => 4,
        'nombre' => 'Lote B',
        'fecha_inicio' => now()->toDateString(),
        'fecha_fin' => now()->addDays(10)->toDateString(),
        'activo' => true,
    ]);

    $encuestasA = [];
    for ($i = 0; $i < 3; $i++) {
        $encuestasA[] = Encuesta::create(['token' => 'TK-A-'.$i, 'estado' => 'disponible', 'lote_id' => $loteA->id]);
    }

    $encuestasB = [];
    for ($i = 0; $i < 2; $i++) {
        $encuestasB[] = Encuesta::create(['token' => 'TK-B-'.$i, 'estado' => 'disponible', 'lote_id' => $loteB->id]);
    }

    $seleccion = [(string) $encuestasA[0]->id, (string) $encuestasA[1]->id, (string) $encuestasB[0]->id];

    Livewire::actingAs($superAdmin)
        ->test(EncuestasTable::class)
        ->set('selectedTokens', $seleccion)
        ->call('confirmarEliminacion')
        ->call('eliminarTokensSeleccionados')
        ->assertDispatched('notify')
        ->assertSet('selectedTokens', []);

    $loteA->refresh();
    $loteB->refresh();

    expect($loteA->tokens_total)->toBe(3);
    expect(Encuesta::where('lote_id', $loteA->id)->count())->toBe(1);

    expect($loteB->tokens_total)->toBe(3);
    expect(Encuesta::where('lote_id', $loteB->id)->count())->toBe(1);
});

it('updatedSelectAll selecciona todos los tokens disponibles del filtro actual a través de múltiples páginas ignorando otros estados', function () {
    $empresa = Empresa::factory()->create();
    $superAdmin = User::factory()->superAdmin()->create();

    $lote = Lote::create([
        'empresa_id' => $empresa->id,
        'user_id' => $superAdmin->id,
        'tokens_total' => 20,
        'nombre' => 'Lote Multipagina',
        'fecha_inicio' => now()->toDateString(),
        'fecha_fin' => now()->addDays(10)->toDateString(),
        'activo' => true,
    ]);

    $disponiblesIds = [];
    for ($i = 0; $i < 16; $i++) {
        $enc = Encuesta::create(['token' => 'TK-DISP-'.$i, 'estado' => 'disponible', 'lote_id' => $lote->id]);
        $disponiblesIds[] = (string) $enc->id;
    }

    $encCompletada = Encuesta::create(['token' => 'TK-COMP-1', 'estado' => 'completado', 'lote_id' => $lote->id]);
    $encAsignada = Encuesta::create(['token' => 'TK-ASIG-1', 'estado' => 'asignado', 'lote_id' => $lote->id]);

    Livewire::actingAs($superAdmin)
        ->test(EncuestasTable::class)
        ->set('selectAll', true)
        ->assertSet('selectedTokens', function ($selectedTokens) use ($disponiblesIds, $encCompletada, $encAsignada) {
            expect(count($selectedTokens))->toBe(16);
            foreach ($disponiblesIds as $id) {
                expect($selectedTokens)->toContain($id);
            }
            expect($selectedTokens)->not->toContain((string) $encCompletada->id);
            expect($selectedTokens)->not->toContain((string) $encAsignada->id);

            return true;
        });
});

test('censura la segunda mitad del token en la tabla de encuestas para prevenir suplantación', function () {
    $empresa = Empresa::factory()->create();
    $superAdmin = User::factory()->superAdmin()->create();
    $lote = Lote::factory()->create(['empresa_id' => $empresa->id]);

    $encuesta = Encuesta::create([
        'token' => 'TK-ABCD-1234',
        'estado' => 'disponible',
        'lote_id' => $lote->id,
    ]);

    Livewire::actingAs($superAdmin)
        ->test(EncuestasTable::class)
        ->assertSee('TK-ABCD-••••')
        ->assertDontSee('TK-ABCD-1234');
});
