<?php

namespace Database\Seeders;

use App\Models\DatoDemografico;
use App\Models\Empresa;
use App\Models\Encuesta;
use App\Models\OpcionRespuesta;
use App\Models\Pregunta;
use App\Models\PreguntaAbierta;
use App\Models\Subdimension;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // ----------------------------------------------------------------
            // 1. Setup — cargar catálogos en memoria (una query cada uno)
            // ----------------------------------------------------------------
            $empresa = Empresa::where('nombre', 'Empresa Demo')->firstOrFail();

            /** @var \Illuminate\Database\Eloquent\Collection<int, Pregunta> */
            $preguntas = Pregunta::all();

            // Subdimensiones con su dimensión cargada, indexadas por id
            /** @var \Illuminate\Support\Collection<int, Subdimension> */
            $subdimensiones = Subdimension::with('dimension')->get()->keyBy('id');

            // Las 4 opciones de respuesta globales (no están ligadas a una pregunta específica)
            $opcionesRespuesta = OpcionRespuesta::all();

            // IDs de preguntas abiertas
            $preguntasAbiertasIds = PreguntaAbierta::pluck('id');

            // Catálogos demográficos
            $sexosIds          = DB::table('sexos')->pluck('id')->toArray();
            $cargosIds         = DB::table('cargos')->pluck('id')->toArray();
            $edadesIds         = DB::table('edades')->orderBy('orden')->pluck('id')->toArray();
            $gradosIds         = DB::table('grados_academicos')->orderBy('orden')->pluck('id')->toArray();
            $antiguedadesIds   = DB::table('antiguedades')->orderBy('orden')->pluck('id')->toArray();
            $lugaresIds        = DB::table('lugares_trabajo')->pluck('id')->toArray();

            $now = now();

            // ----------------------------------------------------------------
            // 2. Mapeo de opciones → categoría de peso
            //    Texto en BD: 'Verdadero', 'A veces falso/a veces verdadero',
            //                 'Falso', 'Prefiero no responder'
            // ----------------------------------------------------------------
            $categoriaDeOpcion = [];
            foreach ($opcionesRespuesta as $opcion) {
                $texto = $opcion->opcion;
                if (str_contains($texto, 'A veces')) {
                    $categoriaDeOpcion[$opcion->id] = 'a_veces';
                } elseif (str_contains($texto, 'Verdadero')) {
                    $categoriaDeOpcion[$opcion->id] = 'verdadero';
                } elseif (str_contains($texto, 'Falso')) {
                    $categoriaDeOpcion[$opcion->id] = 'falso';
                } elseif (str_contains($texto, 'Prefiero')) {
                    $categoriaDeOpcion[$opcion->id] = 'no_responde';
                }
            }

            // ----------------------------------------------------------------
            // 3. Pesos de respuesta por dimensión (nombres reales en BD)
            // ----------------------------------------------------------------
            $pesosPorDimension = [
                'Credibilidad'            => ['verdadero' => 65, 'a_veces' => 25, 'falso' => 7,  'no_responde' => 3],
                'Respeto'                 => ['verdadero' => 55, 'a_veces' => 30, 'falso' => 10, 'no_responde' => 5],
                'Imparcialidad'           => ['verdadero' => 48, 'a_veces' => 32, 'falso' => 15, 'no_responde' => 5],
                'Orgullo'                 => ['verdadero' => 60, 'a_veces' => 28, 'falso' => 8,  'no_responde' => 4],
                'Compañerismo'            => ['verdadero' => 60, 'a_veces' => 28, 'falso' => 8,  'no_responde' => 4],
                'Seguridad y Capacitación'=> ['verdadero' => 30, 'a_veces' => 35, 'falso' => 28, 'no_responde' => 7],
            ];
            $pesosDefault = ['verdadero' => 50, 'a_veces' => 30, 'falso' => 15, 'no_responde' => 5];

            // Pre-construir pools de IDs ponderados por dimensión
            // pool[dimensionNombre][categoría] → array de opcion_id's repetidos
            $poolsPorDimension = [];
            foreach ($pesosPorDimension as $dimNombre => $pesos) {
                $pool = [];
                foreach ($categoriaDeOpcion as $opcionId => $categoria) {
                    $repeticiones = $pesos[$categoria] ?? 0;
                    for ($i = 0; $i < $repeticiones; $i++) {
                        $pool[] = $opcionId;
                    }
                }
                $poolsPorDimension[$dimNombre] = $pool;
            }
            // Pool por defecto
            $poolDefault = [];
            foreach ($categoriaDeOpcion as $opcionId => $categoria) {
                $repeticiones = $pesosDefault[$categoria] ?? 0;
                for ($i = 0; $i < $repeticiones; $i++) {
                    $poolDefault[] = $opcionId;
                }
            }

            // ----------------------------------------------------------------
            // 4. Distribuciones demográficas ponderadas
            // ----------------------------------------------------------------

            // Sexo: distribuir equitativamente entre los sexos disponibles
            // (SexosSeeder sólo crea Mujer y Hombre)
            $sexosPool = [];
            $sexoCount = count($sexosIds);
            foreach ($sexosIds as $idx => $id) {
                // ~60% al primero (Mujer orden=1), ~40% al segundo (Hombre orden=2)
                $pct = ($idx === 0) ? 60 : 40;
                for ($i = 0; $i < $pct; $i++) {
                    $sexosPool[] = $id;
                }
            }

            // Antigüedad: 5 rangos — 2yrsmenos(20%), 3-5(30%), 6-10(25%), 11-15(15%), 16+(10%)
            $antiguedadPool = [];
            $antiguedadPcts = [20, 30, 25, 15, 10];
            foreach ($antiguedadesIds as $idx => $id) {
                $pct = $antiguedadPcts[$idx] ?? 10;
                for ($i = 0; $i < $pct; $i++) {
                    $antiguedadPool[] = $id;
                }
            }

            // Edad: 5 rangos — extremos 15%, centrales 25% (18-20: 10%, 21-25: 20%, 26-34: 30%, 35-44: 25%, 45+: 15%)
            $edadPool = [];
            $edadPcts = [10, 20, 30, 25, 15];
            foreach ($edadesIds as $idx => $id) {
                $pct = $edadPcts[$idx] ?? 20;
                for ($i = 0; $i < $pct; $i++) {
                    $edadPool[] = $id;
                }
            }

            // Grado académico: 5 opciones — Prep.trunca(5%), Prep/Técnico(25%), Lic.trunca(15%), Lic/Ing(40%), Posgrado(15%)
            $gradoPool = [];
            $gradoPcts = [5, 25, 15, 40, 15];
            foreach ($gradosIds as $idx => $id) {
                $pct = $gradoPcts[$idx] ?? 20;
                for ($i = 0; $i < $pct; $i++) {
                    $gradoPool[] = $id;
                }
            }

            // ----------------------------------------------------------------
            // 5. Loop: 93 encuestas COMPLETADAS
            // ----------------------------------------------------------------
            $respuestasBatch       = [];
            $respuestasAbiertasBatch = [];

            for ($i = 0; $i < 93; $i++) {
                $createdAt        = now()->subDays(fake()->numberBetween(8, 60));
                $fechaAsignacion  = (clone $createdAt)->addMinutes(fake()->numberBetween(2, 360));
                $fechaCompletada  = (clone $fechaAsignacion)->addMinutes(fake()->numberBetween(15, 7200));

                $encuesta = Encuesta::create([
                    'token'            => Str::uuid()->toString(),
                    'empresa_id'       => $empresa->id,
                    'estado'           => 'completado',
                    'fecha_asignacion' => $fechaAsignacion,
                    'fecha_completada' => $fechaCompletada,
                    'created_at'       => $createdAt,
                    'updated_at'       => $now,
                ]);

                // Dato demográfico
                DatoDemografico::create([
                    'encuesta_id'       => $encuesta->id,
                    'sexo_id'           => fake()->randomElement($sexosPool),
                    'antiguedad_id'     => fake()->randomElement($antiguedadPool),
                    'edad_id'           => fake()->randomElement($edadPool),
                    'cargo_id'          => fake()->randomElement($cargosIds),
                    'grado_academico_id'=> fake()->randomElement($gradoPool),
                    'lugar_trabajo_id'  => fake()->randomElement($lugaresIds),
                ]);

                // Respuestas cerradas — acumular en batch
                foreach ($preguntas as $pregunta) {
                    $dimNombre = $subdimensiones[$pregunta->subdimension_id]->dimension->nombre ?? null;
                    $pool = isset($dimNombre, $poolsPorDimension[$dimNombre])
                        ? $poolsPorDimension[$dimNombre]
                        : $poolDefault;

                    $respuestasBatch[] = [
                        'encuesta_id'         => $encuesta->id,
                        'pregunta_id'         => $pregunta->id,
                        'opcion_respuesta_id' => fake()->randomElement($pool),
                        'created_at'          => $now,
                        'updated_at'          => $now,
                    ];
                }

                // Respuestas abiertas — 70% con texto, 30% null
                foreach ($preguntasAbiertasIds as $preguntaAbiertaId) {
                    $texto = fake()->boolean(70)
                        ? fake()->sentence(fake()->numberBetween(8, 25))
                        : null;

                    $respuestasAbiertasBatch[] = [
                        'encuesta_id'         => $encuesta->id,
                        'pregunta_abierta_id' => $preguntaAbiertaId,
                        'texto'               => $texto,
                        'created_at'          => $now,
                        'updated_at'          => $now,
                    ];
                }
            }

            // ----------------------------------------------------------------
            // 6. Loop: 7 tokens EN RIESGO (estado asignado, > 14 días)
            // ----------------------------------------------------------------
            for ($i = 0; $i < 7; $i++) {
                // fecha_asignacion debe ser > 14 días atrás para activar scopeEnRiesgo
                $createdAt       = now()->subDays(fake()->numberBetween(15, 30));
                $fechaAsignacion = (clone $createdAt)->addMinutes(fake()->numberBetween(2, 360));

                Encuesta::create([
                    'token'            => Str::uuid()->toString(),
                    'empresa_id'       => $empresa->id,
                    'estado'           => 'asignado',
                    'fecha_asignacion' => $fechaAsignacion,
                    'fecha_completada' => null,
                    'created_at'       => $createdAt,
                    'updated_at'       => $now,
                ]);
                // Sin DatoDemografico, sin respuestas, sin respuestas abiertas
            }

            // ----------------------------------------------------------------
            // 7. Insert masivo FUERA del loop
            // ----------------------------------------------------------------
            DB::table('respuestas')->insert($respuestasBatch);
            DB::table('respuestas_abiertas')->insert($respuestasAbiertasBatch);

            // ----------------------------------------------------------------
            // 8. Resumen
            // ----------------------------------------------------------------
            $this->command->info('✓ 93 encuestas completadas creadas');
            $this->command->info('✓ 7 tokens en riesgo creados');
            $this->command->info('✓ ' . count($respuestasBatch) . ' respuestas insertadas');
            $this->command->info('✓ ' . count($respuestasAbiertasBatch) . ' respuestas abiertas insertadas');
        });
    }
}
