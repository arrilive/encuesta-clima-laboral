<?php

use App\Models\Encuesta;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

it('crea 93 encuestas completadas y 7 en riesgo', function () {
    $this->seed(DemoSeeder::class);

    $this->assertDatabaseCount('encuestas', 100);
    expect(Encuesta::where('estado', 'completado')->count())->toBe(93);
    expect(Encuesta::where('estado', 'asignado')->count())->toBe(7);
});

it('cada encuesta completada tiene dato demografico y respuestas completas', function () {
    $this->seed(DemoSeeder::class);

    $encuesta = Encuesta::where('estado', 'completado')->inRandomOrder()->first();

    expect($encuesta->datoDemografico)->not->toBeNull();
    expect($encuesta->respuestas()->count())->toBe(64);
    expect($encuesta->respuestasAbiertas()->count())->toBe(3);
});

it('los 7 tokens en riesgo activan scope en riesgo', function () {
    $this->seed(DemoSeeder::class);

    expect(Encuesta::enRiesgo()->count())->toBe(7);
});

it('el demo seeder no se llama desde database seeder', function () {
    $this->assertDatabaseCount('encuestas', 0);
});
