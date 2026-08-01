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

test('cuando hay menos de 10 respuestas completadas bajoUmbral es true en la vista de respuestas abiertas', function () {
    seedPreguntasAbiertas();
    $empresa = \App\Models\Empresa::factory()->create();
    $user = User::factory()->adminEmpresa($empresa->id)->create();
    $lote = \App\Models\Lote::factory()->for($empresa)->create();

    for ($i = 0; $i < 3; $i++) {
        $encuesta = Encuesta::factory()->completada()->create(['lote_id' => $lote->id]);
        RespuestaAbierta::create([
            'encuesta_id' => $encuesta->id,
            'pregunta_abierta_id' => PreguntaAbierta::first()->id,
            'texto' => 'Comentario '.$i,
        ]);
    }

    Livewire::actingAs($user)
        ->test(RespuestasAbiertas::class)
        ->assertViewHas('bajoUmbral', true)
        ->call('toggleRespuestasAbiertas')
        ->assertSee('Comentarios protegidos')
        ->assertSee('Se necesitan al menos 10 respuestas');
});

test('sin filtroLoteId explicito el resultado se limita al lote de estado actual', function () {
    seedPreguntasAbiertas();
    $empresa = \App\Models\Empresa::factory()->create();
    $user = User::factory()->adminEmpresa($empresa->id)->create();

    $loteAntiguo = \App\Models\Lote::factory()->create([
        'empresa_id' => $empresa->id,
        'fecha_inicio' => now()->subDays(60),
        'fecha_fin' => now()->subDays(30),
        'activo' => false,
    ]);

    $loteReciente = \App\Models\Lote::factory()->create([
        'empresa_id' => $empresa->id,
        'fecha_inicio' => now()->subDays(20),
        'fecha_fin' => now()->subDays(5),
        'activo' => false,
    ]);

    $enc1 = Encuesta::factory()->completada()->create(['lote_id' => $loteAntiguo->id]);
    $enc2 = Encuesta::factory()->completada()->create(['lote_id' => $loteReciente->id]);

    $comp = Livewire::actingAs($user)->test(RespuestasAbiertas::class);

    $reflection = new \ReflectionMethod(RespuestasAbiertas::class, 'getEncuestasBaseQuery');
    $reflection->setAccessible(true);
    $query = $reflection->invoke($comp->instance());

    expect($query->count())->toBe(1);
    expect($query->first()->id)->toBe($enc2->id);
});

test('con filtroSucursalId seleccionado el resultado se limita a esa sucursal', function () {
    seedPreguntasAbiertas();
    $empresa = \App\Models\Empresa::factory()->create();
    $sucursal1 = \App\Models\Sucursal::factory()->create(['empresa_id' => $empresa->id]);
    $sucursal2 = \App\Models\Sucursal::factory()->create(['empresa_id' => $empresa->id]);

    $user = User::factory()->adminEmpresa($empresa->id)->create();

    $lote1 = \App\Models\Lote::factory()->create(['empresa_id' => $empresa->id, 'sucursal_id' => $sucursal1->id]);
    $lote2 = \App\Models\Lote::factory()->create(['empresa_id' => $empresa->id, 'sucursal_id' => $sucursal2->id]);

    $enc1 = Encuesta::factory()->completada()->create(['lote_id' => $lote1->id]);
    $enc2 = Encuesta::factory()->completada()->create(['lote_id' => $lote2->id]);

    $comp = Livewire::actingAs($user)
        ->test(RespuestasAbiertas::class, [
            'filtroSucursalId' => (string) $sucursal1->id,
        ]);

    $reflection = new \ReflectionMethod(RespuestasAbiertas::class, 'getEncuestasBaseQuery');
    $reflection->setAccessible(true);
    $query = $reflection->invoke($comp->instance());

    expect($query->count())->toBe(1);
    expect($query->first()->id)->toBe($enc1->id);
});
