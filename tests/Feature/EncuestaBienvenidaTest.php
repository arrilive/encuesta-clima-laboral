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

test('redirige a pantalla de acceso cuando contraseña es correcta aunque no haya tokens', function () {
    $empresa = Empresa::factory()->create();
    Encuesta::factory()->for($empresa)->asignada()->create(); // sin tokens disponibles

    $this->post(route('encuesta.acceso'), ['password' => 'test1234'])
        ->assertRedirect(route('encuesta.mostrar-acceso'));
});

// ---------------------------------------------------------------------------
// Acceso — caso exitoso
// ---------------------------------------------------------------------------

test('acceso con contraseña correcta redirige a pantalla de elección', function () {
    $empresa  = Empresa::factory()->create();
    Encuesta::factory()->for($empresa)->create(); // disponible

    $response = $this->post(route('encuesta.acceso'), ['password' => 'test1234']);

    $response->assertRedirect(route('encuesta.mostrar-acceso'));

    $response->assertSessionHas('empresa_id', $empresa->id);
});

// ---------------------------------------------------------------------------
// Formulario demográfico
// ---------------------------------------------------------------------------

test('muestra el formulario demográfico con token válido', function () {
    $encuesta = Encuesta::factory()->asignada()->create();

    $this->get(route('encuesta.demograficos', $encuesta->token))
        ->assertOk()
        ->assertSee('Cuéntanos sobre ti');
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
