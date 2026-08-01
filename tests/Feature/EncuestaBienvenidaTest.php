<?php

use App\Models\DatoDemografico;
use App\Models\Empresa;
use App\Models\Encuesta;
use App\Models\EncuestaHash;
use App\Models\Lote;
use App\Models\Sucursal;

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
// verificarLlave — #129
// ---------------------------------------------------------------------------

test('verificarLlave devuelve llave_valida con password correcto de empresa', function () {
    $empresa = Empresa::factory()->create(['activa' => true, 'password' => 'secret123']);

    Lote::factory()->create([
        'empresa_id' => $empresa->id,
        'sucursal_id' => null,
        'activo' => true,
        'fecha_inicio' => now()->subDay(),
        'fecha_fin' => now()->addDay(),
    ]);

    $this->postJson(route('encuesta.verificar-llave'), ['password' => 'secret123'])
        ->assertOk()
        ->assertJson(['status' => 'llave_valida'])
        ->assertJsonStructure(['status', 'lote_id', 'nombre_entidad']);
});

test('verificarLlave devuelve llave_valida con password correcto de sucursal', function () {
    $empresa = Empresa::factory()->create(['activa' => true]);
    $sucursal = Sucursal::factory()->create([
        'empresa_id' => $empresa->id,
        'activa' => true,
        'password' => 'suc_secret',
    ]);

    Lote::factory()->create([
        'empresa_id' => $empresa->id,
        'sucursal_id' => $sucursal->id,
        'activo' => true,
        'fecha_inicio' => now()->subDay(),
        'fecha_fin' => now()->addDay(),
    ]);

    $this->postJson(route('encuesta.verificar-llave'), ['password' => 'suc_secret'])
        ->assertOk()
        ->assertJson(['status' => 'llave_valida', 'nombre_entidad' => $sucursal->nombre]);
});

test('verificarLlave devuelve llave_invalida con password incorrecto', function () {
    Empresa::factory()->create(['activa' => true, 'password' => 'correcto123']);

    $this->postJson(route('encuesta.verificar-llave'), ['password' => 'incorrecto'])
        ->assertStatus(422)
        ->assertJson(['error' => 'llave_invalida']);
});

test('verificarLlave devuelve llave_invalida si no hay lote vigente', function () {
    $empresa = Empresa::factory()->create(['activa' => true, 'password' => 'secret123']);

    // Lote fuera de rango: fecha_inicio en el futuro
    Lote::factory()->create([
        'empresa_id' => $empresa->id,
        'sucursal_id' => null,
        'activo' => true,
        'fecha_inicio' => now()->addDay(),
        'fecha_fin' => now()->addDays(10),
    ]);

    $this->postJson(route('encuesta.verificar-llave'), ['password' => 'secret123'])
        ->assertStatus(422)
        ->assertJson(['error' => 'llave_invalida']);
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

test('redirige a dimensiones si ya existen datos demográficos al reingresar', function () {
    $this->seed();
    $encuesta = Encuesta::factory()->asignada()->create();
    DatoDemografico::factory()->for($encuesta)->create();

    $this->get(route('encuesta.demograficos', $encuesta->token))
        ->assertRedirect(route('encuesta.dimensiones', $encuesta->token));
});

test('muestra el formulario demográfico si aún no existen datos demográficos', function () {
    $this->seed();
    $encuesta = Encuesta::factory()->asignada()->create();

    $this->get(route('encuesta.demograficos', $encuesta->token))
        ->assertOk()
        ->assertViewIs('encuesta.demografico');
});
