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
            ->where('empresa_id', $empresa->id)
        );
    $service = app(ClimaScoringService::class);

    expect($service->promedioGeneral($base))->toBe(0.0);
});

it('promedioGeneral excluye respuestas con valor_numerico = 0', function () {
    $this->seed([DimensionesSeeder::class, SubdimensionesSeeder::class, PreguntasSeeder::class, OpcionesRespuestaSeeder::class]);

    $empresa = Empresa::factory()->create();
    $encuesta = Encuesta::factory()->completada()->create(['empresa_id' => $empresa->id]);
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
            ->where('empresa_id', $empresa->id)
        );
    $service = app(ClimaScoringService::class);

    expect($service->promedioGeneral($base))->toBe(0.0);
});

it('promedioGeneral retorna 100.0 cuando todas las respuestas son valor_numerico = 3', function () {
    $this->seed([DimensionesSeeder::class, SubdimensionesSeeder::class, PreguntasSeeder::class, OpcionesRespuestaSeeder::class]);

    $empresa = Empresa::factory()->create();
    $encuesta = Encuesta::factory()->completada()->create(['empresa_id' => $empresa->id]);
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
            ->where('empresa_id', $empresa->id)
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
            ->where('empresa_id', $empresa->id)
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
            ->where('empresa_id', $empresa->id)
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
            ->where('empresa_id', $empresa->id)
        );
    $service = app(ClimaScoringService::class);

    $result = $service->scoresPorSubdimension($base);

    expect($result->count())->toBe(17);
    expect($result->keys()->toArray())->toBe(range(0, 16));
});

it('promedioGeneral calcula un promedio no ponderado de dimensiones', function () {
    $this->seed([DimensionesSeeder::class, SubdimensionesSeeder::class, PreguntasSeeder::class, OpcionesRespuestaSeeder::class]);

    $empresa = Empresa::factory()->create();
    $encuesta = Encuesta::factory()->completada()->create(['empresa_id' => $empresa->id]);
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
            ->where('empresa_id', $empresa->id)
        );
    $service = app(ClimaScoringService::class);

    // Si fuera ponderado (1 Max vs 10 Min): (100 + 0*10) / 11 = 9.09
    // Como es NO ponderado (Promedio Dimensiones): (100 + 0) / 2 = 50.0
    expect($service->promedioGeneral($base))->toBe(50.0);
});
