<?php

use App\Models\Empresa;
use App\Models\Encuesta;

test('puede crearse con factory', function () {
    // Arrange & Act
    $empresa = Empresa::factory()->create();

    // Assert
    expect($empresa)->toBeInstanceOf(Empresa::class);
    expect($empresa->id)->not->toBeNull();
});

test('el password se hashea automáticamente y no se guarda en texto plano', function () {
    // Arrange & Act
    $empresa = Empresa::factory()->create(['password' => 'secreto']);

    // Assert
    expect($empresa->password)->not->toBe('secreto');
    expect(password_verify('secreto', $empresa->password))->toBeTrue();
});

test('la relación encuestas retorna colección vacía por defecto', function () {
    // Arrange
    $empresa = Empresa::factory()->create();

    // Act
    $encuestas = $empresa->encuestas;

    // Assert
    expect($encuestas)->toHaveCount(0);
});

test('la relación encuestas retorna las encuestas asociadas', function () {
    // Arrange
    $empresa = Empresa::factory()->create();

    // Act
    Encuesta::factory()->count(3)->create(['empresa_id' => $empresa->id]);

    // Assert
    expect($empresa->fresh()->encuestas)->toHaveCount(3);
});
