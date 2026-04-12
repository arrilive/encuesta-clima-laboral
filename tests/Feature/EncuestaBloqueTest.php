<?php

use App\Livewire\Encuesta\EncuestaBloque;
use App\Livewire\Encuesta\PreguntaCerrada;
use App\Livewire\Encuesta\PreguntasAbiertas;
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
    app()['db']->table('antiguedades')->count() === 0
        && (new \Database\Seeders\AntiguedadesSeeder)->run();
    app()['db']->table('edades')->count() === 0
        && (new \Database\Seeders\EdadesSeeder)->run();
    app()['db']->table('lugares_trabajo')->count() === 0
        && (new \Database\Seeders\LugaresTrabajoSeeder)->run();
    app()['db']->table('sexos')->count() === 0
        && (new \Database\Seeders\SexosSeeder)->run();
    app()['db']->table('grados_academicos')->count() === 0
        && (new \Database\Seeders\GradosAcademicosSeeder)->run();
    app()['db']->table('cargos')->count() === 0
        && (new \Database\Seeders\CargosSeeder)->run();
}

// ---------------------------------------------------------------------------
// Carga de rutas
// ---------------------------------------------------------------------------

test('la ruta encuesta.bloque carga correctamente con token válido', function () {
    seedEncuesta();

    $encuesta = Encuesta::factory()->asignada()->create();
    $dimension = Dimension::where('orden', 1)->first();

    \App\Models\DatoDemografico::factory()->create([
        'encuesta_id' => $encuesta->id,
    ]);

    $this->get(route('encuesta.bloque', [
        'token' => $encuesta->token,
        'dimension' => $dimension->orden,
    ]))->assertOk()
        ->assertSeeLivewire(EncuestaBloque::class);
});

test('la ruta encuesta.bloque retorna 404 con token inválido', function () {
    seedEncuesta();

    $this->get(route('encuesta.bloque', [
        'token' => 'token-falso',
        'dimension' => 1,
    ]))->assertNotFound();
});

test('la ruta encuesta.bloque retorna 404 con encuesta completada', function () {
    seedEncuesta();

    $encuesta = Encuesta::factory()->completada()->create();

    $this->get(route('encuesta.bloque', [
        'token' => $encuesta->token,
        'dimension' => 1,
    ]))->assertNotFound();
});

test('la ruta encuesta.mostrar-acceso redirige a bienvenida sin sesión de empresa', function () {
    $this->get(route('encuesta.mostrar-acceso'))
        ->assertRedirect(route('encuesta.bienvenida'));
});

test('la ruta encuesta.demograficos carga con token válido sin sesión de empresa', function () {
    seedEncuesta();

    $encuesta = Encuesta::factory()->asignada()->create();

    $this->get(route('encuesta.demograficos', $encuesta->token))
        ->assertOk();
});

test('la ruta encuesta.reanudar redirige a dimensiones con token válido', function () {
    seedEncuesta();

    $encuesta = Encuesta::factory()->asignada()->create();

    $this->withSession(['empresa_id' => $encuesta->empresa_id])
        ->post(route('encuesta.reanudar'), ['token' => $encuesta->token])
        ->assertRedirect(route('encuesta.dimensiones', $encuesta->token));
});

test('la ruta encuesta.reanudar rechaza token si pertenece a otra empresa', function () {
    seedEncuesta();

    // El participante entra con la contraseña de la Empresa A
    $empresaSesion = \App\Models\Empresa::factory()->create();

    // Pero intenta usar un token asignado a la Empresa B
    $encuestaOtraEmpresa = Encuesta::factory()->asignada()->create();

    $this->withSession(['empresa_id' => $empresaSesion->id])
        ->post(route('encuesta.reanudar'), ['token' => $encuestaOtraEmpresa->token])
        ->assertSessionHasErrors(['token' => 'Código no encontrado, favor verificar que sea correcto.']);
});

test('la ruta encuesta.dimensiones redirige a demograficos si no hay datos demográficos', function () {
    seedEncuesta();

    $encuesta = Encuesta::factory()->asignada()->create();

    $this->withSession(['empresa_id' => $encuesta->empresa_id])
        ->get(route('encuesta.dimensiones', $encuesta->token))
        ->assertRedirect(route('encuesta.demograficos', $encuesta->token));
});

test('la ruta encuesta.dimensiones muestra botón a abiertas cuando todas las dimensiones están completadas', function () {
    seedEncuesta();

    $encuesta = Encuesta::factory()->create(['estado' => 'en_progreso']);
    $preguntas = Pregunta::all();
    $opcion = OpcionRespuesta::first();

    \App\Models\DatoDemografico::factory()->create([
        'encuesta_id' => $encuesta->id,
    ]);

    foreach ($preguntas as $pregunta) {
        Respuesta::create([
            'encuesta_id' => $encuesta->id,
            'pregunta_id' => $pregunta->id,
            'opcion_respuesta_id' => $opcion->id,
        ]);
    }

    $this->get(route('encuesta.dimensiones', $encuesta->token))
        ->assertOk()
        ->assertSee('Ir a preguntas finales');
});

test('la ruta encuesta.abiertas no permite acceso cuando no están completas todas las dimensiones', function () {
    seedEncuesta();

    $encuesta = Encuesta::factory()->create(['estado' => 'en_progreso']);

    $this->withSession(['empresa_id' => $encuesta->empresa_id])
        ->get(route('encuesta.abiertas', $encuesta->token))
        ->assertRedirect(route('encuesta.dimensiones', $encuesta->token));
});

test('la ruta encuesta.gracias solo permite token completado', function () {
    seedEncuesta();

    $encuesta = Encuesta::factory()->completada()->create();

    $this->withSession(['empresa_id' => $encuesta->empresa_id])
        ->get(route('encuesta.gracias', $encuesta->token))
        ->assertOk();
});

test('la ruta encuesta.gracias no permite token en progreso', function () {
    seedEncuesta();

    $encuesta = Encuesta::factory()->create(['estado' => 'en_progreso']);

    $this->withSession(['empresa_id' => $encuesta->empresa_id])
        ->get(route('encuesta.gracias', $encuesta->token))
        ->assertNotFound();
});

// ---------------------------------------------------------------------------
// Guardado automático
// ---------------------------------------------------------------------------

test('seleccionar una opción guarda la respuesta en BD', function () {
    seedEncuesta();

    $encuesta = Encuesta::factory()->asignada()->create();
    $pregunta = Pregunta::first();
    $opcion = OpcionRespuesta::first();

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
        'token' => $encuesta->token,
        'dimension' => 1,
    ])->call('siguienteBloque')
        ->assertHasErrors('bloque');
});

test('siguienteBloque redirige si todas las preguntas están respondidas', function () {
    seedEncuesta();

    $encuesta = Encuesta::factory()->asignada()->create();
    $dimension = Dimension::where('orden', 1)->first();
    $preguntas = Pregunta::whereHas('subdimension', fn ($q) => $q->where('dimension_id', $dimension->id)
    )->get();
    $opcion = OpcionRespuesta::first();

    foreach ($preguntas as $pregunta) {
        Respuesta::create([
            'encuesta_id' => $encuesta->id,
            'pregunta_id' => $pregunta->id,
            'opcion_respuesta_id' => $opcion->id,
        ]);
    }

    Livewire::test(EncuestaBloque::class, [
        'token' => $encuesta->token,
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
        'token' => $encuesta->token,
        'dimension' => 1,
    ])->assertSee('0%');
});

test('calcularProgreso retorna 100 cuando el bloque está completo', function () {
    seedEncuesta();

    $encuesta = Encuesta::factory()->asignada()->create();
    $dimension = Dimension::where('orden', 1)->first();
    $preguntas = Pregunta::whereHas('subdimension', fn ($q) => $q->where('dimension_id', $dimension->id)
    )->get();
    $opcion = OpcionRespuesta::first();

    foreach ($preguntas as $pregunta) {
        Respuesta::create([
            'encuesta_id' => $encuesta->id,
            'pregunta_id' => $pregunta->id,
            'opcion_respuesta_id' => $opcion->id,
        ]);
    }

    Livewire::test(EncuestaBloque::class, [
        'token' => $encuesta->token,
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

test('updatedRespuestas guarda respuesta abierta en BD', function () {
    seedEncuesta();
    app()['db']->table('preguntas_abiertas')->count() === 0
        && (new \Database\Seeders\PreguntasAbiertasSeeder)->run();
    $encuesta = Encuesta::factory()->create(['estado' => 'en_progreso']);
    \App\Models\DatoDemografico::factory()->create(['encuesta_id' => $encuesta->id]);
    $pregunta = \App\Models\PreguntaAbierta::orderBy('orden')->first();

    Livewire::test(\App\Livewire\Encuesta\PreguntasAbiertas::class, ['token' => $encuesta->token])
        ->set("respuestas.{$pregunta->id}", 'Mi respuesta de prueba');

    expect(\App\Models\RespuestaAbierta::where('encuesta_id', $encuesta->id)
        ->where('pregunta_abierta_id', $pregunta->id)
        ->exists())->toBeTrue();
});

test('updatedRespuestas no guarda si el texto supera 300 caracteres', function () {
    seedEncuesta();
    app()['db']->table('preguntas_abiertas')->count() === 0
        && (new \Database\Seeders\PreguntasAbiertasSeeder)->run();
    $encuesta = Encuesta::factory()->create(['estado' => 'en_progreso']);
    \App\Models\DatoDemografico::factory()->create(['encuesta_id' => $encuesta->id]);
    $pregunta = \App\Models\PreguntaAbierta::orderBy('orden')->first();
    $textoLargo = str_repeat('a', 301);

    Livewire::test(\App\Livewire\Encuesta\PreguntasAbiertas::class, ['token' => $encuesta->token])
        ->set("respuestas.{$pregunta->id}", $textoLargo);

    expect(\App\Models\RespuestaAbierta::where('encuesta_id', $encuesta->id)->exists())->toBeFalse();
});

test('finalizar marca encuesta como completada y redirige a gracias', function () {
    seedEncuesta();
    $encuesta = Encuesta::factory()->create(['estado' => 'en_progreso']);
    \App\Models\DatoDemografico::factory()->create(['encuesta_id' => $encuesta->id]);

    Livewire::test(\App\Livewire\Encuesta\PreguntasAbiertas::class, ['token' => $encuesta->token])
        ->call('finalizar')
        ->assertRedirect(route('encuesta.gracias', $encuesta->token));

    expect($encuesta->fresh()->estado)->toBe('completado');
});

test('bloque rechaza dimension fuera de rango', function () {
    seedEncuesta();
    $encuesta = Encuesta::factory()->asignada()->create();
    \App\Models\DatoDemografico::factory()->create(['encuesta_id' => $encuesta->id]);

    $this->get(route('encuesta.bloque', ['token' => $encuesta->token, 'dimension' => 7]))
        ->assertStatus(404);
});

test('bloque redirige a dimensiones si el usuario se adelanta', function () {
    seedEncuesta();
    $encuesta = Encuesta::factory()->asignada()->create();
    \App\Models\DatoDemografico::factory()->create(['encuesta_id' => $encuesta->id]);

    $this->get(route('encuesta.bloque', ['token' => $encuesta->token, 'dimension' => 3]))
        ->assertRedirect(route('encuesta.dimensiones', $encuesta->token));
});
