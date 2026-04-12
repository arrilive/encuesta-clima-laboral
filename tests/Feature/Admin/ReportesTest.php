<?php

use App\Livewire\Admin\Reportes;
use App\Models\Dimension;
use App\Models\Empresa;
use App\Models\Encuesta;
use App\Models\OpcionRespuesta;
use App\Models\Pregunta;
use App\Models\Respuesta;
use App\Models\User;
use Database\Seeders\DimensionesSeeder;
use Database\Seeders\OpcionesRespuestaSeeder;
use Database\Seeders\PreguntasSeeder;
use Database\Seeders\SubdimensionesSeeder;
use Livewire\Livewire;

it('el componente carga en nivel 1 para admin_empresa', function () {
    $this->seed([DimensionesSeeder::class, SubdimensionesSeeder::class, PreguntasSeeder::class, OpcionesRespuestaSeeder::class]);

    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();

    Livewire::actingAs($admin)
        ->test(Reportes::class)
        ->assertSet('nivel', 1);
});

it('el puntaje de dimensión es 3.0 cuando todas las respuestas son Verdadero', function () {
    $this->seed([DimensionesSeeder::class, SubdimensionesSeeder::class, PreguntasSeeder::class, OpcionesRespuestaSeeder::class]);

    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();
    $encuesta = Encuesta::factory()->completada()->create(['empresa_id' => $empresa->id]);

    $dimension = Dimension::first();
    $opcion = OpcionRespuesta::where('valor_numerico', 3)->first();
    $preguntas = Pregunta::whereHas('subdimension', fn ($q) => $q->where('dimension_id', $dimension->id))->get();

    foreach ($preguntas as $pregunta) {
        Respuesta::create([
            'encuesta_id' => $encuesta->id,
            'pregunta_id' => $pregunta->id,
            'opcion_respuesta_id' => $opcion->id,
        ]);
    }

    $component = Livewire::actingAs($admin)->test(Reportes::class);
    $datosNivel1 = $component->instance()->getDatosNivel1();
    $puntaje = collect($datosNivel1)->firstWhere('id', $dimension->id)['puntaje'];

    expect($puntaje)->toBe(100.0);
});

it('prefiero no responder (valor_numerico = 0) se excluye del cálculo', function () {
    $this->seed([DimensionesSeeder::class, SubdimensionesSeeder::class, PreguntasSeeder::class, OpcionesRespuestaSeeder::class]);

    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();
    $encuesta = Encuesta::factory()->completada()->create(['empresa_id' => $empresa->id]);

    $dimension = Dimension::first();
    $opcion = OpcionRespuesta::where('valor_numerico', 0)->first();
    $preguntas = Pregunta::whereHas('subdimension', fn ($q) => $q->where('dimension_id', $dimension->id))->get();

    foreach ($preguntas as $pregunta) {
        Respuesta::create([
            'encuesta_id' => $encuesta->id,
            'pregunta_id' => $pregunta->id,
            'opcion_respuesta_id' => $opcion->id,
        ]);
    }

    $component = Livewire::actingAs($admin)->test(Reportes::class);
    $datosNivel1 = $component->instance()->getDatosNivel1();
    $puntaje = collect($datosNivel1)->firstWhere('id', $dimension->id)['puntaje'];

    expect($puntaje)->toBeNull();
});

it('admin_empresa solo ve datos de su empresa', function () {
    $this->seed([DimensionesSeeder::class, SubdimensionesSeeder::class, PreguntasSeeder::class, OpcionesRespuestaSeeder::class]);

    $empresa1 = Empresa::factory()->create();
    $empresa2 = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa1->id)->create();
    $encuesta = Encuesta::factory()->completada()->create(['empresa_id' => $empresa2->id]);

    $dimension = Dimension::first();
    $opcion = OpcionRespuesta::where('valor_numerico', 3)->first();
    $preguntas = Pregunta::whereHas('subdimension', fn ($q) => $q->where('dimension_id', $dimension->id))->get();

    foreach ($preguntas as $pregunta) {
        Respuesta::create([
            'encuesta_id' => $encuesta->id,
            'pregunta_id' => $pregunta->id,
            'opcion_respuesta_id' => $opcion->id,
        ]);
    }

    $component = Livewire::actingAs($admin)->test(Reportes::class);
    $datosNivel1 = $component->instance()->getDatosNivel1();
    $puntaje = collect($datosNivel1)->firstWhere('id', $dimension->id)['puntaje'];

    expect($puntaje)->toBeNull();
});

it('super_admin sin filtro ve datos de todas las empresas', function () {
    $this->seed([DimensionesSeeder::class, SubdimensionesSeeder::class, PreguntasSeeder::class, OpcionesRespuestaSeeder::class]);

    $empresa1 = Empresa::factory()->create();
    $empresa2 = Empresa::factory()->create();
    $superAdmin = User::factory()->superAdmin()->create();

    $encuesta1 = Encuesta::factory()->completada()->create(['empresa_id' => $empresa1->id]);
    $encuesta2 = Encuesta::factory()->completada()->create(['empresa_id' => $empresa2->id]);

    $dimension = Dimension::first();
    $opcion = OpcionRespuesta::where('valor_numerico', 3)->first();
    $preguntas = Pregunta::whereHas('subdimension', fn ($q) => $q->where('dimension_id', $dimension->id))->get();

    foreach ([$encuesta1, $encuesta2] as $encuesta) {
        foreach ($preguntas as $pregunta) {
            Respuesta::create([
                'encuesta_id' => $encuesta->id,
                'pregunta_id' => $pregunta->id,
                'opcion_respuesta_id' => $opcion->id,
            ]);
        }
    }

    $component = Livewire::actingAs($superAdmin)->test(Reportes::class);
    $datosNivel1 = $component->instance()->getDatosNivel1();
    $puntaje = collect($datosNivel1)->firstWhere('id', $dimension->id)['puntaje'];

    expect($puntaje)->toBeGreaterThan(0);
});

it('irNivel2 cambia el nivel a 2 y asigna dimensionActivaId', function () {
    $this->seed([DimensionesSeeder::class, SubdimensionesSeeder::class, PreguntasSeeder::class, OpcionesRespuestaSeeder::class]);

    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();
    $dimension = Dimension::first();

    Livewire::actingAs($admin)
        ->test(Reportes::class)
        ->call('irNivel2', $dimension->id)
        ->assertSet('nivel', 2)
        ->assertSet('dimensionActivaId', $dimension->id);
});

it('irNivel3 cambia el nivel a 3 y asigna subdimensionActivaId', function () {
    $this->seed([DimensionesSeeder::class, SubdimensionesSeeder::class, PreguntasSeeder::class, OpcionesRespuestaSeeder::class]);

    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();
    $dimension = Dimension::first();
    $subdimension = $dimension->subdimensiones()->first();

    Livewire::actingAs($admin)
        ->test(Reportes::class)
        ->call('irNivel2', $dimension->id)
        ->call('irNivel3', $subdimension->id)
        ->assertSet('nivel', 3)
        ->assertSet('subdimensionActivaId', $subdimension->id);
});

it('irNivel1 resetea el nivel y los IDs activos', function () {
    $this->seed([DimensionesSeeder::class, SubdimensionesSeeder::class, PreguntasSeeder::class, OpcionesRespuestaSeeder::class]);

    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();
    $dimension = Dimension::first();

    Livewire::actingAs($admin)
        ->test(Reportes::class)
        ->call('irNivel2', $dimension->id)
        ->call('irNivel1')
        ->assertSet('nivel', 1)
        ->assertSet('dimensionActivaId', null);
});

it('getDatosNivel2 retorna subdimensiones de la dimension activa', function () {
    $this->seed([DimensionesSeeder::class, SubdimensionesSeeder::class, PreguntasSeeder::class, OpcionesRespuestaSeeder::class]);

    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();
    $encuesta = Encuesta::factory()->completada()->create(['empresa_id' => $empresa->id]);
    $dimension = Dimension::first();
    $opcion = OpcionRespuesta::where('valor_numerico', 3)->first();
    $preguntas = Pregunta::whereHas('subdimension', fn ($q) => $q->where('dimension_id', $dimension->id))->get();

    foreach ($preguntas as $pregunta) {
        Respuesta::create([
            'encuesta_id' => $encuesta->id,
            'pregunta_id' => $pregunta->id,
            'opcion_respuesta_id' => $opcion->id,
        ]);
    }

    $component = Livewire::actingAs($admin)->test(Reportes::class);
    $component->call('irNivel2', $dimension->id);
    $datosNivel2 = $component->instance()->getDatosNivel2();

    expect($datosNivel2)->not->toBeEmpty();
    expect($datosNivel2[0])->toHaveKeys(['id', 'nombre', 'puntaje']);
    expect($datosNivel2[0]['puntaje'])->toBe(100.0);
});

it('getDatosNivel3 retorna preguntas de la subdimension activa', function () {
    $this->seed([DimensionesSeeder::class, SubdimensionesSeeder::class, PreguntasSeeder::class, OpcionesRespuestaSeeder::class]);

    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();
    $encuesta = Encuesta::factory()->completada()->create(['empresa_id' => $empresa->id]);
    $dimension = Dimension::first();
    $subdimension = $dimension->subdimensiones()->first();
    $opcion = OpcionRespuesta::where('valor_numerico', 3)->first();
    $preguntas = Pregunta::where('subdimension_id', $subdimension->id)->get();

    foreach ($preguntas as $pregunta) {
        Respuesta::create([
            'encuesta_id' => $encuesta->id,
            'pregunta_id' => $pregunta->id,
            'opcion_respuesta_id' => $opcion->id,
        ]);
    }

    $component = Livewire::actingAs($admin)->test(Reportes::class);
    $component->call('irNivel2', $dimension->id);
    $component->call('irNivel3', $subdimension->id);
    $datosNivel3 = $component->instance()->getDatosNivel3();

    expect($datosNivel3)->not->toBeEmpty();
    expect($datosNivel3[0])->toHaveKeys(['id', 'texto', 'puntaje', 'total', 'distribucion']);
    expect($datosNivel3[0]['puntaje'])->toBe(100.0);
});

it('limpiarFiltros resetea todos los filtros y vuelve a nivel 1', function () {
    $this->seed([DimensionesSeeder::class, SubdimensionesSeeder::class, PreguntasSeeder::class, OpcionesRespuestaSeeder::class]);

    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();
    $dimension = Dimension::first();

    Livewire::actingAs($admin)
        ->test(Reportes::class)
        ->call('irNivel2', $dimension->id)
        ->set('filtroSexoId', '1')
        ->call('limpiarFiltros')
        ->assertSet('nivel', 1)
        ->assertSet('filtroSexoId', '')
        ->assertSet('dimensionActivaId', null);
});
