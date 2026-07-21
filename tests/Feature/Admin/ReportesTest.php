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
    $encuesta = Encuesta::factory()->completada()->create(['lote_id' => \App\Models\Lote::factory()->for($empresa)->create()->id]);

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
    $encuesta = Encuesta::factory()->completada()->create(['lote_id' => \App\Models\Lote::factory()->for($empresa)->create()->id]);

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
    $encuesta = Encuesta::factory()->completada()->create(['lote_id' => \App\Models\Lote::factory()->for($empresa2)->create()->id]);

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

    $encuesta1 = Encuesta::factory()->completada()->create(['lote_id' => \App\Models\Lote::factory()->for($empresa1)->create()->id]);
    $encuesta2 = Encuesta::factory()->completada()->create(['lote_id' => \App\Models\Lote::factory()->for($empresa2)->create()->id]);

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
    $encuesta = Encuesta::factory()->completada()->create(['lote_id' => \App\Models\Lote::factory()->for($empresa)->create()->id]);
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
    $encuesta = Encuesta::factory()->completada()->create(['lote_id' => \App\Models\Lote::factory()->for($empresa)->create()->id]);
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

it('filtroLoteId filtra respuestas al lote seleccionado', function () {
    $this->seed([DimensionesSeeder::class, SubdimensionesSeeder::class, PreguntasSeeder::class, OpcionesRespuestaSeeder::class]);

    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();

    $lote1 = \App\Models\Lote::factory()->for($empresa)->create();
    $lote2 = \App\Models\Lote::factory()->for($empresa)->create();

    $encuesta1 = Encuesta::factory()->completada()->create(['lote_id' => $lote1->id]);
    $encuesta2 = Encuesta::factory()->completada()->create(['lote_id' => $lote2->id]);

    $dimension = Dimension::first();
    $opcionVerdadero = OpcionRespuesta::where('valor_numerico', 3)->first();
    $opcionFalso = OpcionRespuesta::where('valor_numerico', 1)->first();
    $preguntas = Pregunta::whereHas('subdimension', fn ($q) => $q->where('dimension_id', $dimension->id))->get();

    foreach ($preguntas as $pregunta) {
        Respuesta::create([
            'encuesta_id' => $encuesta1->id,
            'pregunta_id' => $pregunta->id,
            'opcion_respuesta_id' => $opcionVerdadero->id,
        ]);
    }

    foreach ($preguntas as $pregunta) {
        Respuesta::create([
            'encuesta_id' => $encuesta2->id,
            'pregunta_id' => $pregunta->id,
            'opcion_respuesta_id' => $opcionFalso->id,
        ]);
    }

    $component = Livewire::actingAs($admin)->test(Reportes::class);

    $component->set('filtroLoteId', (string) $lote1->id);
    $datosNivel1 = $component->instance()->getDatosNivel1();
    $puntajeLote1 = collect($datosNivel1)->firstWhere('id', $dimension->id)['puntaje'];
    expect($puntajeLote1)->toBe(100.0);

    $component->set('filtroLoteId', (string) $lote2->id);
    $datosNivel1 = $component->instance()->getDatosNivel1();
    $puntajeLote2 = collect($datosNivel1)->firstWhere('id', $dimension->id)['puntaje'];
    expect($puntajeLote2)->toBe(0.0);
});

it('limpiarFiltros resetea filtroLoteId', function () {
    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();

    Livewire::actingAs($admin)
        ->test(Reportes::class)
        ->set('filtroLoteId', '123')
        ->call('limpiarFiltros')
        ->assertSet('filtroLoteId', '');
});

it('updatedFiltroEmpresaId resetea filtroLoteId', function () {
    $empresa = Empresa::factory()->create();
    $superAdmin = User::factory()->superAdmin()->create();

    Livewire::actingAs($superAdmin)
        ->test(Reportes::class)
        ->set('filtroEmpresaId', (string) $empresa->id)
        ->set('filtroLoteId', '123')
        ->set('filtroEmpresaId', '456')
        ->assertSet('filtroLoteId', '');
});

it('la exportacion a PDF aplica el filtro por lote y formatea correctamente su etiqueta', function () {
    $this->seed([DimensionesSeeder::class, SubdimensionesSeeder::class, PreguntasSeeder::class, OpcionesRespuestaSeeder::class]);

    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();

    $lote = \App\Models\Lote::factory()->for($empresa)->create(['nombre' => null]);
    $encuesta = Encuesta::factory()->completada()->create(['lote_id' => $lote->id]);

    $opcion = OpcionRespuesta::where('valor_numerico', 3)->first();
    Respuesta::create([
        'encuesta_id' => $encuesta->id,
        'pregunta_id' => Pregunta::first()->id,
        'opcion_respuesta_id' => $opcion->id,
    ]);

    $this->actingAs($admin);

    $response = $this->get(route('admin.reportes.pdf', [
        'lote_id' => $lote->id,
        'alcance' => 'dimensiones',
    ]));

    $response->assertOk();
});

it('filtroSucursalId filtra respuestas a la sucursal seleccionada', function () {
    $this->seed([DimensionesSeeder::class, SubdimensionesSeeder::class, PreguntasSeeder::class, OpcionesRespuestaSeeder::class]);

    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();

    $sucursal = \App\Models\Sucursal::factory()->create(['empresa_id' => $empresa->id]);

    $loteGeneral = \App\Models\Lote::factory()->for($empresa)->create(['sucursal_id' => null]);
    $loteSucursal = \App\Models\Lote::factory()->for($empresa)->create(['sucursal_id' => $sucursal->id]);

    $encuestaGeneral = Encuesta::factory()->completada()->create(['lote_id' => $loteGeneral->id]);
    $encuestaSucursal = Encuesta::factory()->completada()->create(['lote_id' => $loteSucursal->id]);

    $dimension = Dimension::first();
    $opcionVerdadero = OpcionRespuesta::where('valor_numerico', 3)->first();
    $opcionFalso = OpcionRespuesta::where('valor_numerico', 1)->first();
    $preguntas = Pregunta::whereHas('subdimension', fn ($q) => $q->where('dimension_id', $dimension->id))->get();

    foreach ($preguntas as $pregunta) {
        Respuesta::create([
            'encuesta_id' => $encuestaSucursal->id,
            'pregunta_id' => $pregunta->id,
            'opcion_respuesta_id' => $opcionVerdadero->id,
        ]);
        Respuesta::create([
            'encuesta_id' => $encuestaGeneral->id,
            'pregunta_id' => $pregunta->id,
            'opcion_respuesta_id' => $opcionFalso->id,
        ]);
    }

    $component = Livewire::actingAs($admin)->test(Reportes::class);

    $component->set('filtroSucursalId', (string) $sucursal->id);
    $datosNivel1 = $component->instance()->getDatosNivel1();
    $puntajeSucursal = collect($datosNivel1)->firstWhere('id', $dimension->id)['puntaje'];
    expect($puntajeSucursal)->toBe(100.0);
});

it('filtroCorporativoId filtra respuestas al corporativo seleccionado', function () {
    $this->seed([DimensionesSeeder::class, SubdimensionesSeeder::class, PreguntasSeeder::class, OpcionesRespuestaSeeder::class]);

    $superAdmin = User::factory()->superAdmin()->create();

    $corp1 = \App\Models\Corporativo::factory()->create();
    $corp2 = \App\Models\Corporativo::factory()->create();

    $empresa1 = Empresa::factory()->create(['corporativo_id' => $corp1->id]);
    $empresa2 = Empresa::factory()->create(['corporativo_id' => $corp2->id]);

    $lote1 = \App\Models\Lote::factory()->for($empresa1)->create();
    $lote2 = \App\Models\Lote::factory()->for($empresa2)->create();

    $encuesta1 = Encuesta::factory()->completada()->create(['lote_id' => $lote1->id]);
    $encuesta2 = Encuesta::factory()->completada()->create(['lote_id' => $lote2->id]);

    $dimension = Dimension::first();
    $opcionVerdadero = OpcionRespuesta::where('valor_numerico', 3)->first();
    $opcionFalso = OpcionRespuesta::where('valor_numerico', 1)->first();
    $preguntas = Pregunta::whereHas('subdimension', fn ($q) => $q->where('dimension_id', $dimension->id))->get();

    foreach ($preguntas as $pregunta) {
        Respuesta::create([
            'encuesta_id' => $encuesta1->id,
            'pregunta_id' => $pregunta->id,
            'opcion_respuesta_id' => $opcionVerdadero->id,
        ]);
        Respuesta::create([
            'encuesta_id' => $encuesta2->id,
            'pregunta_id' => $pregunta->id,
            'opcion_respuesta_id' => $opcionFalso->id,
        ]);
    }

    $component = Livewire::actingAs($superAdmin)->test(Reportes::class);

    $component->set('filtroCorporativoId', (string) $corp1->id);
    $datosNivel1 = $component->instance()->getDatosNivel1();
    $puntajeCorp1 = collect($datosNivel1)->firstWhere('id', $dimension->id)['puntaje'];
    expect($puntajeCorp1)->toBe(100.0);

    $component->set('filtroCorporativoId', (string) $corp2->id);
    $datosNivel1 = $component->instance()->getDatosNivel1();
    $puntajeCorp2 = collect($datosNivel1)->firstWhere('id', $dimension->id)['puntaje'];
    expect($puntajeCorp2)->toBe(0.0);
});

it('limpiarFiltros resetea filtroCorporativoId y filtroSucursalId', function () {
    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();

    Livewire::actingAs($admin)
        ->test(Reportes::class)
        ->set('filtroCorporativoId', '12')
        ->set('filtroSucursalId', '34')
        ->call('limpiarFiltros')
        ->assertSet('filtroCorporativoId', '')
        ->assertSet('filtroSucursalId', '');
});

it('updatedFiltroCorporativoId limpia empresa sucursal y lote', function () {
    $superAdmin = User::factory()->superAdmin()->create();

    Livewire::actingAs($superAdmin)
        ->test(Reportes::class)
        ->set('filtroEmpresaId', '1')
        ->set('filtroSucursalId', '2')
        ->set('filtroLoteId', '3')
        ->set('filtroCorporativoId', '4')
        ->assertSet('filtroEmpresaId', '')
        ->assertSet('filtroSucursalId', '')
        ->assertSet('filtroLoteId', '');
});

it('cuando hay menos de 5 respuestas completadas bajoUmbral es true y sinDatos es false', function () {
    $this->seed([DimensionesSeeder::class, SubdimensionesSeeder::class, PreguntasSeeder::class, OpcionesRespuestaSeeder::class]);

    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();
    $lote = \App\Models\Lote::factory()->for($empresa)->create();

    for ($i = 0; $i < 3; $i++) {
        $encuesta = Encuesta::factory()->completada()->create(['lote_id' => $lote->id]);
        Respuesta::create([
            'encuesta_id' => $encuesta->id,
            'pregunta_id' => Pregunta::first()->id,
            'opcion_respuesta_id' => OpcionRespuesta::first()->id,
        ]);
    }

    Livewire::actingAs($admin)
        ->test(Reportes::class)
        ->assertViewHas('bajoUmbral', true)
        ->assertViewHas('sinDatos', false)
        ->assertSee('Resultados protegidos')
        ->assertSee('Se necesitan al menos 5 respuestas');
});

it('cuando hay exactamente 5 respuestas completadas bajoUmbral es false', function () {
    $this->seed([DimensionesSeeder::class, SubdimensionesSeeder::class, PreguntasSeeder::class, OpcionesRespuestaSeeder::class]);

    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();
    $lote = \App\Models\Lote::factory()->for($empresa)->create();

    for ($i = 0; $i < 5; $i++) {
        $encuesta = Encuesta::factory()->completada()->create(['lote_id' => $lote->id]);
        Respuesta::create([
            'encuesta_id' => $encuesta->id,
            'pregunta_id' => Pregunta::first()->id,
            'opcion_respuesta_id' => OpcionRespuesta::first()->id,
        ]);
    }

    Livewire::actingAs($admin)
        ->test(Reportes::class)
        ->assertViewHas('bajoUmbral', false)
        ->assertViewHas('sinDatos', false)
        ->assertDontSee('Resultados protegidos');
});

it('reportes muestra sinDatos en escenario 1 cuando no hay ningún lote', function () {
    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();

    Livewire::actingAs($admin)
        ->test(Reportes::class)
        ->assertViewHas('sinDatos', true);
});

it('reportes usa el lote activo en escenario 2 cuando no hay cerrado previo', function () {
    $this->seed([DimensionesSeeder::class, SubdimensionesSeeder::class, PreguntasSeeder::class, OpcionesRespuestaSeeder::class]);
    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();

    $loteActivo = \App\Models\Lote::factory()->create([
        'empresa_id' => $empresa->id,
        'fecha_inicio' => now()->subDays(5),
        'fecha_fin' => null,
        'activo' => true,
    ]);

    $opcion = OpcionRespuesta::where('valor_numerico', 3)->first();
    for ($i = 0; $i < 5; $i++) {
        $enc = Encuesta::factory()->completada()->create(['lote_id' => $loteActivo->id]);
        Respuesta::create([
            'encuesta_id' => $enc->id,
            'pregunta_id' => Pregunta::first()->id,
            'opcion_respuesta_id' => $opcion->id,
        ]);
    }

    Livewire::actingAs($admin)
        ->test(Reportes::class)
        ->assertViewHas('sinDatos', false)
        ->assertViewHas('promedioGeneral', 100.0);
});

it('reportes usa el lote cerrado más reciente en escenario 3 cuando no hay lote activo', function () {
    $this->seed([DimensionesSeeder::class, SubdimensionesSeeder::class, PreguntasSeeder::class, OpcionesRespuestaSeeder::class]);
    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();

    $loteCerrado = \App\Models\Lote::factory()->create([
        'empresa_id' => $empresa->id,
        'fecha_inicio' => now()->subDays(20),
        'fecha_fin' => now()->subDays(5),
        'activo' => false,
    ]);

    $opcion = OpcionRespuesta::where('valor_numerico', 3)->first();
    for ($i = 0; $i < 5; $i++) {
        $enc = Encuesta::factory()->completada()->create(['lote_id' => $loteCerrado->id]);
        Respuesta::create([
            'encuesta_id' => $enc->id,
            'pregunta_id' => Pregunta::first()->id,
            'opcion_respuesta_id' => $opcion->id,
        ]);
    }

    Livewire::actingAs($admin)
        ->test(Reportes::class)
        ->assertViewHas('sinDatos', false)
        ->assertViewHas('promedioGeneral', 100.0);
});

it('reportes usa el lote cerrado en escenario 4 cuando hay cerrado y activo en curso', function () {
    $this->seed([DimensionesSeeder::class, SubdimensionesSeeder::class, PreguntasSeeder::class, OpcionesRespuestaSeeder::class]);
    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();

    $loteCerrado = \App\Models\Lote::factory()->create([
        'empresa_id' => $empresa->id,
        'fecha_inicio' => now()->subDays(20),
        'fecha_fin' => now()->subDays(5),
        'activo' => false,
    ]);

    $loteActivo = \App\Models\Lote::factory()->create([
        'empresa_id' => $empresa->id,
        'fecha_inicio' => now()->subDays(3),
        'fecha_fin' => null,
        'activo' => true,
    ]);

    $opcionMax = OpcionRespuesta::where('valor_numerico', 3)->first();
    $opcionMin = OpcionRespuesta::where('valor_numerico', 1)->first();

    for ($i = 0; $i < 5; $i++) {
        $enc = Encuesta::factory()->completada()->create(['lote_id' => $loteCerrado->id]);
        Respuesta::create([
            'encuesta_id' => $enc->id,
            'pregunta_id' => Pregunta::first()->id,
            'opcion_respuesta_id' => $opcionMax->id,
        ]);
    }

    for ($i = 0; $i < 5; $i++) {
        $enc = Encuesta::factory()->completada()->create(['lote_id' => $loteActivo->id]);
        Respuesta::create([
            'encuesta_id' => $enc->id,
            'pregunta_id' => Pregunta::first()->id,
            'opcion_respuesta_id' => $opcionMin->id,
        ]);
    }

    Livewire::actingAs($admin)
        ->test(Reportes::class)
        ->assertViewHas('promedioGeneral', 100.0);
});

it('reportes respeta el filtroLoteId manual sin importar si el lote esta cerrado o no', function () {
    $this->seed([DimensionesSeeder::class, SubdimensionesSeeder::class, PreguntasSeeder::class, OpcionesRespuestaSeeder::class]);
    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();

    $loteCerrado = \App\Models\Lote::factory()->create([
        'empresa_id' => $empresa->id,
        'fecha_inicio' => now()->subDays(20),
        'fecha_fin' => now()->subDays(5),
        'activo' => false,
    ]);

    $loteActivo = \App\Models\Lote::factory()->create([
        'empresa_id' => $empresa->id,
        'fecha_inicio' => now()->subDays(3),
        'fecha_fin' => null,
        'activo' => true,
    ]);

    $opcionMax = OpcionRespuesta::where('valor_numerico', 3)->first();
    $opcionMin = OpcionRespuesta::where('valor_numerico', 1)->first();

    for ($i = 0; $i < 5; $i++) {
        $enc1 = Encuesta::factory()->completada()->create(['lote_id' => $loteCerrado->id]);
        Respuesta::create([
            'encuesta_id' => $enc1->id,
            'pregunta_id' => Pregunta::first()->id,
            'opcion_respuesta_id' => $opcionMax->id,
        ]);

        $enc2 = Encuesta::factory()->completada()->create(['lote_id' => $loteActivo->id]);
        Respuesta::create([
            'encuesta_id' => $enc2->id,
            'pregunta_id' => Pregunta::first()->id,
            'opcion_respuesta_id' => $opcionMin->id,
        ]);
    }

    Livewire::actingAs($admin)
        ->test(Reportes::class)
        ->set('filtroLoteId', (string) $loteActivo->id)
        ->assertViewHas('promedioGeneral', 0.0);
});

it('los filtros demograficos se aplican sobre el lote resuelto sin arrastrar datos historicos', function () {
    $this->seed([DimensionesSeeder::class, SubdimensionesSeeder::class, PreguntasSeeder::class, OpcionesRespuestaSeeder::class, \Database\Seeders\SexosSeeder::class]);
    $empresa = Empresa::factory()->create();
    $admin = User::factory()->adminEmpresa($empresa->id)->create();
    $sexos = \App\Models\Sexo::all();
    $sexoMasculino = $sexos->first();

    // Lote antiguo (cerrado)
    $loteAntiguo = \App\Models\Lote::factory()->create([
        'empresa_id' => $empresa->id,
        'fecha_inicio' => now()->subDays(60),
        'fecha_fin' => now()->subDays(30),
        'activo' => false,
    ]);

    // Lote resuelto (cerrado mas reciente)
    $loteReciente = \App\Models\Lote::factory()->create([
        'empresa_id' => $empresa->id,
        'fecha_inicio' => now()->subDays(20),
        'fecha_fin' => now()->subDays(5),
        'activo' => false,
    ]);

    $opcionMax = OpcionRespuesta::where('valor_numerico', 3)->first();
    $opcionMin = OpcionRespuesta::where('valor_numerico', 1)->first();

    // En el lote antiguo hay respuestas de sexoMasculino con 0 pts (opcionMin)
    for ($i = 0; $i < 5; $i++) {
        $enc = Encuesta::factory()->completada()->create(['lote_id' => $loteAntiguo->id]);
        \App\Models\DatoDemografico::create(['encuesta_id' => $enc->id, 'sexo_id' => $sexoMasculino->id]);
        Respuesta::create(['encuesta_id' => $enc->id, 'pregunta_id' => Pregunta::first()->id, 'opcion_respuesta_id' => $opcionMin->id]);
    }

    // En el lote reciente hay respuestas de sexoMasculino con 100 pts (opcionMax)
    for ($i = 0; $i < 5; $i++) {
        $enc = Encuesta::factory()->completada()->create(['lote_id' => $loteReciente->id]);
        \App\Models\DatoDemografico::create(['encuesta_id' => $enc->id, 'sexo_id' => $sexoMasculino->id]);
        Respuesta::create(['encuesta_id' => $enc->id, 'pregunta_id' => Pregunta::first()->id, 'opcion_respuesta_id' => $opcionMax->id]);
    }

    Livewire::actingAs($admin)
        ->test(Reportes::class)
        ->set('filtroSexoId', (string) $sexoMasculino->id)
        ->assertViewHas('promedioGeneral', 100.0);
});
