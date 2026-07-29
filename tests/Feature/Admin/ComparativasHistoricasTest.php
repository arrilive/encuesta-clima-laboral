<?php

use App\Livewire\Admin\ComparativasHistoricas;
use App\Models\Dimension;
use App\Models\Empresa;
use App\Models\Encuesta;
use App\Models\Lote;
use App\Models\OpcionRespuesta;
use App\Models\Pregunta;
use App\Models\Respuesta;
use App\Models\User;
use Database\Seeders\DimensionesSeeder;
use Database\Seeders\OpcionesRespuestaSeeder;
use Database\Seeders\PreguntasSeeder;
use Database\Seeders\SubdimensionesSeeder;
use Livewire\Livewire;

it('el componente tendencias carga correctamente para admin_empresa', function () {
    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();

    Livewire::actingAs($admin)
        ->test(ComparativasHistoricas::class)
        ->assertSet('nivel', 1)
        ->assertSet('dimensionActivaId', null);
});

it('calcula correctamente delta positivo (subida), negativo (bajada) y neutro para general, dimensiones y subdimensiones', function () {
    $this->seed([DimensionesSeeder::class, SubdimensionesSeeder::class, PreguntasSeeder::class, OpcionesRespuestaSeeder::class]);

    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();

    $loteA = Lote::factory()->create(['empresa_id' => $empresa->id, 'fecha_inicio' => now()->subDays(30)]);
    $loteB = Lote::factory()->create(['empresa_id' => $empresa->id, 'fecha_inicio' => now()->subDays(5)]);

    $dimensiones = Dimension::with('subdimensiones')->orderBy('orden')->get();
    $dimSubida = $dimensiones[0];
    $dimBajada = $dimensiones[1];
    $dimNeutro = $dimensiones[2];

    $opcionMax = OpcionRespuesta::where('valor_numerico', 3)->first(); // 100 pts
    $opcionMin = OpcionRespuesta::where('valor_numerico', 1)->first(); // 0 pts
    $opcionMid = OpcionRespuesta::where('valor_numerico', 2)->first(); // 50 pts

    // Respuestas Lote A: dimSubida=0, dimBajada=100, dimNeutro=50
    $encA = Encuesta::factory()->completada()->create(['lote_id' => $loteA->id]);
    foreach (Pregunta::whereHas('subdimension', fn ($q) => $q->where('dimension_id', $dimSubida->id))->get() as $p) {
        Respuesta::create(['encuesta_id' => $encA->id, 'pregunta_id' => $p->id, 'opcion_respuesta_id' => $opcionMin->id]);
    }
    foreach (Pregunta::whereHas('subdimension', fn ($q) => $q->where('dimension_id', $dimBajada->id))->get() as $p) {
        Respuesta::create(['encuesta_id' => $encA->id, 'pregunta_id' => $p->id, 'opcion_respuesta_id' => $opcionMax->id]);
    }
    foreach (Pregunta::whereHas('subdimension', fn ($q) => $q->where('dimension_id', $dimNeutro->id))->get() as $p) {
        Respuesta::create(['encuesta_id' => $encA->id, 'pregunta_id' => $p->id, 'opcion_respuesta_id' => $opcionMid->id]);
    }

    // Respuestas Lote B: dimSubida=100 (+100 delta), dimBajada=0 (-100 delta), dimNeutro=50 (0 delta)
    $encB = Encuesta::factory()->completada()->create(['lote_id' => $loteB->id]);
    foreach (Pregunta::whereHas('subdimension', fn ($q) => $q->where('dimension_id', $dimSubida->id))->get() as $p) {
        Respuesta::create(['encuesta_id' => $encB->id, 'pregunta_id' => $p->id, 'opcion_respuesta_id' => $opcionMax->id]);
    }
    foreach (Pregunta::whereHas('subdimension', fn ($q) => $q->where('dimension_id', $dimBajada->id))->get() as $p) {
        Respuesta::create(['encuesta_id' => $encB->id, 'pregunta_id' => $p->id, 'opcion_respuesta_id' => $opcionMin->id]);
    }
    foreach (Pregunta::whereHas('subdimension', fn ($q) => $q->where('dimension_id', $dimNeutro->id))->get() as $p) {
        Respuesta::create(['encuesta_id' => $encB->id, 'pregunta_id' => $p->id, 'opcion_respuesta_id' => $opcionMid->id]);
    }

    $component = Livewire::actingAs($admin)
        ->test(ComparativasHistoricas::class)
        ->set('loteIdA', (string) $loteA->id)
        ->set('loteIdB', (string) $loteB->id);

    $datosDimensiones = collect($component->viewData('datosDimensiones'));

    $itemSubida = $datosDimensiones->firstWhere('id', $dimSubida->id);
    expect($itemSubida['badge']['direccion'])->toBe('subida');
    expect($itemSubida['badge']['delta'])->toBe(100.0);

    $itemBajada = $datosDimensiones->firstWhere('id', $dimBajada->id);
    expect($itemBajada['badge']['direccion'])->toBe('bajada');
    expect($itemBajada['badge']['delta'])->toBe(-100.0);

    $itemNeutro = $datosDimensiones->firstWhere('id', $dimNeutro->id);
    expect($itemNeutro['badge']['direccion'])->toBe('neutro');
    expect($itemNeutro['badge']['delta'])->toBe(0.0);
});

it('navegacion irNivel2 muestra subdimensiones y irNivel1 regresa a nivel 1', function () {
    $this->seed([DimensionesSeeder::class, SubdimensionesSeeder::class, PreguntasSeeder::class, OpcionesRespuestaSeeder::class]);

    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();
    $dimension = Dimension::first();

    Livewire::actingAs($admin)
        ->test(ComparativasHistoricas::class)
        ->call('irNivel2', $dimension->id)
        ->assertSet('nivel', 2)
        ->assertSet('dimensionActivaId', $dimension->id)
        ->call('irNivel1')
        ->assertSet('nivel', 1)
        ->assertSet('dimensionActivaId', null);
});

it('admin_empresa no puede seleccionar lotes fuera de su alcance en ninguno de los selectores', function () {
    $this->seed([DimensionesSeeder::class, SubdimensionesSeeder::class, PreguntasSeeder::class, OpcionesRespuestaSeeder::class]);

    $empresaPropia = Empresa::factory()->create();
    $empresaAjena = Empresa::factory()->create();

    $admin = User::factory()->adminEmpresa($empresaPropia->id)->create();

    $lotePropio = Lote::factory()->create(['empresa_id' => $empresaPropia->id]);
    $loteAjeno = Lote::factory()->create(['empresa_id' => $empresaAjena->id]);

    $opcionMax = OpcionRespuesta::where('valor_numerico', 3)->first();

    $encAjena = Encuesta::factory()->completada()->create(['lote_id' => $loteAjeno->id]);
    Respuesta::create([
        'encuesta_id' => $encAjena->id,
        'pregunta_id' => Pregunta::first()->id,
        'opcion_respuesta_id' => $opcionMax->id,
    ]);

    $encPropia = Encuesta::factory()->completada()->create(['lote_id' => $lotePropio->id]);
    Respuesta::create([
        'encuesta_id' => $encPropia->id,
        'pregunta_id' => Pregunta::first()->id,
        'opcion_respuesta_id' => $opcionMax->id,
    ]);

    $test = Livewire::actingAs($admin)
        ->test(ComparativasHistoricas::class)
        ->set('loteIdA', (string) $loteAjeno->id)
        ->set('loteIdB', (string) $lotePropio->id);

    expect($test->viewData('loteA'))->toBeNull();
    expect($test->viewData('loteB')->id)->toBe($lotePropio->id);
});

it('muestra sin datos N/A cuando se compara un lote con respuestas contra un lote sin respuestas', function () {
    $this->seed([DimensionesSeeder::class, SubdimensionesSeeder::class, PreguntasSeeder::class, OpcionesRespuestaSeeder::class]);

    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();

    $loteConDatos = Lote::factory()->create(['empresa_id' => $empresa->id, 'fecha_inicio' => now()->subDays(30)]);
    $loteSinDatos = Lote::factory()->create(['empresa_id' => $empresa->id, 'fecha_inicio' => now()->subDays(5)]);

    $opcionMax = OpcionRespuesta::where('valor_numerico', 3)->first();

    $enc = Encuesta::factory()->completada()->create(['lote_id' => $loteConDatos->id]);
    foreach (Pregunta::all() as $p) {
        Respuesta::create(['encuesta_id' => $enc->id, 'pregunta_id' => $p->id, 'opcion_respuesta_id' => $opcionMax->id]);
    }

    $dimension = Dimension::first();

    $component = Livewire::actingAs($admin)
        ->test(ComparativasHistoricas::class)
        ->set('loteIdA', (string) $loteConDatos->id)
        ->set('loteIdB', (string) $loteSinDatos->id)
        ->call('irNivel2', $dimension->id);

    expect($component->viewData('promedioGeneralB'))->toBeNull();

    $datosSubdimensiones = collect($component->viewData('datosSubdimensiones'));
    foreach ($datosSubdimensiones as $sub) {
        expect($sub['puntajeB'])->toBeNull();
        expect($sub['badge']['direccion'])->toBe('sin_datos');
        expect($sub['badge']['formatted'])->toBe('N/A');
    }
});

// ── Modo Historial ────────────────────────────────────────────────────────────

it('modo historial: los lotes activos (activo=true y sin fecha_fin pasada) no aparecen en lotesHistorial', function () {
    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();

    // Lote cerrado (activo=false) — debe aparecer
    $loteCerrado = Lote::factory()->create([
        'empresa_id' => $empresa->id,
        'activo' => false,
        'fecha_inicio' => now()->subDays(60),
        'fecha_fin' => now()->subDays(30),
    ]);

    // Lote activo — NO debe aparecer
    $loteActivo = Lote::factory()->create([
        'empresa_id' => $empresa->id,
        'activo' => true,
        'fecha_inicio' => now()->subDays(10),
        'fecha_fin' => now()->addDays(20),
    ]);

    $component = Livewire::actingAs($admin)
        ->test(ComparativasHistoricas::class)
        ->set('modo', 'historial');

    $lotesHistorial = $component->viewData('lotesHistorial');

    expect($lotesHistorial->pluck('id')->toArray())->toContain($loteCerrado->id);
    expect($lotesHistorial->pluck('id')->toArray())->not->toContain($loteActivo->id);
});

it('modo historial: los lotes cerrados se ordenan cronológicamente por fecha_inicio ASC', function () {
    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();

    $loteReciente = Lote::factory()->create([
        'empresa_id' => $empresa->id,
        'activo' => false,
        'fecha_inicio' => now()->subDays(10),
        'fecha_fin' => now()->subDays(3),
    ]);

    $loteAntiguo = Lote::factory()->create([
        'empresa_id' => $empresa->id,
        'activo' => false,
        'fecha_inicio' => now()->subDays(90),
        'fecha_fin' => now()->subDays(60),
    ]);

    $component = Livewire::actingAs($admin)
        ->test(ComparativasHistoricas::class)
        ->set('modo', 'historial');

    $ids = $component->viewData('lotesHistorial')->pluck('id')->toArray();

    // El lote más antiguo debe estar primero (ASC)
    expect($ids[0])->toBe($loteAntiguo->id);
    expect($ids[1])->toBe($loteReciente->id);
});

it('modo historial: admin_empresa no ve en timeline los lotes de otra empresa', function () {
    $empresaPropia = Empresa::factory()->create();
    $empresaAjena = Empresa::factory()->create();

    $admin = User::factory()->adminEmpresa($empresaPropia->id)->create();

    Lote::factory()->create([
        'empresa_id' => $empresaAjena->id,
        'activo' => false,
        'fecha_inicio' => now()->subDays(60),
        'fecha_fin' => now()->subDays(30),
    ]);

    $lotePropio = Lote::factory()->create([
        'empresa_id' => $empresaPropia->id,
        'activo' => false,
        'fecha_inicio' => now()->subDays(60),
        'fecha_fin' => now()->subDays(30),
    ]);

    $component = Livewire::actingAs($admin)
        ->test(ComparativasHistoricas::class)
        ->set('modo', 'historial');

    $ids = $component->viewData('lotesHistorial')->pluck('id')->toArray();

    expect($ids)->toContain($lotePropio->id);
    expect(count($ids))->toBe(1);
});

it('modo historial: un lote cerrado sin respuestas tiene promedio_general null en timeline', function () {
    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();

    // Lote cerrado sin ninguna encuesta completada
    Lote::factory()->create([
        'empresa_id' => $empresa->id,
        'activo' => false,
        'fecha_inicio' => now()->subDays(60),
        'fecha_fin' => now()->subDays(30),
    ]);

    $component = Livewire::actingAs($admin)
        ->test(ComparativasHistoricas::class)
        ->set('modo', 'historial');

    $timeline = $component->viewData('timeline');

    expect($timeline)->toHaveCount(1);
    expect($timeline->first()['promedio_general'])->toBeNull();
});

it('modo historial: un lote activo=true pero con fecha_fin pasada sí aparece (expiró naturalmente)', function () {
    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();

    $loteExpirado = Lote::factory()->create([
        'empresa_id' => $empresa->id,
        'activo' => true,
        'fecha_inicio' => now()->subDays(60),
        'fecha_fin' => now()->subDays(5),
    ]);

    $component = Livewire::actingAs($admin)
        ->test(ComparativasHistoricas::class)
        ->set('modo', 'historial');

    $ids = $component->viewData('lotesHistorial')->pluck('id')->toArray();

    expect($ids)->toContain($loteExpirado->id);
});
