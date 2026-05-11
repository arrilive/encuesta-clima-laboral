<?php

use App\Models\Empresa;
use App\Models\Encuesta;
use App\Models\EncuestaHash;
use App\Models\Lote;

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
// solicitarOtp — #128
// ---------------------------------------------------------------------------

test('solicitarOtp devuelve otp_enviado con numero y lote validos', function () {
    $empresa = Empresa::factory()->create(['activa' => true]);

    $lote = Lote::factory()->create([
        'empresa_id' => $empresa->id,
        'activo' => true,
        'fecha_inicio' => now()->subDay(),
        'fecha_fin' => now()->addDay(),
    ]);

    $this->postJson(route('encuesta.solicitar-otp'), [
        'numero_e164' => '+5219991234567',
        'lote_id' => $lote->id,
    ])->assertOk()
        ->assertJson(['status' => 'otp_enviado']);
});

test('solicitarOtp devuelve ya_participaste si el hash del numero ya existe en el lote', function () {
    $empresa = Empresa::factory()->create(['activa' => true]);

    $lote = Lote::factory()->create([
        'empresa_id' => $empresa->id,
        'activo' => true,
        'fecha_inicio' => now()->subDay(),
        'fecha_fin' => now()->addDay(),
    ]);

    $numero = '+5219991234567';
    $hashPhone = hash('sha256', $numero.$lote->id.config('app.phone_hash_salt'));

    EncuestaHash::create(['phone_hash' => $hashPhone, 'lote_id' => $lote->id]);

    $this->postJson(route('encuesta.solicitar-otp'), [
        'numero_e164' => $numero,
        'lote_id' => $lote->id,
    ])->assertStatus(422)
        ->assertJson(['error' => 'ya_participaste']);
});

test('solicitarOtp devuelve acceso_invalido si el lote no esta vigente', function () {
    $empresa = Empresa::factory()->create(['activa' => true]);

    $lote = Lote::factory()->create([
        'empresa_id' => $empresa->id,
        'activo' => true,
        'fecha_inicio' => now()->addDay(),
        'fecha_fin' => now()->addDays(10),
    ]);

    $this->postJson(route('encuesta.solicitar-otp'), [
        'numero_e164' => '+5219991234567',
        'lote_id' => $lote->id,
    ])->assertStatus(422)
        ->assertJson(['error' => 'acceso_invalido']);
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
