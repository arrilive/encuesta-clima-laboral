<?php

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

it('crea 290 encuestas completadas y 17 en riesgo', function () {
    $this->seed(\Database\Seeders\DemoSeeder::class);

    expect(\App\Models\Encuesta::where('estado', 'completado')->count())->toBe(290);
    expect(\App\Models\Encuesta::where('estado', 'asignado')->count())->toBe(17);
});

it('cada encuesta completada tiene dato demografico y respuestas completas', function () {
    $this->seed(\Database\Seeders\DemoSeeder::class);

    $completadas = \App\Models\Encuesta::where('estado', 'completado')->get();

    expect($completadas)->toHaveCount(290);

    foreach ($completadas as $encuesta) {
        expect($encuesta->datoDemografico)->not->toBeNull();
        expect($encuesta->respuestas()->count())->toBeGreaterThan(0);
    }
});

it('los 17 tokens en riesgo activan scope en riesgo', function () {
    $this->seed(\Database\Seeders\DemoSeeder::class);

    expect(\App\Models\Encuesta::enRiesgo()->count())->toBe(17);
});

it('el demo seeder no se llama desde database seeder', function () {
    $this->assertDatabaseCount('encuestas', 0);
});
