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
