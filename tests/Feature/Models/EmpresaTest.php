<?php

use App\Models\Empresa;

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

test('la relación encuestas retorna las encuestas asociadas a través de sus lotes', function () {
    // Arrange
    $empresa = Empresa::factory()->create();
    $lote = \App\Models\Lote::factory()->create(['empresa_id' => $empresa->id]);
    $encuesta = \App\Models\Encuesta::factory()->create(['lote_id' => $lote->id]);

    // Act & Assert
    expect($empresa->encuestas)->toHaveCount(1);
    expect($empresa->encuestas->first()->id)->toBe($encuesta->id);
});
