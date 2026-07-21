<?php

use App\Livewire\Admin\Dashboard;
use App\Models\Empresa;
use App\Models\Encuesta;
use App\Models\OpcionRespuesta;
use App\Models\Pregunta;
use App\Models\Respuesta;
use App\Models\User;
use Database\Seeders\DimensionesSeeder;
use Database\Seeders\OpcionesRespuestaSeeder;
use Database\Seeders\PreguntasSeeder;
use Database\Seeders\SubdimensionesSeeder;
use Livewire\Livewire;

it('super_admin puede acceder al dashboard', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk();
});

it('admin_empresa puede acceder al dashboard', function () {
    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk();
});

it('usuario sin autenticar no puede acceder al dashboard', function () {
    $this->get(route('admin.dashboard'))
        ->assertRedirect(route('login'));
});

it('los KPIs del super_admin incluyen todas las empresas', function () {
    $empresa1 = Empresa::factory()->create();
    $empresa2 = Empresa::factory()->create();

    Encuesta::factory()->count(3)->create(['lote_id' => \App\Models\Lote::factory()->for($empresa1)->create()->id, 'estado' => 'completado']);
    Encuesta::factory()->count(2)->create(['lote_id' => \App\Models\Lote::factory()->for($empresa2)->create()->id, 'estado' => 'disponible']);

    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin);

    $kpis = Livewire::test(Dashboard::class)->viewData('kpis');

    expect($kpis['total_tokens'])->toBe(5)
        ->and($kpis['completadas'])->toBe(3)
        ->and($kpis['disponibles'])->toBe(2);
});

it('los KPIs del admin_empresa solo incluyen su empresa', function () {
    $empresa1 = Empresa::factory()->create();
    $empresa2 = Empresa::factory()->create();

    Encuesta::factory()->count(3)->create(['lote_id' => \App\Models\Lote::factory()->for($empresa1)->create()->id]);
    Encuesta::factory()->count(2)->create(['lote_id' => \App\Models\Lote::factory()->for($empresa2)->create()->id]);

    $admin = User::factory()->adminEmpresa($empresa1->id)->create();

    $this->actingAs($admin);

    $kpis = Livewire::test(Dashboard::class)->viewData('kpis');

    expect($kpis['total_tokens'])->toBe(3);
});

// ── Nuevos tests: KPIs avanzados ─────────────────────────────────────────────

it('alerta_tokens es true cuando no hay tokens', function () {
    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();

    $this->actingAs($admin);

    $kpis = Livewire::test(Dashboard::class)->viewData('kpis');

    expect($kpis['alerta_tokens'])->toBeTrue();
});

it('alerta_tokens se activa cuando disponibles son menos del 10% del total', function () {
    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();

    Encuesta::factory()->count(9)->create(['lote_id' => \App\Models\Lote::factory()->for($empresa)->create()->id, 'estado' => 'completado']);
    Encuesta::factory()->create(['lote_id' => \App\Models\Lote::factory()->for($empresa)->create()->id, 'estado' => 'disponible']);

    $this->actingAs($admin);

    $kpis = Livewire::test(Dashboard::class)->viewData('kpis');

    expect($kpis['alerta_tokens'])->toBeFalse()
        ->and($kpis['tasa_participacion'])->toBe(90.0);

    Encuesta::whereHas('lote', fn ($q) => $q->where('empresa_id', $empresa->id))->where('estado', 'disponible')
        ->update(['estado' => 'completado']);

    $kpis = Livewire::test(Dashboard::class)->viewData('kpis');

    expect($kpis['alerta_tokens'])->toBeTrue();
});

it('en_riesgo y en_advertencia cuentan tokens asignados basados en 14 y 7 días', function () {
    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();

    // En riesgo: asignado hace 15 días
    Encuesta::factory()->create([
        'lote_id' => \App\Models\Lote::factory()->for($empresa)->create()->id,
        'estado' => 'asignado',
        'fecha_asignacion' => now()->subDays(15),
    ]);

    // En advertencia: asignado hace 8 días
    Encuesta::factory()->create([
        'lote_id' => \App\Models\Lote::factory()->for($empresa)->create()->id,
        'estado' => 'asignado',
        'fecha_asignacion' => now()->subDays(8),
    ]);

    // No en riesgo ni advertencia: asignado hace 3 días
    Encuesta::factory()->create([
        'lote_id' => \App\Models\Lote::factory()->for($empresa)->create()->id,
        'estado' => 'asignado',
        'fecha_asignacion' => now()->subDays(3),
    ]);

    $this->actingAs($admin);

    $kpis = Livewire::test(Dashboard::class)->viewData('kpis');

    expect($kpis['en_riesgo'])->toBe(1)
        ->and($kpis['en_advertencia'])->toBe(1);
});

// ── Nuevos tests: visibilidad por rol ────────────────────────────────────────

it('clima solo se pasa a la vista para admin_empresa', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin);

    $clima = Livewire::test(Dashboard::class)->viewData('clima');

    expect($clima)->toBeEmpty();
});

it('rankingEmpresas está vacío para admin_empresa', function () {
    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();

    $this->actingAs($admin);

    $rankingEmpresas = Livewire::test(Dashboard::class)->viewData('rankingEmpresas');

    expect($rankingEmpresas)->toBeEmpty();
});

it('clima contiene promedio_general para admin_empresa cuando hay respuestas', function () {
    $this->seed([DimensionesSeeder::class, SubdimensionesSeeder::class, PreguntasSeeder::class, OpcionesRespuestaSeeder::class]);

    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();
    $lote = \App\Models\Lote::factory()->for($empresa)->create();

    $opcion = OpcionRespuesta::where('valor_numerico', 3)->first();
    $preguntas = Pregunta::all();

    for ($i = 0; $i < 5; $i++) {
        $encuesta = Encuesta::factory()->completada()->create(['lote_id' => $lote->id]);
        foreach ($preguntas as $pregunta) {
            Respuesta::create([
                'encuesta_id' => $encuesta->id,
                'pregunta_id' => $pregunta->id,
                'opcion_respuesta_id' => $opcion->id,
            ]);
        }
    }

    $this->actingAs($admin);

    $clima = Livewire::test(Dashboard::class)->viewData('clima');

    expect($clima['promedio_general'])->toBe(100.0);
});

it('filtroLoteId filtra KPIs al lote seleccionado', function () {
    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();

    $lote1 = \App\Models\Lote::factory()->for($empresa)->create();
    $lote2 = \App\Models\Lote::factory()->for($empresa)->create();

    Encuesta::factory()->count(3)->create(['lote_id' => $lote1->id]);
    Encuesta::factory()->count(2)->create(['lote_id' => $lote2->id]);

    $this->actingAs($admin);

    $component = Livewire::test(Dashboard::class);
    expect($component->viewData('kpis')['total_tokens'])->toBe(5);

    $component->set('filtroLoteId', (string) $lote1->id);
    expect($component->viewData('kpis')['total_tokens'])->toBe(3);

    $component->set('filtroLoteId', (string) $lote2->id);
    expect($component->viewData('kpis')['total_tokens'])->toBe(2);
});

it('filtroSucursalId filtra KPIs a la sucursal seleccionada', function () {
    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();

    $sucursal = \App\Models\Sucursal::factory()->create(['empresa_id' => $empresa->id]);

    $loteGeneral = \App\Models\Lote::factory()->for($empresa)->create(['sucursal_id' => null]);
    $loteSucursal = \App\Models\Lote::factory()->for($empresa)->create(['sucursal_id' => $sucursal->id]);

    Encuesta::factory()->count(3)->create(['lote_id' => $loteGeneral->id]);
    Encuesta::factory()->count(2)->create(['lote_id' => $loteSucursal->id]);

    $this->actingAs($admin);

    $component = Livewire::test(Dashboard::class);
    expect($component->viewData('kpis')['total_tokens'])->toBe(5);

    $component->set('filtroSucursalId', (string) $sucursal->id);
    expect($component->viewData('kpis')['total_tokens'])->toBe(2);

    $component->set('filtroSucursalId', '');
    expect($component->viewData('kpis')['total_tokens'])->toBe(5);
});

it('admin_corporativo no puede liberar tokens en riesgo', function () {
    $corporativo = \App\Models\Corporativo::factory()->create();
    $empresa = Empresa::factory()->create(['corporativo_id' => $corporativo->id]);
    $lote = \App\Models\Lote::factory()->for($empresa)->create();
    $encuesta = Encuesta::factory()->create([
        'lote_id' => $lote->id,
        'estado' => 'asignado',
        'fecha_asignacion' => now()->subDays(20),
    ]);

    $admin = User::factory()->adminCorporativo($corporativo->id)->create();

    $this->actingAs($admin);

    Livewire::test(Dashboard::class)->call('liberarTokens');

    expect($encuesta->fresh()->estado)->toBe('asignado');
});

it('admin_empresa puede liberar tokens en riesgo de su empresa', function () {
    $empresa = Empresa::factory()->create();
    $lote = \App\Models\Lote::factory()->for($empresa)->create();
    $encuesta = Encuesta::factory()->create([
        'lote_id' => $lote->id,
        'estado' => 'asignado',
        'fecha_asignacion' => now()->subDays(20),
    ]);

    $admin = User::factory()->adminEmpresa($empresa->id)->create();

    $this->actingAs($admin);

    Livewire::test(Dashboard::class)->call('liberarTokens');

    expect($encuesta->fresh())
        ->estado->toBe('disponible')
        ->fecha_asignacion->toBeNull();
});

it('admin_sucursal puede liberar tokens en riesgo de su sucursal', function () {
    $empresa = Empresa::factory()->create();
    $sucursal = \App\Models\Sucursal::factory()->create(['empresa_id' => $empresa->id]);
    $lote = \App\Models\Lote::factory()->create(['empresa_id' => $empresa->id, 'sucursal_id' => $sucursal->id]);
    $encuesta = Encuesta::factory()->create([
        'lote_id' => $lote->id,
        'estado' => 'asignado',
        'fecha_asignacion' => now()->subDays(20),
    ]);

    $admin = User::factory()->adminSucursal($sucursal->id)->create();

    $this->actingAs($admin);

    Livewire::test(Dashboard::class)->call('liberarTokens');

    expect($encuesta->fresh())
        ->estado->toBe('disponible')
        ->fecha_asignacion->toBeNull();
});

it('super_admin puede liberar cualquier token en riesgo', function () {
    $empresa = Empresa::factory()->create();
    $lote = \App\Models\Lote::factory()->for($empresa)->create();
    $encuesta = Encuesta::factory()->create([
        'lote_id' => $lote->id,
        'estado' => 'asignado',
        'fecha_asignacion' => now()->subDays(20),
    ]);

    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin);

    Livewire::test(Dashboard::class)->call('liberarTokens');

    expect($encuesta->fresh())
        ->estado->toBe('disponible')
        ->fecha_asignacion->toBeNull();
});

it('clima muestra escenario 1 (vacio) cuando no hay ningun lote', function () {
    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();

    $this->actingAs($admin);

    $component = Livewire::test(Dashboard::class);
    $clima = $component->viewData('clima');

    expect($clima['sinDatos'])->toBeTrue()
        ->and($clima['escenario'])->toBe(1);

    $component->assertSee('Sin datos de clima');
});

it('clima muestra escenario 2 (lote activo) cuando hay un lote activo sin cerrado previo', function () {
    $this->seed([DimensionesSeeder::class, SubdimensionesSeeder::class, PreguntasSeeder::class, OpcionesRespuestaSeeder::class]);
    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();

    $loteActivo = \App\Models\Lote::factory()->create([
        'empresa_id' => $empresa->id,
        'fecha_inicio' => now()->subDays(5),
        'fecha_fin' => null,
        'activo' => true,
        'nombre' => 'Ronda Activa 2026',
    ]);

    // Ponemos 5 respuestas para pasar el umbral
    $opcion = OpcionRespuesta::where('valor_numerico', 3)->first();
    for ($i = 0; $i < 5; $i++) {
        $enc = Encuesta::factory()->completada()->create(['lote_id' => $loteActivo->id]);
        foreach (Pregunta::all() as $pregunta) {
            Respuesta::create([
                'encuesta_id' => $enc->id,
                'pregunta_id' => $pregunta->id,
                'opcion_respuesta_id' => $opcion->id,
            ]);
        }
    }

    $this->actingAs($admin);

    $component = Livewire::test(Dashboard::class);
    $clima = $component->viewData('clima');

    expect($clima['sinDatos'])->toBeFalse()
        ->and($clima['escenario'])->toBe(2)
        ->and($clima['promedio_general'])->toBe(100.0);

    $component->assertSee('Ronda')
        ->assertSee('Ronda Activa 2026')
        ->assertSee('en curso — resultados parciales.');
});

it('clima muestra escenario 3 (lote cerrado) cuando hay un lote cerrado sin lote activo', function () {
    $this->seed([DimensionesSeeder::class, SubdimensionesSeeder::class, PreguntasSeeder::class, OpcionesRespuestaSeeder::class]);
    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();

    $loteCerrado = \App\Models\Lote::factory()->create([
        'empresa_id' => $empresa->id,
        'fecha_inicio' => now()->subDays(20),
        'fecha_fin' => now()->subDays(5),
        'activo' => false,
        'nombre' => 'Ronda Cerrada 2026',
    ]);

    // Ponemos 5 respuestas para pasar el umbral
    $opcion = OpcionRespuesta::where('valor_numerico', 3)->first();
    for ($i = 0; $i < 5; $i++) {
        $enc = Encuesta::factory()->completada()->create(['lote_id' => $loteCerrado->id]);
        foreach (Pregunta::all() as $pregunta) {
            Respuesta::create([
                'encuesta_id' => $enc->id,
                'pregunta_id' => $pregunta->id,
                'opcion_respuesta_id' => $opcion->id,
            ]);
        }
    }

    $this->actingAs($admin);

    $component = Livewire::test(Dashboard::class);
    $clima = $component->viewData('clima');

    expect($clima['sinDatos'])->toBeFalse()
        ->and($clima['escenario'])->toBe(3)
        ->and($clima['promedio_general'])->toBe(100.0);

    $component->assertSee('Estado actual: ronda')
        ->assertSee('Ronda Cerrada 2026')
        ->assertSee('cerrada el '.$loteCerrado->fecha_fin->format('d/m/Y'));
});

it('clima muestra escenario 4 (lote cerrado + activo) cuando hay un lote cerrado y un lote activo', function () {
    $this->seed([DimensionesSeeder::class, SubdimensionesSeeder::class, PreguntasSeeder::class, OpcionesRespuestaSeeder::class]);
    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();

    $loteCerrado = \App\Models\Lote::factory()->create([
        'empresa_id' => $empresa->id,
        'fecha_inicio' => now()->subDays(20),
        'fecha_fin' => now()->subDays(5),
        'activo' => false,
        'nombre' => 'Ronda Cerrada 2026',
    ]);

    $loteActivo = \App\Models\Lote::factory()->create([
        'empresa_id' => $empresa->id,
        'fecha_inicio' => now()->subDays(3),
        'fecha_fin' => null,
        'activo' => true,
        'nombre' => 'Ronda Nueva 2026',
    ]);

    // Ponemos 5 respuestas en el cerrado, y 2 en el activo (que no se deben usar)
    $opcionMax = OpcionRespuesta::where('valor_numerico', 3)->first();
    $opcionMin = OpcionRespuesta::where('valor_numerico', 1)->first();

    for ($i = 0; $i < 5; $i++) {
        $enc = Encuesta::factory()->completada()->create(['lote_id' => $loteCerrado->id]);
        foreach (Pregunta::all() as $pregunta) {
            Respuesta::create([
                'encuesta_id' => $enc->id,
                'pregunta_id' => $pregunta->id,
                'opcion_respuesta_id' => $opcionMax->id,
            ]);
        }
    }

    for ($i = 0; $i < 2; $i++) {
        $enc = Encuesta::factory()->completada()->create(['lote_id' => $loteActivo->id]);
        foreach (Pregunta::all() as $pregunta) {
            Respuesta::create([
                'encuesta_id' => $enc->id,
                'pregunta_id' => $pregunta->id,
                'opcion_respuesta_id' => $opcionMin->id,
            ]);
        }
    }

    $this->actingAs($admin);

    $component = Livewire::test(Dashboard::class);
    $clima = $component->viewData('clima');

    // Debe mostrar la puntuación del lote cerrado (100.0) y no del activo
    expect($clima['sinDatos'])->toBeFalse()
        ->and($clima['escenario'])->toBe(4)
        ->and($clima['promedio_general'])->toBe(100.0);

    $component->assertSee('Hay una nueva ronda en curso')
        ->assertSee('Ronda Nueva 2026')
        ->assertSee('este panorama se actualizará cuando cierre.');
});

it('clima calcula y muestra promedio_general aun con solo 1 respuesta completada en el lote', function () {
    $this->seed([DimensionesSeeder::class, SubdimensionesSeeder::class, PreguntasSeeder::class, OpcionesRespuestaSeeder::class]);
    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();

    $lote = \App\Models\Lote::factory()->create([
        'empresa_id' => $empresa->id,
        'fecha_inicio' => now()->subDays(10),
        'fecha_fin' => now()->subDays(2),
        'activo' => false,
        'nombre' => 'Ronda Unica Respuesta',
    ]);

    // Solo 1 encuesta completada
    $opcion = OpcionRespuesta::where('valor_numerico', 3)->first();
    $enc = Encuesta::factory()->completada()->create(['lote_id' => $lote->id]);
    foreach (Pregunta::all() as $pregunta) {
        Respuesta::create([
            'encuesta_id' => $enc->id,
            'pregunta_id' => $pregunta->id,
            'opcion_respuesta_id' => $opcion->id,
        ]);
    }

    $this->actingAs($admin);

    $component = Livewire::test(Dashboard::class);
    $clima = $component->viewData('clima');

    expect($clima['sinDatos'])->toBeFalse()
        ->and($clima['promedio_general'])->toBe(100.0);

    $component->assertSee('100.0');
});
