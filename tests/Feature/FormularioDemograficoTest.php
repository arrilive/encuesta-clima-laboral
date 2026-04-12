<?php

use App\Livewire\Encuesta\FormularioDemografico;
use App\Models\Encuesta;
use App\Models\DatoDemografico;
use Database\Seeders\DemograficosSeeder;
use Livewire\Livewire;

function seedDemograficos(): void
{
    app()['db']->table('edades')->count() === 0
        && (new \Database\Seeders\EdadesSeeder)->run();
    app()['db']->table('sexos')->count() === 0
        && (new \Database\Seeders\SexosSeeder)->run();
    app()['db']->table('antiguedades')->count() === 0
        && (new \Database\Seeders\AntiguedadesSeeder)->run();
    app()['db']->table('lugares_trabajo')->count() === 0
        && (new \Database\Seeders\LugaresTrabajoSeeder)->run();
    app()['db']->table('grados_academicos')->count() === 0
        && (new \Database\Seeders\GradosAcademicosSeeder)->run();
    app()['db']->table('cargos')->count() === 0
        && (new \Database\Seeders\CargosSeeder)->run();
}

test('el formulario carga con token en progreso', function () {
    seedDemograficos();
    $encuesta = Encuesta::factory()->asignada()->create();

    Livewire::test(FormularioDemografico::class, ['token' => $encuesta->token])
        ->assertOk();
});

test('mount marca la encuesta en progreso si estaba asignada', function () {
    seedDemograficos();
    $encuesta = Encuesta::factory()->asignada()->create();

    Livewire::test(FormularioDemografico::class, ['token' => $encuesta->token]);

    expect($encuesta->fresh()->estado)->toBe('en_progreso');
});

test('mount carga datos demograficos previos si existen', function () {
    seedDemograficos();
    $encuesta = Encuesta::factory()->asignada()->create();
    $edad = \App\Models\Edad::first();
    $sexo = \App\Models\Sexo::first();
    DatoDemografico::factory()->create([
        'encuesta_id' => $encuesta->id,
        'edad_id'     => $edad->id,
        'sexo_id'     => $sexo->id,
    ]);

    Livewire::test(FormularioDemografico::class, ['token' => $encuesta->token])
        ->assertSet('edad_id', $edad->id)
        ->assertSet('sexo_id', $sexo->id);
});

test('guardarProgreso crea dato demografico en BD', function () {
    seedDemograficos();
    $encuesta = Encuesta::factory()->asignada()->create();
    $edad = \App\Models\Edad::first();
    $sexo = \App\Models\Sexo::first();

    Livewire::test(FormularioDemografico::class, ['token' => $encuesta->token])
        ->set('edad_id', $edad->id)
        ->set('sexo_id', $sexo->id);

    expect(DatoDemografico::where('encuesta_id', $encuesta->id)->exists())->toBeTrue();
});

test('continuar falla si faltan campos requeridos', function () {
    seedDemograficos();
    $encuesta = Encuesta::factory()->asignada()->create();

    Livewire::test(FormularioDemografico::class, ['token' => $encuesta->token])
        ->call('continuar')
        ->assertHasErrors(['edad_id', 'sexo_id', 'antiguedad_id', 'lugar_trabajo_id', 'grado_academico_id', 'cargo_id']);
});

test('continuar redirige a dimensiones cuando todos los campos son validos', function () {
    seedDemograficos();
    $encuesta = Encuesta::factory()->asignada()->create();

    $edad       = \App\Models\Edad::first();
    $sexo       = \App\Models\Sexo::first();
    $antiguedad = \App\Models\Antiguedad::first();
    $lugar      = \App\Models\LugarTrabajo::first();
    $grado      = \App\Models\GradoAcademico::first();
    $cargo      = \App\Models\Cargo::first();

    Livewire::test(FormularioDemografico::class, ['token' => $encuesta->token])
        ->set('edad_id', $edad->id)
        ->set('sexo_id', $sexo->id)
        ->set('antiguedad_id', $antiguedad->id)
        ->set('lugar_trabajo_id', $lugar->id)
        ->set('grado_academico_id', $grado->id)
        ->set('cargo_id', $cargo->id)
        ->call('continuar')
        ->assertRedirect(route('encuesta.dimensiones', $encuesta->token));
});
