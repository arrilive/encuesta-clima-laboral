<?php

use App\Services\SmsService;
use Illuminate\Support\Facades\Log;

uses(Tests\TestCase::class);

test('SmsService envia OTP en modo simulado durante pruebas', function () {
    Log::spy();

    $service = new SmsService;
    $result = $service->enviarOtp('+529991234567', '123456');

    expect($result)->toBeTrue();
    Log::shouldHaveReceived('info')->with('[SIMULADO] SMS OTP enviado', [
        'to' => '+529991234567',
        'codigo' => '123456',
    ]);
});

test('SmsService envia enlace de acceso en modo simulado durante pruebas', function () {
    Log::spy();

    $service = new SmsService;
    $result = $service->enviarEnlaceAcceso('+529991234567', 'http://localhost/encuesta/demograficos/TK-1234', 'Empresa Demo');

    expect($result)->toBeTrue();
    Log::shouldHaveReceived('info')->with('[SIMULADO] SMS Enlace Acceso enviado', [
        'to' => '+529991234567',
        'url' => 'http://localhost/encuesta/demograficos/TK-1234',
        'entidad' => 'Empresa Demo',
    ]);
});
