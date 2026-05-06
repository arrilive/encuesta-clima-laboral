<?php

use App\Livewire\Admin\RespuestasAbiertas;
use App\Models\Encuesta;
use App\Models\PreguntaAbierta;
use App\Models\RespuestaAbierta;
use App\Models\User;
use Livewire\Livewire;

function seedPreguntasAbiertas(): void
{
    app()['db']->table('preguntas_abiertas')->count() === 0
        && (new \Database\Seeders\PreguntasAbiertasSeeder)->run();
}

test('el componente carga correctamente para admin_empresa', function () {
    seedPreguntasAbiertas();
    $empresa = \App\Models\Empresa::factory()->create();
    $user = User::factory()->adminEmpresa($empresa->id)->create();

    Livewire::actingAs($user)
        ->test(RespuestasAbiertas::class)
        ->assertOk();
});

test('mount asigna la primera pregunta abierta como activa', function () {
    seedPreguntasAbiertas();
    $empresa = \App\Models\Empresa::factory()->create();
    $user = User::factory()->adminEmpresa($empresa->id)->create();
    $primera = PreguntaAbierta::orderBy('orden')->first();

    Livewire::actingAs($user)
        ->test(RespuestasAbiertas::class)
        ->assertSet('preguntaAbiertaActiva', $primera->id);
});

test('toggleRespuestasAbiertas cambia el estado de mostrarRespuestasAbiertas', function () {
    seedPreguntasAbiertas();
    $empresa = \App\Models\Empresa::factory()->create();
    $user = User::factory()->adminEmpresa($empresa->id)->create();

    Livewire::actingAs($user)
        ->test(RespuestasAbiertas::class)
        ->assertSet('mostrarRespuestasAbiertas', false)
        ->call('toggleRespuestasAbiertas')
        ->assertSet('mostrarRespuestasAbiertas', true)
        ->call('toggleRespuestasAbiertas')
        ->assertSet('mostrarRespuestasAbiertas', false);
});

test('seleccionarPreguntaAbierta actualiza la pregunta activa', function () {
    seedPreguntasAbiertas();
    $empresa = \App\Models\Empresa::factory()->create();
    $user = User::factory()->adminEmpresa($empresa->id)->create();
    $segunda = PreguntaAbierta::orderBy('orden')->skip(1)->first();

    Livewire::actingAs($user)
        ->test(RespuestasAbiertas::class)
        ->call('seleccionarPreguntaAbierta', $segunda->id)
        ->assertSet('preguntaAbiertaActiva', $segunda->id);
});

test('respuestas abiertas se muestran cuando mostrarRespuestasAbiertas es true', function () {
    seedPreguntasAbiertas();
    $empresa = \App\Models\Empresa::factory()->create();
    $user = User::factory()->adminEmpresa($empresa->id)->create();
    $encuesta = Encuesta::factory()->completada()->create(['lote_id' => \App\Models\Lote::factory()->for($empresa)->create()->id]);
    $pregunta = PreguntaAbierta::orderBy('orden')->first();

    RespuestaAbierta::create([
        'encuesta_id' => $encuesta->id,
        'pregunta_abierta_id' => $pregunta->id,
        'texto' => 'Respuesta de prueba',
    ]);

    Livewire::actingAs($user)
        ->test(RespuestasAbiertas::class)
        ->call('toggleRespuestasAbiertas')
        ->assertViewHas('respuestasAbiertas');
});

test('admin_empresa solo ve respuestas de su empresa', function () {
    seedPreguntasAbiertas();
    $empresa = \App\Models\Empresa::factory()->create();
    $user = User::factory()->adminEmpresa($empresa->id)->create();
    $otraEmpresa = \App\Models\Empresa::factory()->create();
    $encuestaOtra = Encuesta::factory()->completada()->create(['lote_id' => \App\Models\Lote::factory()->for($otraEmpresa)->create()->id]);
    $pregunta = PreguntaAbierta::orderBy('orden')->first();

    RespuestaAbierta::create([
        'encuesta_id' => $encuestaOtra->id,
        'pregunta_abierta_id' => $pregunta->id,
        'texto' => 'Respuesta secreta de otra empresa',
    ]);

    Livewire::actingAs($user)
        ->test(RespuestasAbiertas::class)
        ->call('toggleRespuestasAbiertas')
        ->assertDontSee('Respuesta secreta de otra empresa');
});
