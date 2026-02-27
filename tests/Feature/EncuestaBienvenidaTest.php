<?php

use App\Models\Empresa;
use App\Models\Encuesta;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Bienvenida
// ---------------------------------------------------------------------------

test('muestra la página de bienvenida', function () {
    /** @var \Illuminate\Testing\TestResponse $response */
    $this->get(route('encuesta.bienvenida'))
        ->assertOk()
        ->assertSee('Encuesta de Clima Laboral');
});

// ---------------------------------------------------------------------------
// Acceso — validación de contraseña
// ---------------------------------------------------------------------------

test('rechaza contraseña incorrecta', function () {
    Empresa::factory()->create(); // empresa activa con contraseña 'test1234'

    $this->post(route('encuesta.acceso'), ['password' => 'wrongpassword'])
        ->assertSessionHasErrors('password');
});

test('rechaza empresa inactiva', function () {
    Empresa::factory()->create(['activa' => false]);

    $this->post(route('encuesta.acceso'), ['password' => 'test1234'])
        ->assertSessionHasErrors('password');
});

test('muestra error si no hay tokens disponibles', function () {
    // Empresa activa pero sin encuestas con estado 'disponible'
    $empresa = Empresa::factory()->create();
    Encuesta::factory()->for($empresa)->asignada()->create();

    $this->post(route('encuesta.acceso'), ['password' => 'test1234'])
        ->assertSessionHasErrors('password');
});

// ---------------------------------------------------------------------------
// Acceso — caso exitoso
// ---------------------------------------------------------------------------

test('asigna token disponible con contraseña correcta', function () {
    $empresa  = Empresa::factory()->create();
    $encuesta = Encuesta::factory()->for($empresa)->create(); // estado 'disponible'

    $this->post(route('encuesta.acceso'), ['password' => 'test1234'])
        ->assertOk()
        ->assertSee($encuesta->fresh()->token);

    expect($encuesta->fresh()->estado)->toBe('asignado');
});

// ---------------------------------------------------------------------------
// Formulario demográfico
// ---------------------------------------------------------------------------

test('muestra el formulario demográfico con token válido', function () {
    $encuesta = Encuesta::factory()->asignada()->create();

    $this->get(route('encuesta.demograficos', $encuesta->token))
        ->assertOk()
        ->assertSee('Datos generales');
});

test('retorna 404 con token inválido', function () {
    $this->get(route('encuesta.demograficos', 'token-que-no-existe'))
        ->assertNotFound();
});

test('retorna 404 con token completado', function () {
    $encuesta = Encuesta::factory()->completada()->create();

    $this->get(route('encuesta.demograficos', $encuesta->token))
        ->assertNotFound();
});
