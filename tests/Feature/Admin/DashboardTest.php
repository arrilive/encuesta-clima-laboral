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
    $encuesta = Encuesta::factory()->completada()->create(['lote_id' => \App\Models\Lote::factory()->for($empresa)->create()->id]);

    $opcion = OpcionRespuesta::where('valor_numerico', 3)->first();
    $preguntas = Pregunta::all();

    foreach ($preguntas as $pregunta) {
        Respuesta::create([
            'encuesta_id' => $encuesta->id,
            'pregunta_id' => $pregunta->id,
            'opcion_respuesta_id' => $opcion->id,
        ]);
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
