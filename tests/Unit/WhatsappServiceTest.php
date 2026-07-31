<?php

use App\Services\WhatsappService;
use Illuminate\Support\Facades\Log;

uses(Tests\TestCase::class);

test('WhatsappService envia OTP en modo simulado durante pruebas', function () {
    Log::spy();

    $service = new WhatsappService;
    $result = $service->enviarOtp('+5219991234567', '123456');

    expect($result)->toBeTrue();
    Log::shouldHaveReceived('info')->with('[SIMULADO] WhatsApp OTP enviado', [
        'to' => 'whatsapp:+5219991234567',
        'codigo' => '123456',
    ]);
});

test('WhatsappService envia enlace de acceso en modo simulado durante pruebas', function () {
    Log::spy();

    $service = new WhatsappService;
    $result = $service->enviarEnlaceAcceso('+5219991234567', 'http://localhost/encuesta/demograficos/TK-1234', 'Empresa Demo');

    expect($result)->toBeTrue();
    Log::shouldHaveReceived('info')->with('[SIMULADO] WhatsApp Enlace Acceso enviado', [
        'to' => 'whatsapp:+5219991234567',
        'url' => 'http://localhost/encuesta/demograficos/TK-1234',
        'entidad' => 'Empresa Demo',
    ]);
});

test('WhatsappService formatea correctamente numeros sin prefijo whatsapp:', function () {
    Log::spy();

    $service = new WhatsappService;
    $result = $service->enviarOtp('5219991234567', '654321');

    expect($result)->toBeTrue();
    Log::shouldHaveReceived('info')->with('[SIMULADO] WhatsApp OTP enviado', [
        'to' => 'whatsapp:5219991234567',
        'codigo' => '654321',
    ]);
});
