<?php

use App\Livewire\Encuesta\EncuestaBloque;
use App\Livewire\Encuesta\PreguntasAbiertas;
use App\Livewire\Encuesta\PreguntaCerrada;
use App\Models\Dimension;
use App\Models\Encuesta;
use App\Models\OpcionRespuesta;
use App\Models\Pregunta;
use App\Models\Respuesta;
use Database\Seeders\DimensionesSeeder;
use Database\Seeders\OpcionesRespuestaSeeder;
use Database\Seeders\PreguntasSeeder;
use Database\Seeders\SubdimensionesSeeder;
use Livewire\Livewire;

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function seedEncuesta(): void
{
    app()['db']->table('dimensiones')->count() === 0
        && (new DimensionesSeeder)->run();
    app()['db']->table('subdimensiones')->count() === 0
        && (new SubdimensionesSeeder)->run();
    app()['db']->table('preguntas')->count() === 0
        && (new PreguntasSeeder)->run();
    app()['db']->table('opciones_respuesta')->count() === 0
        && (new OpcionesRespuestaSeeder)->run();
}

// ---------------------------------------------------------------------------
// Carga de rutas
// ---------------------------------------------------------------------------

test('la ruta encuesta.bloque carga correctamente con token válido', function () {
    seedEncuesta();

    $encuesta  = Encuesta::factory()->asignada()->create();
    $dimension = Dimension::where('orden', 1)->first();

    $this->get(route('encuesta.bloque', [
        'token'     => $encuesta->token,
        'dimension' => $dimension->orden,
    ]))->assertOk()
       ->assertSeeLivewire(EncuestaBloque::class);
});

test('la ruta encuesta.bloque retorna 404 con token inválido', function () {
    seedEncuesta();

    $this->get(route('encuesta.bloque', [
        'token'     => 'token-falso',
        'dimension' => 1,
    ]))->assertNotFound();
});

test('la ruta encuesta.bloque retorna 404 con encuesta completada', function () {
    seedEncuesta();

    $encuesta = Encuesta::factory()->completada()->create();

    $this->get(route('encuesta.bloque', [
        'token'     => $encuesta->token,
        'dimension' => 1,
    ]))->assertNotFound();
});

// ---------------------------------------------------------------------------
// Guardado automático
// ---------------------------------------------------------------------------

test('seleccionar una opción guarda la respuesta en BD', function () {
    seedEncuesta();

    $encuesta = Encuesta::factory()->asignada()->create();
    $pregunta = Pregunta::first();
    $opcion   = OpcionRespuesta::first();

    Livewire::test(PreguntaCerrada::class, [
        'encuesta' => $encuesta,
        'pregunta' => $pregunta,
        'mostrarError' => false,
    ])->call('seleccionar', $opcion->id);

    expect(
        Respuesta::where('encuesta_id', $encuesta->id)
            ->where('pregunta_id', $pregunta->id)
            ->exists()
    )->toBeTrue();
});

test('seleccionar dos veces la misma pregunta no duplica registros', function () {
    seedEncuesta();

    $encuesta = Encuesta::factory()->asignada()->create();
    $pregunta = Pregunta::first();
    $opciones = OpcionRespuesta::take(2)->get();

    $component = Livewire::test(PreguntaCerrada::class, [
        'encuesta' => $encuesta,
        'pregunta' => $pregunta,
        'mostrarError' => false,
    ]);

    $component->call('seleccionar', $opciones->first()->id);
    $component->call('seleccionar', $opciones->last()->id);

    expect(
        Respuesta::where('encuesta_id', $encuesta->id)
            ->where('pregunta_id', $pregunta->id)
            ->count()
    )->toBe(1);
});

// ---------------------------------------------------------------------------
// Validación de bloque
// ---------------------------------------------------------------------------

test('siguienteBloque agrega error si hay preguntas sin responder', function () {
    seedEncuesta();

    $encuesta = Encuesta::factory()->asignada()->create();

    Livewire::test(EncuestaBloque::class, [
        'token'     => $encuesta->token,
        'dimension' => 1,
    ])->call('siguienteBloque')
      ->assertHasErrors('bloque');
});

test('siguienteBloque redirige si todas las preguntas están respondidas', function () {
    seedEncuesta();

    $encuesta  = Encuesta::factory()->asignada()->create();
    $dimension = Dimension::where('orden', 1)->first();
    $preguntas = Pregunta::whereHas('subdimension', fn ($q) =>
        $q->where('dimension_id', $dimension->id)
    )->get();
    $opcion = OpcionRespuesta::first();

    foreach ($preguntas as $pregunta) {
        Respuesta::create([
            'encuesta_id'         => $encuesta->id,
            'pregunta_id'         => $pregunta->id,
            'opcion_respuesta_id' => $opcion->id,
        ]);
    }

    Livewire::test(EncuestaBloque::class, [
        'token'     => $encuesta->token,
        'dimension' => 1,
    ])->call('siguienteBloque')
      ->assertHasNoErrors()
      ->assertRedirect();
});

// ---------------------------------------------------------------------------
// Progreso por bloque
// ---------------------------------------------------------------------------

test('calcularProgreso retorna 0 cuando no hay respuestas', function () {
    seedEncuesta();

    $encuesta = Encuesta::factory()->asignada()->create();

    Livewire::test(EncuestaBloque::class, [
        'token'     => $encuesta->token,
        'dimension' => 1,
    ])->assertSee('0%');
});

test('calcularProgreso retorna 100 cuando el bloque está completo', function () {
    seedEncuesta();

    $encuesta  = Encuesta::factory()->asignada()->create();
    $dimension = Dimension::where('orden', 1)->first();
    $preguntas = Pregunta::whereHas('subdimension', fn ($q) =>
        $q->where('dimension_id', $dimension->id)
    )->get();
    $opcion = OpcionRespuesta::first();

    foreach ($preguntas as $pregunta) {
        Respuesta::create([
            'encuesta_id'         => $encuesta->id,
            'pregunta_id'         => $pregunta->id,
            'opcion_respuesta_id' => $opcion->id,
        ]);
    }

    Livewire::test(EncuestaBloque::class, [
        'token'     => $encuesta->token,
        'dimension' => 1,
    ])->assertSee('100%');
});

// ---------------------------------------------------------------------------
// Finalización
// ---------------------------------------------------------------------------

test('finalizar marca la encuesta como completada', function () {
    seedEncuesta();

    // El mount() de PreguntasAbiertas requiere que la encuesta esté "en_progreso"
    $encuesta = Encuesta::factory()->create(['estado' => 'en_progreso']);

    Livewire::test(PreguntasAbiertas::class, [
        'token' => $encuesta->token,
    ])->call('finalizar');

    expect($encuesta->fresh()->estado)->toBe('completado');
});