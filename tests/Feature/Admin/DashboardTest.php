<?php

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

    Encuesta::factory()->count(3)->create(['empresa_id' => $empresa1->id, 'estado' => 'completado']);
    Encuesta::factory()->count(2)->create(['empresa_id' => $empresa2->id, 'estado' => 'disponible']);

    $admin = User::factory()->superAdmin()->create();

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertViewHas('kpis', fn($kpis) =>
        $kpis['total_tokens'] === 5 &&
        $kpis['completadas'] === 3 &&
        $kpis['disponibles'] === 2
    );
});

it('los KPIs del admin_empresa solo incluyen su empresa', function () {
    $empresa1 = Empresa::factory()->create();
    $empresa2 = Empresa::factory()->create();

    Encuesta::factory()->count(3)->create(['empresa_id' => $empresa1->id]);
    Encuesta::factory()->count(2)->create(['empresa_id' => $empresa2->id]);

    $admin = User::factory()->adminEmpresa($empresa1->id)->create();

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertViewHas('kpis', fn($kpis) => $kpis['total_tokens'] === 3);
});

// ── Nuevos tests: KPIs avanzados ─────────────────────────────────────────────

it('alerta_tokens es false cuando no hay tokens', function () {
    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertViewHas('kpis', fn($kpis) => $kpis['alerta_tokens'] === false);
});

it('alerta_tokens se activa cuando disponibles son menos del 10% del total', function () {
    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();

    // 10% exacto (1 de 10 disponible) → NO activa
    Encuesta::factory()->count(9)->create(['empresa_id' => $empresa->id, 'estado' => 'completado']);
    Encuesta::factory()->create(['empresa_id' => $empresa->id, 'estado' => 'disponible']);

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));
    $response->assertViewHas('kpis', fn($kpis) =>
        $kpis['alerta_tokens'] === false &&
        $kpis['tasa_participacion'] === 90.0
    );

    // 0 disponibles de 10 → SÍ activa (0% < 10%)
    Encuesta::where('empresa_id', $empresa->id)->where('estado', 'disponible')
        ->update(['estado' => 'completado']);

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));
    $response->assertViewHas('kpis', fn($kpis) => $kpis['alerta_tokens'] === true);
});

it('en_riesgo cuenta tokens asignados hace más de 7 días', function () {
    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();

    // En riesgo: asignado hace 8 días
    Encuesta::factory()->create([
        'empresa_id'       => $empresa->id,
        'estado'           => 'asignado',
        'fecha_asignacion' => now()->subDays(8),
    ]);

    // No en riesgo: asignado hace 3 días
    Encuesta::factory()->create([
        'empresa_id'       => $empresa->id,
        'estado'           => 'asignado',
        'fecha_asignacion' => now()->subDays(3),
    ]);

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertViewHas('kpis', fn($kpis) => $kpis['en_riesgo'] === 1);
});

// ── Nuevos tests: visibilidad por rol ────────────────────────────────────────

it('clima solo se pasa a la vista para admin_empresa', function () {
    $empresa = Empresa::factory()->create();
    $admin = User::factory()->superAdmin()->create();

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertViewHas('clima', fn($c) => empty($c));
});

it('rankingEmpresas está vacío para admin_empresa', function () {
    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertViewHas('rankingEmpresas', fn($r) => $r->isEmpty());
});

it('clima contiene promedio_general para admin_empresa cuando hay respuestas', function () {
    $this->seed([DimensionesSeeder::class, SubdimensionesSeeder::class, PreguntasSeeder::class, OpcionesRespuestaSeeder::class]);

    $empresa  = Empresa::factory()->create();
    $admin    = User::factory()->adminEmpresa($empresa->id)->create();
    $encuesta = Encuesta::factory()->completada()->create(['empresa_id' => $empresa->id]);

    $opcion    = OpcionRespuesta::where('valor_numerico', 3)->first();
    $preguntas = Pregunta::all();

    foreach ($preguntas as $pregunta) {
        Respuesta::create([
            'encuesta_id'         => $encuesta->id,
            'pregunta_id'         => $pregunta->id,
            'opcion_respuesta_id' => $opcion->id,
        ]);
    }

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertViewHas('clima', fn($c) => $c['promedio_general'] === 100.0);
});
