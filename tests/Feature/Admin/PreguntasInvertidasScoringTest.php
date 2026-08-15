<?php

use App\Models\Empresa;
use App\Models\Encuesta;
use App\Models\Lote;
use App\Models\OpcionRespuesta;
use App\Models\Pregunta;
use App\Models\Respuesta;
use App\Models\Subdimension;
use App\Models\User;
use App\Services\ClimaScoringService;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed();
});

test('pregunta invertida con respuesta Falso equivale a 3 puntos (100%) y Verdadero equivale a 1 punto (0%), a diferencia de una pregunta normal', function () {
    $admin = User::factory()->superAdmin()->create();
    $this->actingAs($admin);

    $empresa = Empresa::factory()->create();
    $lote = Lote::factory()->create(['empresa_id' => $empresa->id, 'activo' => true]);

    $opcionFalso = OpcionRespuesta::where('valor_numerico', 1)->first();
    $opcionVerdadero = OpcionRespuesta::where('valor_numerico', 3)->first();

    $subSeguridad = Subdimension::where('nombre', 'Seguridad')->first();
    $preguntaNormal = Pregunta::where('subdimension_id', $subSeguridad->id)->where('orden', 1)->first();
    $preguntaInvertida = Pregunta::where('subdimension_id', $subSeguridad->id)->where('orden', 2)->first();

    expect($preguntaNormal->invertida)->toBeFalse();
    expect($preguntaInvertida->invertida)->toBeTrue();

    // Encuesta A: Responde "Falso" (val 1) en pregunta invertida
    $encuestaA = Encuesta::factory()->completada()->create(['lote_id' => $lote->id]);
    Respuesta::create([
        'encuesta_id' => $encuestaA->id,
        'pregunta_id' => $preguntaInvertida->id,
        'opcion_respuesta_id' => $opcionFalso->id,
    ]);

    // Encuesta B: Responde "Verdadero" (val 3) en pregunta invertida
    $encuestaB = Encuesta::factory()->completada()->create(['lote_id' => $lote->id]);
    Respuesta::create([
        'encuesta_id' => $encuestaB->id,
        'pregunta_id' => $preguntaInvertida->id,
        'opcion_respuesta_id' => $opcionVerdadero->id,
    ]);

    // Encuesta C: Responde "Falso" (val 1) en pregunta normal
    $encuestaC = Encuesta::factory()->completada()->create(['lote_id' => $lote->id]);
    Respuesta::create([
        'encuesta_id' => $encuestaC->id,
        'pregunta_id' => $preguntaNormal->id,
        'opcion_respuesta_id' => $opcionFalso->id,
    ]);

    $scoring = app(ClimaScoringService::class);

    // Scoring para encuesta A (invertida + Falso -> val 3 = 100%)
    $baseQueryA = Respuesta::where('encuesta_id', $encuestaA->id);
    $scoresA = $scoring->scoresPorSubdimension($baseQueryA);
    $scoreSeguridadA = $scoresA->firstWhere('id', $subSeguridad->id)['puntaje'];
    expect($scoreSeguridadA)->toBe(100.0);

    // Scoring para encuesta B (invertida + Verdadero -> val 1 = 0%)
    $baseQueryB = Respuesta::where('encuesta_id', $encuestaB->id);
    $scoresB = $scoring->scoresPorSubdimension($baseQueryB);
    $scoreSeguridadB = $scoresB->firstWhere('id', $subSeguridad->id)['puntaje'];
    expect($scoreSeguridadB)->toBe(0.0);

    // Scoring para encuesta C (normal + Falso -> val 1 = 0%)
    $baseQueryC = Respuesta::where('encuesta_id', $encuestaC->id);
    $scoresC = $scoring->scoresPorSubdimension($baseQueryC);
    $scoreSeguridadC = $scoresC->firstWhere('id', $subSeguridad->id)['puntaje'];
    expect($scoreSeguridadC)->toBe(0.0);
});

test('respuestas con Prefiero no responder (valor_numerico 0) en preguntas invertidas quedan excluidas del cálculo', function () {
    $admin = User::factory()->superAdmin()->create();
    $this->actingAs($admin);

    $empresa = Empresa::factory()->create();
    $lote = Lote::factory()->create(['empresa_id' => $empresa->id, 'activo' => true]);

    $opcionCero = OpcionRespuesta::where('valor_numerico', 0)->first();
    $subSeguridad = Subdimension::where('nombre', 'Seguridad')->first();
    $preguntaInvertida = Pregunta::where('subdimension_id', $subSeguridad->id)->where('orden', 2)->first();

    $encuesta = Encuesta::factory()->completada()->create(['lote_id' => $lote->id]);
    Respuesta::create([
        'encuesta_id' => $encuesta->id,
        'pregunta_id' => $preguntaInvertida->id,
        'opcion_respuesta_id' => $opcionCero->id,
    ]);

    $scoring = app(ClimaScoringService::class);
    $baseQuery = Respuesta::where('encuesta_id', $encuesta->id);
    $scores = $scoring->scoresPorSubdimension($baseQuery);
    $scoreSeguridad = $scores->firstWhere('id', $subSeguridad->id)['puntaje'];

    expect($scoreSeguridad)->toBe(0.0);
});

test('las 16 subdimensiones sin preguntas invertidas mantienen su comportamiento en Nivel 2 (titulo Distribución de Respuestas y agrupamiento literal)', function () {
    $admin = User::factory()->superAdmin()->create();
    $this->actingAs($admin);

    $empresa = Empresa::factory()->create();
    $lote = Lote::factory()->create(['empresa_id' => $empresa->id, 'activo' => true]);

    // Subdimensión Comunicación (sin preguntas invertidas)
    $subComunicacion = Subdimension::where('nombre', 'Comunicación')->first();
    $preguntaCom = Pregunta::where('subdimension_id', $subComunicacion->id)->first();
    $opcionVerdadero = OpcionRespuesta::where('valor_numerico', 3)->first();

    $encuestas = Encuesta::factory()->count(5)->completada()->create(['lote_id' => $lote->id]);
    foreach ($encuestas as $encuesta) {
        Respuesta::create([
            'encuesta_id' => $encuesta->id,
            'pregunta_id' => $preguntaCom->id,
            'opcion_respuesta_id' => $opcionVerdadero->id,
        ]);
    }

    $component = Livewire::test(\App\Livewire\Admin\Reportes::class)
        ->set('filtroLoteId', $lote->id)
        ->call('irNivel2', $subComunicacion->dimension_id);

    expect($component->instance()->tieneInvertidasEnNivel2())->toBeFalse();
    $distribucion = $component->instance()->getDistribucionAgregadaNivel2();

    // Debe agrupar por texto literal 'Verdadero'
    expect($distribucion)->toBeArray();
    expect($distribucion[0]['opcion'])->toBe('Verdadero');
    expect($distribucion[0]['total'])->toBe(5);

    $component->assertSee('Distribución de Respuestas')
        ->assertDontSee('Distribución de Percepción');
});

test('subdimensión Seguridad en Nivel 2 cambia a titulo Distribución de Percepción y agrupa por Favorable / Neutral / Desfavorable', function () {
    $admin = User::factory()->superAdmin()->create();
    $this->actingAs($admin);

    $empresa = Empresa::factory()->create();
    $lote = Lote::factory()->create(['empresa_id' => $empresa->id, 'activo' => true]);

    $subSeguridad = Subdimension::where('nombre', 'Seguridad')->first();
    $preguntaInvertida = Pregunta::where('subdimension_id', $subSeguridad->id)->where('orden', 2)->first();
    $opcionFalso = OpcionRespuesta::where('valor_numerico', 1)->first();

    $encuestas = Encuesta::factory()->count(5)->completada()->create(['lote_id' => $lote->id]);
    foreach ($encuestas as $encuesta) {
        Respuesta::create([
            'encuesta_id' => $encuesta->id,
            'pregunta_id' => $preguntaInvertida->id,
            'opcion_respuesta_id' => $opcionFalso->id,
        ]);
    }

    $component = Livewire::test(\App\Livewire\Admin\Reportes::class)
        ->set('filtroLoteId', $lote->id)
        ->call('irNivel2', $subSeguridad->dimension_id);

    expect($component->instance()->tieneInvertidasEnNivel2())->toBeTrue();
    $distribucion = $component->instance()->getDistribucionAgregadaNivel2();

    expect($distribucion)->toBeArray();
    expect($distribucion[0]['opcion'])->toBe('Favorable');
    expect($distribucion[0]['total'])->toBe(5);

    $component->assertSee('Distribución de Percepción');
});
