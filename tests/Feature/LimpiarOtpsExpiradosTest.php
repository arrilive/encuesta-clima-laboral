<?php

use App\Models\OtpVerificacion;
use Illuminate\Console\Command;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('elimina registros cuyo expira_en es anterior a now()', function () {
    // Crear un registro expirado
    OtpVerificacion::factory()->expirada()->create();

    // Confirmar que existe en BD antes de la limpieza
    expect(OtpVerificacion::count())->toBe(1);

    // Ejecutar el comando
    $this->artisan('otp:limpiar-expirados')
        ->assertExitCode(Command::SUCCESS);

    // Verificar que fue eliminado
    expect(OtpVerificacion::count())->toBe(0);
});

test('NO elimina registros cuyo expira_en es posterior a now()', function () {
    // Crear un registro vigente (expira en 10 minutos por defecto en el factory)
    OtpVerificacion::factory()->create();

    // Confirmar que existe en BD antes de la limpieza
    expect(OtpVerificacion::count())->toBe(1);

    // Ejecutar el comando
    $this->artisan('otp:limpiar-expirados')
        ->assertExitCode(Command::SUCCESS);

    // Verificar que NO fue eliminado
    expect(OtpVerificacion::count())->toBe(1);
});

test('retorna exit code 0 (Command::SUCCESS)', function () {
    $this->artisan('otp:limpiar-expirados')
        ->assertExitCode(Command::SUCCESS);
});

test('output contiene el número de registros eliminados', function () {
    // Crear 2 registros expirados y 1 vigente
    OtpVerificacion::factory()->count(2)->expirada()->create();
    OtpVerificacion::factory()->create();

    // Ejecutar el comando y verificar el output parcial
    $this->artisan('otp:limpiar-expirados')
        ->expectsOutputToContain('Limpieza completada: 2 registro(s) eliminado(s)')
        ->assertExitCode(Command::SUCCESS);
});
