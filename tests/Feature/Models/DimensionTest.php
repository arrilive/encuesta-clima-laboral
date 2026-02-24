<?php

use App\Models\Dimension;
use App\Models\Pregunta;
use App\Models\Subdimension;
use Database\Seeders\DimensionesSeeder;
use Database\Seeders\PreguntasSeeder;
use Database\Seeders\SubdimensionesSeeder;

test('hay 6 dimensiones en BD después de seedear', function () {
    /** @var \Tests\TestCase $this */
    // Arrange
    $this->seed([
        DimensionesSeeder::class,
    ]);

    // Act
    $total = Dimension::count();

    // Assert
    expect($total)->toBe(6);
});

test('la relación subdimensiones retorna las subdimensiones de la dimensión', function () {
    /** @var \Tests\TestCase $this */
    // Arrange
    $this->seed([
        DimensionesSeeder::class,
        SubdimensionesSeeder::class,
    ]);
    $dimension = Dimension::first();

    // Act
    $subdimensiones = $dimension->subdimensiones;

    // Assert
    expect($subdimensiones)->not->toHaveCount(0);
    expect($subdimensiones->first())->toBeInstanceOf(Subdimension::class);
});

test('la relación preguntas via hasManyThrough retorna preguntas de todas sus subdimensiones', function () {
    /** @var \Tests\TestCase $this */
    // Arrange
    $this->seed([
        DimensionesSeeder::class,
        SubdimensionesSeeder::class,
        PreguntasSeeder::class,
    ]);
    $dimension = Dimension::first();

    // Act
    $preguntas = $dimension->preguntas;

    // Assert
    expect($preguntas)->not->toHaveCount(0);
    expect($preguntas->first())->toBeInstanceOf(Pregunta::class);

    // Verificar que solo retorna preguntas de sus propias subdimensiones
    $subdimensionIds = $dimension->subdimensiones->pluck('id');
    $preguntasAjenas = $preguntas->filter(
        fn (Pregunta $p) => ! $subdimensionIds->contains($p->subdimension_id)
    );
    expect($preguntasAjenas)->toHaveCount(0);
});
