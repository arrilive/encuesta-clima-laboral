<?php

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

todo('crea 93 encuestas completadas y 7 en riesgo — DemoSeeder usa empresa_id directo, se actualiza cuando se refactorice el seeder');

todo('cada encuesta completada tiene dato demografico y respuestas completas — DemoSeeder usa empresa_id directo, se actualiza cuando se refactorice el seeder');

todo('los 7 tokens en riesgo activan scope en riesgo — DemoSeeder usa empresa_id directo, se actualiza cuando se refactorice el seeder');

it('el demo seeder no se llama desde database seeder', function () {
    $this->assertDatabaseCount('encuestas', 0);
});
