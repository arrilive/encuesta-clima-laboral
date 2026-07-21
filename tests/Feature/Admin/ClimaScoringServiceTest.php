<?php

use App\Models\Empresa;
use App\Models\Encuesta;
use App\Models\OpcionRespuesta;
use App\Models\Pregunta;
use App\Models\Respuesta;
use App\Services\ClimaScoringService;
use Database\Seeders\DimensionesSeeder;
use Database\Seeders\OpcionesRespuestaSeeder;
use Database\Seeders\PreguntasSeeder;
use Database\Seeders\SubdimensionesSeeder;

it('promedioGeneral retorna 0.0 cuando no hay respuestas', function () {
    $empresa = Empresa::factory()->create();
    $base = Respuesta::query()
        ->whereHas('encuesta', fn ($q) => $q
            ->where('estado', 'completado')
            ->whereHas('lote', fn ($q) => $q->where('empresa_id', $empresa->id))
        );
    $service = app(ClimaScoringService::class);

    expect($service->promedioGeneral($base))->toBe(0.0);
});

it('promedioGeneral excluye respuestas con valor_numerico = 0', function () {
    $this->seed([DimensionesSeeder::class, SubdimensionesSeeder::class, PreguntasSeeder::class, OpcionesRespuestaSeeder::class]);

    $empresa = Empresa::factory()->create();
    $encuesta = Encuesta::factory()->completada()->create(['lote_id' => \App\Models\Lote::factory()->for($empresa)->create()->id]);
    $opcion = OpcionRespuesta::where('valor_numerico', 0)->first();

    foreach (Pregunta::all() as $pregunta) {
        Respuesta::create([
            'encuesta_id' => $encuesta->id,
            'pregunta_id' => $pregunta->id,
            'opcion_respuesta_id' => $opcion->id,
        ]);
    }

    $base = Respuesta::query()
        ->whereHas('encuesta', fn ($q) => $q
            ->where('estado', 'completado')
            ->whereHas('lote', fn ($q) => $q->where('empresa_id', $empresa->id))
        );
    $service = app(ClimaScoringService::class);

    expect($service->promedioGeneral($base))->toBe(0.0);
});

it('promedioGeneral retorna 100.0 cuando todas las respuestas son valor_numerico = 3', function () {
    $this->seed([DimensionesSeeder::class, SubdimensionesSeeder::class, PreguntasSeeder::class, OpcionesRespuestaSeeder::class]);

    $empresa = Empresa::factory()->create();
    $encuesta = Encuesta::factory()->completada()->create(['lote_id' => \App\Models\Lote::factory()->for($empresa)->create()->id]);
    $opcion = OpcionRespuesta::where('valor_numerico', 3)->first();

    foreach (Pregunta::all() as $pregunta) {
        Respuesta::create([
            'encuesta_id' => $encuesta->id,
            'pregunta_id' => $pregunta->id,
            'opcion_respuesta_id' => $opcion->id,
        ]);
    }

    $base = Respuesta::query()
        ->whereHas('encuesta', fn ($q) => $q
            ->where('estado', 'completado')
            ->whereHas('lote', fn ($q) => $q->where('empresa_id', $empresa->id))
        );
    $service = app(ClimaScoringService::class);

    expect($service->promedioGeneral($base))->toBe(100.0);
});

it('scoresPorDimension retorna exactamente 6 dimensiones', function () {
    $this->seed([DimensionesSeeder::class, SubdimensionesSeeder::class, PreguntasSeeder::class, OpcionesRespuestaSeeder::class]);

    $empresa = Empresa::factory()->create();
    $base = Respuesta::query()
        ->whereHas('encuesta', fn ($q) => $q
            ->where('estado', 'completado')
            ->whereHas('lote', fn ($q) => $q->where('empresa_id', $empresa->id))
        );
    $service = app(ClimaScoringService::class);

    expect($service->scoresPorDimension($base)->count())->toBe(6);
});

it('scoresPorDimension retorna collection indexada numéricamente', function () {
    $this->seed([DimensionesSeeder::class, SubdimensionesSeeder::class, PreguntasSeeder::class, OpcionesRespuestaSeeder::class]);

    $empresa = Empresa::factory()->create();
    $base = Respuesta::query()
        ->whereHas('encuesta', fn ($q) => $q
            ->where('estado', 'completado')
            ->whereHas('lote', fn ($q) => $q->where('empresa_id', $empresa->id))
        );
    $service = app(ClimaScoringService::class);

    $result = $service->scoresPorDimension($base);

    expect($result->keys()->toArray())->toBe([0, 1, 2, 3, 4, 5]);
});

it('scoresPorSubdimension retorna collection indexada numéricamente', function () {
    $this->seed([DimensionesSeeder::class, SubdimensionesSeeder::class, PreguntasSeeder::class, OpcionesRespuestaSeeder::class]);

    $empresa = Empresa::factory()->create();
    $base = Respuesta::query()
        ->whereHas('encuesta', fn ($q) => $q
            ->where('estado', 'completado')
            ->whereHas('lote', fn ($q) => $q->where('empresa_id', $empresa->id))
        );
    $service = app(ClimaScoringService::class);

    $result = $service->scoresPorSubdimension($base);

    expect($result->count())->toBe(17);
    expect($result->keys()->toArray())->toBe(range(0, 16));
});

it('promedioGeneral calcula un promedio no ponderado de dimensiones', function () {
    $this->seed([DimensionesSeeder::class, SubdimensionesSeeder::class, PreguntasSeeder::class, OpcionesRespuestaSeeder::class]);

    $empresa = Empresa::factory()->create();
    $encuesta = Encuesta::factory()->completada()->create(['lote_id' => \App\Models\Lote::factory()->for($empresa)->create()->id]);
    $opcionMin = OpcionRespuesta::where('valor_numerico', 1)->first(); // 0 pts
    $opcionMax = OpcionRespuesta::where('valor_numerico', 3)->first(); // 100 pts

    // Dimensión 1: Le damos 100 pts (Verdadero)
    $d1 = \App\Models\Dimension::orderBy('orden')->first();
    $p1 = $d1->preguntas()->first();
    Respuesta::create([
        'encuesta_id' => $encuesta->id,
        'pregunta_id' => $p1->id,
        'opcion_respuesta_id' => $opcionMax->id,
    ]);

    // Dimensión 2: Le damos 0 pts (Falso) pero con MUCHAS respuestas (ej: 10) para intentar sesgar el promedio
    $d2 = \App\Models\Dimension::orderBy('orden')->skip(1)->first();
    $preguntasD2 = $d2->preguntas()->limit(10)->get();
    foreach ($preguntasD2 as $p) {
        Respuesta::create([
            'encuesta_id' => $encuesta->id,
            'pregunta_id' => $p->id,
            'opcion_respuesta_id' => $opcionMin->id,
        ]);
    }

    $base = Respuesta::query()
        ->whereHas('encuesta', fn ($q) => $q
            ->where('estado', 'completado')
            ->whereHas('lote', fn ($q) => $q->where('empresa_id', $empresa->id))
        );
    $service = app(ClimaScoringService::class);

    // Si fuera ponderado (1 Max vs 10 Min): (100 + 0*10) / 11 = 9.09
    // Como es NO ponderado (Promedio Dimensiones): (100 + 0) / 2 = 50.0
    expect($service->promedioGeneral($base))->toBe(50.0);
});

it('promediosGeneralesPorEmpresas retorna null para empresas sin lotes', function () {
    $empresa = Empresa::factory()->create();
    $service = app(ClimaScoringService::class);
    $result = $service->promediosGeneralesPorEmpresas([$empresa->id]);
    expect($result->get($empresa->id))->toBeNull();
});

it('promediosGeneralesPorEmpresas usa el lote cerrado más reciente cuando hay cerrado y activo', function () {
    $this->seed([DimensionesSeeder::class, SubdimensionesSeeder::class, PreguntasSeeder::class, OpcionesRespuestaSeeder::class]);
    $empresa = Empresa::factory()->create();

    // Lote cerrado más antiguo (fecha_fin en el pasado)
    $loteCerradoAntiguo = \App\Models\Lote::factory()->create([
        'empresa_id' => $empresa->id,
        'fecha_inicio' => now()->subDays(60),
        'fecha_fin' => now()->subDays(30),
        'activo' => false,
    ]);

    // Lote cerrado más reciente (fecha_fin en el pasado)
    $loteCerradoReciente = \App\Models\Lote::factory()->create([
        'empresa_id' => $empresa->id,
        'fecha_inicio' => now()->subDays(40),
        'fecha_fin' => now()->subDays(10),
        'activo' => false,
    ]);

    // Lote activo actual (fecha_fin nula o en el futuro)
    $loteActivo = \App\Models\Lote::factory()->create([
        'empresa_id' => $empresa->id,
        'fecha_inicio' => now()->subDays(5),
        'fecha_fin' => null,
        'activo' => true,
    ]);

    // Completamos encuestas en todos
    // Pero en el lote reciente ponemos valor_numerico = 3 (100 pts)
    // En el lote antiguo ponemos valor_numerico = 1 (0 pts)
    // En el lote activo ponemos valor_numerico = 2 (50 pts)
    $opcionMin = OpcionRespuesta::where('valor_numerico', 1)->first();
    $opcionMid = OpcionRespuesta::where('valor_numerico', 2)->first();
    $opcionMax = OpcionRespuesta::where('valor_numerico', 3)->first();

    // Necesitamos al menos 5 encuestas completas por lote para pasar el umbral
    for ($i = 0; $i < 5; $i++) {
        $enc1 = Encuesta::factory()->completada()->create(['lote_id' => $loteCerradoAntiguo->id]);
        $enc2 = Encuesta::factory()->completada()->create(['lote_id' => $loteCerradoReciente->id]);
        $enc3 = Encuesta::factory()->completada()->create(['lote_id' => $loteActivo->id]);

        foreach (Pregunta::all() as $pregunta) {
            Respuesta::create([
                'encuesta_id' => $enc1->id,
                'pregunta_id' => $pregunta->id,
                'opcion_respuesta_id' => $opcionMin->id,
            ]);
            Respuesta::create([
                'encuesta_id' => $enc2->id,
                'pregunta_id' => $pregunta->id,
                'opcion_respuesta_id' => $opcionMax->id,
            ]);
            Respuesta::create([
                'encuesta_id' => $enc3->id,
                'pregunta_id' => $pregunta->id,
                'opcion_respuesta_id' => $opcionMid->id,
            ]);
        }
    }

    $service = app(ClimaScoringService::class);
    $result = $service->promediosGeneralesPorEmpresas([$empresa->id]);

    // Debería tomar el lote cerrado reciente (100.0 pts)
    expect($result->get($empresa->id))->toBe(100.0);
});

it('promediosGeneralesPorEmpresas retorna null si no alcanza el umbral de anonimato', function () {
    $this->seed([DimensionesSeeder::class, SubdimensionesSeeder::class, PreguntasSeeder::class, OpcionesRespuestaSeeder::class]);
    $empresa = Empresa::factory()->create();

    $lote = \App\Models\Lote::factory()->create([
        'empresa_id' => $empresa->id,
        'fecha_inicio' => now()->subDays(40),
        'fecha_fin' => now()->subDays(10),
        'activo' => false,
    ]);

    // Solo 4 encuestas completadas (menos del umbral de 5)
    $opcionMax = OpcionRespuesta::where('valor_numerico', 3)->first();
    for ($i = 0; $i < 4; $i++) {
        $enc = Encuesta::factory()->completada()->create(['lote_id' => $lote->id]);
        foreach (Pregunta::all() as $pregunta) {
            Respuesta::create([
                'encuesta_id' => $enc->id,
                'pregunta_id' => $pregunta->id,
                'opcion_respuesta_id' => $opcionMax->id,
            ]);
        }
    }

    $service = app(ClimaScoringService::class);
    $result = $service->promediosGeneralesPorEmpresas([$empresa->id]);

    expect($result->get($empresa->id))->toBeNull();
});
