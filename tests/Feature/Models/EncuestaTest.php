<?php

use App\Models\Empresa;
use App\Models\Encuesta;

test('puede crearse con factory y tiene token único', function () {
    // Arrange & Act
    $encuesta1 = Encuesta::factory()->create();
    $encuesta2 = Encuesta::factory()->create();

    // Assert
    expect($encuesta1->token)->not->toBeEmpty();
    expect($encuesta1->token)->not->toBe($encuesta2->token);
});

test('la relación empresa retorna una instancia de Empresa', function () {
    // Arrange
    $encuesta = Encuesta::factory()->create();

    // Act
    $empresa = $encuesta->empresa;

    // Assert
    expect($empresa)->toBeInstanceOf(Empresa::class);
});

test('la relación respuestas retorna una colección vacía por defecto', function () {
    // Arrange
    $encuesta = Encuesta::factory()->create();

    // Act
    $respuestas = $encuesta->respuestas;

    // Assert
    expect($respuestas)->toHaveCount(0);
});

test('la relación datoDemografico retorna null por defecto', function () {
    // Arrange
    $encuesta = Encuesta::factory()->create();

    // Act
    $dato = $encuesta->datoDemografico;

    // Assert
    expect($dato)->toBeNull();
});

test('el scope disponibles filtra correctamente', function () {
    // Arrange
    Encuesta::factory()->create(['estado' => 'disponible']);
    Encuesta::factory()->create(['estado' => 'completado']);
    Encuesta::factory()->create(['estado' => 'asignado']);

    // Act
    $disponibles = Encuesta::disponibles()->get();

    // Assert
    expect($disponibles)->toHaveCount(1);
    expect($disponibles->first()->estado)->toBe('disponible');
});

test('el scope completadas filtra correctamente', function () {
    // Arrange
    Encuesta::factory()->create(['estado' => 'completado']);
    Encuesta::factory()->create(['estado' => 'completado']);
    Encuesta::factory()->create(['estado' => 'disponible']);

    // Act
    $completadas = Encuesta::completadas()->get();

    // Assert
    expect($completadas)->toHaveCount(2);
});

test('el método marcarEnProgreso actualiza el estado en BD', function () {
    // Arrange
    $encuesta = Encuesta::factory()->create(['estado' => 'asignado']);

    // Act
    $encuesta->marcarEnProgreso();

    // Assert
    expect($encuesta->fresh()->estado)->toBe('en_progreso');
});

test('el state completada asigna estado completado y ambas fechas', function () {
    // Arrange & Act
    $encuesta = Encuesta::factory()->completada()->create();

    // Assert
    expect($encuesta->estado)->toBe('completado');
    expect($encuesta->fecha_asignacion)->not->toBeNull();
    expect($encuesta->fecha_completada)->not->toBeNull();
});

test('el método marcarComoCompletada actualiza el estado y fecha en BD', function () {
    // Arrange
    $encuesta = Encuesta::factory()->asignada()->create();

    // Act
    $encuesta->marcarComoCompletada();

    // Assert
    expect($encuesta->fresh()->estado)->toBe('completado');
    expect($encuesta->fresh()->fecha_completada)->not->toBeNull();
});
