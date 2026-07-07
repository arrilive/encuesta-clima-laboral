<?php

namespace Database\Seeders;

use App\Models\Corporativo;
use App\Models\DatoDemografico;
use App\Models\Empresa;
use App\Models\Encuesta;
use App\Models\Lote;
use App\Models\OpcionRespuesta;
use App\Models\Pregunta;
use App\Models\PreguntaAbierta;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    private const EXCELENTE_ALTO = ['verdadero' => 80, 'a_veces' => 14, 'falso' => 4, 'no_responde' => 2];   // score ≈ 88.8

    private const EXCELENTE_MEDIO = ['verdadero' => 72, 'a_veces' => 20, 'falso' => 5, 'no_responde' => 3];  // score ≈ 84.5

    private const BUEN_CLIMA = ['verdadero' => 55, 'a_veces' => 30, 'falso' => 10, 'no_responde' => 5];       // score ≈ 73.7

    private const ATENCION = ['verdadero' => 35, 'a_veces' => 35, 'falso' => 22, 'no_responde' => 8];          // score ≈ 57.1

    private const RIESGO = ['verdadero' => 24, 'a_veces' => 28, 'falso' => 38, 'no_responde' => 10];           // score ≈ 42.2

    public function run(): void
    {
        // Sembrar faker para aleatoriedad determinista y estable para las respuestas de los tests
        fake()->seed(1234);

        DB::transaction(function () {
            // ----------------------------------------------------------------
            // 1. Limpieza Previa Idempotente
            // ----------------------------------------------------------------
            DB::table('respuestas')->delete();
            DB::table('respuestas_abiertas')->delete();
            DB::table('datos_demograficos')->delete();
            DB::table('encuestas')->delete();

            if (Schema::hasTable('otp_verificaciones')) {
                DB::table('otp_verificaciones')->delete();
            }
            if (Schema::hasTable('encuesta_hashes_usados')) {
                DB::table('encuesta_hashes_usados')->delete();
            }

            DB::table('lotes')->delete();
            DB::table('sucursales')->delete();
            DB::table('empresas')->delete();
            DB::table('corporativos')->delete();
            DB::table('users')->where('role', '!=', \App\Enums\Role::SUPER_ADMIN->value)->delete();

            // ----------------------------------------------------------------
            // 2. Setup de catálogos y pools demográficos (Cargar en memoria)
            // ----------------------------------------------------------------
            $preguntas = Pregunta::all();
            $preguntasAbiertasIds = PreguntaAbierta::pluck('id');
            $opcionesRespuesta = OpcionRespuesta::all();

            $sexosIds = DB::table('sexos')->pluck('id')->toArray();
            $cargosIds = DB::table('cargos')->pluck('id')->toArray();
            $edadesIds = DB::table('edades')->orderBy('orden')->pluck('id')->toArray();
            $gradosIds = DB::table('grados_academicos')->orderBy('orden')->pluck('id')->toArray();
            $antiguedadesIds = DB::table('antiguedades')->orderBy('orden')->pluck('id')->toArray();
            $lugaresIds = DB::table('lugares_trabajo')->pluck('id')->toArray();

            // Mapeo de opciones a categorías de peso
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

            // Pools demográficos ponderados
            $sexosPool = [];
            foreach ($sexosIds as $idx => $id) {
                $pct = ($idx === 0) ? 60 : 40;
                for ($i = 0; $i < $pct; $i++) {
                    $sexosPool[] = $id;
                }
            }

            $antiguedadPool = [];
            $antiguedadPcts = [20, 30, 25, 15, 10];
            foreach ($antiguedadesIds as $idx => $id) {
                $pct = $antiguedadPcts[$idx] ?? 10;
                for ($i = 0; $i < $pct; $i++) {
                    $antiguedadPool[] = $id;
                }
            }

            $edadPool = [];
            $edadPcts = [10, 20, 30, 25, 15];
            foreach ($edadesIds as $idx => $id) {
                $pct = $edadPcts[$idx] ?? 20;
                for ($i = 0; $i < $pct; $i++) {
                    $edadPool[] = $id;
                }
            }

            $gradoPool = [];
            $gradoPcts = [5, 25, 15, 40, 15];
            foreach ($gradosIds as $idx => $id) {
                $pct = $gradoPcts[$idx] ?? 20;
                for ($i = 0; $i < $pct; $i++) {
                    $gradoPool[] = $id;
                }
            }

            // ----------------------------------------------------------------
            // 3. Crear Jerarquía de Negocio
            // ----------------------------------------------------------------
            $corporativo = Corporativo::create([
                'nombre' => 'Monterrey Industrial',
                'activa' => true,
            ]);

            // Empresas
            $grupoAltamira = Empresa::create([
                'corporativo_id' => $corporativo->id,
                'nombre' => 'Grupo Altamira',
                'password' => 'demo1234',
                'activa' => true,
            ]);

            $manufacturasNoreste = Empresa::create([
                'corporativo_id' => $corporativo->id,
                'nombre' => 'Manufacturas Noreste',
                'password' => 'demo1234',
                'activa' => true,
            ]);

            $distribuidoraRegio = Empresa::create([
                'corporativo_id' => $corporativo->id,
                'nombre' => 'Distribuidora Regio',
                'password' => 'demo1234',
                'activa' => true,
            ]);

            // Sucursales
            $plantaApodaca = Sucursal::create([
                'empresa_id' => $manufacturasNoreste->id,
                'nombre' => 'Planta Apodaca',
                'password' => 'demo1234',
                'activa' => true,
            ]);

            $sucursalNorte = Sucursal::create([
                'empresa_id' => $distribuidoraRegio->id,
                'nombre' => 'Sucursal Norte',
                'password' => 'demo1234',
                'activa' => true,
            ]);

            $sucursalCentro = Sucursal::create([
                'empresa_id' => $distribuidoraRegio->id,
                'nombre' => 'Sucursal Centro',
                'password' => 'demo1234',
                'activa' => true,
            ]);

            $sucursalSur = Sucursal::create([
                'empresa_id' => $distribuidoraRegio->id,
                'nombre' => 'Sucursal Sur',
                'password' => 'demo1234',
                'activa' => true,
            ]);

            // ----------------------------------------------------------------
            // 4. Crear Usuarios Administradores
            // ----------------------------------------------------------------
            User::create([
                'name' => 'Admin Corporativo Monterrey Industrial',
                'email' => 'admin@monterreyindustrial.demo',
                'password' => Hash::make('demo1234'),
                'role' => \App\Enums\Role::ADMIN_CORPORATIVO->value,
                'corporativo_id' => $corporativo->id,
            ]);

            User::create([
                'name' => 'Admin Grupo Altamira',
                'email' => 'admin@grupoaltamira.demo',
                'password' => Hash::make('demo1234'),
                'role' => \App\Enums\Role::ADMIN_EMPRESA->value,
                'empresa_id' => $grupoAltamira->id,
            ]);

            User::create([
                'name' => 'Admin Manufacturas Noreste',
                'email' => 'admin@manufacturasnoreste.demo',
                'password' => Hash::make('demo1234'),
                'role' => \App\Enums\Role::ADMIN_EMPRESA->value,
                'empresa_id' => $manufacturasNoreste->id,
            ]);

            User::create([
                'name' => 'Admin Distribuidora Regio',
                'email' => 'admin@distribuidoraregio.demo',
                'password' => Hash::make('demo1234'),
                'role' => \App\Enums\Role::ADMIN_EMPRESA->value,
                'empresa_id' => $distribuidoraRegio->id,
            ]);

            User::create([
                'name' => 'Admin Planta Apodaca',
                'email' => 'admin@plantaapodaca.demo',
                'password' => Hash::make('demo1234'),
                'role' => \App\Enums\Role::ADMIN_SUCURSAL->value,
                'empresa_id' => $manufacturasNoreste->id,
                'sucursal_id' => $plantaApodaca->id,
            ]);

            User::create([
                'name' => 'Admin Sucursal Norte',
                'email' => 'admin@sucursalnorte.demo',
                'password' => Hash::make('demo1234'),
                'role' => \App\Enums\Role::ADMIN_SUCURSAL->value,
                'empresa_id' => $distribuidoraRegio->id,
                'sucursal_id' => $sucursalNorte->id,
            ]);

            User::create([
                'name' => 'Admin Sucursal Centro',
                'email' => 'admin@sucursalcentro.demo',
                'password' => Hash::make('demo1234'),
                'role' => \App\Enums\Role::ADMIN_SUCURSAL->value,
                'empresa_id' => $distribuidoraRegio->id,
                'sucursal_id' => $sucursalCentro->id,
            ]);

            User::create([
                'name' => 'Admin Sucursal Sur',
                'email' => 'admin@sucursalsur.demo',
                'password' => Hash::make('demo1234'),
                'role' => \App\Enums\Role::ADMIN_SUCURSAL->value,
                'empresa_id' => $distribuidoraRegio->id,
                'sucursal_id' => $sucursalSur->id,
            ]);

            // ----------------------------------------------------------------
            // 5. Configuración de Lotes y Población de Datos
            // ----------------------------------------------------------------
            $lotesConfig = [
                [
                    'nombre' => 'Lote General',
                    'empresa_id' => $grupoAltamira->id,
                    'sucursal_id' => null,
                    'user_id' => User::where('email', 'admin@grupoaltamira.demo')->first()->id,
                    'completadas' => 60,
                    'en_riesgo' => 3,
                    'perfil' => self::BUEN_CLIMA,
                    'nombre_entidad' => 'Grupo Altamira',
                    'score_esperado' => '~74%',
                ],
                [
                    'nombre' => 'Lote General',
                    'empresa_id' => $manufacturasNoreste->id,
                    'sucursal_id' => null,
                    'user_id' => User::where('email', 'admin@manufacturasnoreste.demo')->first()->id,
                    'completadas' => 30,
                    'en_riesgo' => 3,
                    'perfil' => self::ATENCION,
                    'nombre_entidad' => 'Manufacturas Noreste (General)',
                    'score_esperado' => '~57%',
                ],
                [
                    'nombre' => 'Lote Planta Apodaca',
                    'empresa_id' => $manufacturasNoreste->id,
                    'sucursal_id' => $plantaApodaca->id,
                    'user_id' => User::where('email', 'admin@plantaapodaca.demo')->first()->id,
                    'completadas' => 45,
                    'en_riesgo' => 3,
                    'perfil' => self::EXCELENTE_MEDIO,
                    'nombre_entidad' => 'Planta Apodaca',
                    'score_esperado' => '~85%',
                ],
                [
                    'nombre' => 'Lote Sucursal Norte',
                    'empresa_id' => $distribuidoraRegio->id,
                    'sucursal_id' => $sucursalNorte->id,
                    'user_id' => User::where('email', 'admin@sucursalnorte.demo')->first()->id,
                    'completadas' => 20,
                    'en_riesgo' => 2,
                    'perfil' => self::EXCELENTE_ALTO,
                    'nombre_entidad' => 'Sucursal Norte',
                    'score_esperado' => '~89%',
                ],
                [
                    'nombre' => 'Lote Sucursal Centro',
                    'empresa_id' => $distribuidoraRegio->id,
                    'sucursal_id' => $sucursalCentro->id,
                    'user_id' => User::where('email', 'admin@sucursalcentro.demo')->first()->id,
                    'completadas' => 45,
                    'en_riesgo' => 3,
                    'perfil' => self::BUEN_CLIMA,
                    'nombre_entidad' => 'Sucursal Centro',
                    'score_esperado' => '~74%',
                ],
                [
                    'nombre' => 'Lote Sucursal Sur',
                    'empresa_id' => $distribuidoraRegio->id,
                    'sucursal_id' => $sucursalSur->id,
                    'user_id' => User::where('email', 'admin@sucursalsur.demo')->first()->id,
                    'completadas' => 90,
                    'en_riesgo' => 3,
                    'perfil' => self::RIESGO,
                    'nombre_entidad' => 'Sucursal Sur',
                    'score_esperado' => '~42%',
                ],
            ];

            $respuestasBatch = [];
            $respuestasAbiertasBatch = [];
            $totalRiesgoCreados = 0;
            $resumenReporte = [];

            foreach ($lotesConfig as $cfg) {
                // Tokens en riesgo fijos y definidos para consistencia multiplataforma
                $enRiesgo = $cfg['en_riesgo'];
                $totalRiesgoCreados += $enRiesgo;
                $tokensTotal = $cfg['completadas'] + $enRiesgo;

                $lote = Lote::create([
                    'empresa_id' => $cfg['empresa_id'],
                    'sucursal_id' => $cfg['sucursal_id'],
                    'user_id' => $cfg['user_id'],
                    'tokens_total' => $tokensTotal,
                    'nombre' => $cfg['nombre'],
                    'fecha_inicio' => now()->subDays(60)->toDateString(),
                    'fecha_fin' => now()->addDays(30)->toDateString(),
                    'activo' => true,
                ]);

                // Generar pool de opciones ponderadas para esta entidad
                $poolOpciones = $this->generarPoolOpciones($cfg['perfil'], $categoriaDeOpcion);

                // Crear encuestas completadas
                for ($i = 0; $i < $cfg['completadas']; $i++) {
                    $createdAt = now()->subDays(fake()->numberBetween(8, 59));
                    $fechaAsignacion = (clone $createdAt)->addMinutes(fake()->numberBetween(2, 360));
                    $fechaCompletada = (clone $fechaAsignacion)->addMinutes(fake()->numberBetween(15, 7200));

                    $encuesta = Encuesta::create([
                        'token' => 'TK-'.Str::upper(Str::random(4)).'-'.Str::upper(Str::random(4)),
                        'lote_id' => $lote->id,
                        'estado' => 'completado',
                        'fecha_asignacion' => $fechaAsignacion,
                        'fecha_completada' => $fechaCompletada,
                        'created_at' => $createdAt,
                        'updated_at' => now(),
                    ]);

                    // Dato Demográfico
                    DatoDemografico::create([
                        'encuesta_id' => $encuesta->id,
                        'sexo_id' => fake()->randomElement($sexosPool),
                        'antiguedad_id' => fake()->randomElement($antiguedadPool),
                        'edad_id' => fake()->randomElement($edadPool),
                        'cargo_id' => fake()->randomElement($cargosIds),
                        'grado_academico_id' => fake()->randomElement($gradoPool),
                        'lugar_trabajo_id' => fake()->randomElement($lugaresIds),
                    ]);

                    // Respuestas cerradas
                    foreach ($preguntas as $pregunta) {
                        $respuestasBatch[] = [
                            'encuesta_id' => $encuesta->id,
                            'pregunta_id' => $pregunta->id,
                            'opcion_respuesta_id' => fake()->randomElement($poolOpciones),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }

                    // Respuestas abiertas
                    foreach ($preguntasAbiertasIds as $preguntaAbiertaId) {
                        $texto = fake()->boolean(70)
                            ? fake()->sentence(fake()->numberBetween(8, 25))
                            : null;

                        $respuestasAbiertasBatch[] = [
                            'encuesta_id' => $encuesta->id,
                            'pregunta_abierta_id' => $preguntaAbiertaId,
                            'texto' => $texto,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }

                // Crear tokens en riesgo (>14 días asignados)
                for ($i = 0; $i < $enRiesgo; $i++) {
                    $createdAt = now()->subDays(fake()->numberBetween(15, 30));
                    $fechaAsignacion = (clone $createdAt)->addMinutes(fake()->numberBetween(2, 360));

                    Encuesta::create([
                        'token' => 'TK-'.Str::upper(Str::random(4)).'-'.Str::upper(Str::random(4)),
                        'lote_id' => $lote->id,
                        'estado' => 'asignado',
                        'fecha_asignacion' => $fechaAsignacion,
                        'fecha_completada' => null,
                        'created_at' => $createdAt,
                        'updated_at' => now(),
                    ]);
                }

                $resumenReporte[] = [
                    'entidad' => $cfg['nombre_entidad'],
                    'completadas' => $cfg['completadas'],
                    'en_riesgo' => $enRiesgo,
                    'score_esperado' => $cfg['score_esperado'],
                ];
            }

            // Inserciones masivas en batches seguros
            collect($respuestasBatch)->chunk(2000)->each(fn ($chunk) => DB::table('respuestas')->insert($chunk->toArray()));
            collect($respuestasAbiertasBatch)->chunk(500)->each(fn ($chunk) => DB::table('respuestas_abiertas')->insert($chunk->toArray()));

            // ----------------------------------------------------------------
            // 6. Impresión del Reporte al Consola
            // ----------------------------------------------------------------
            $this->command->info('================================================================');
            $this->command->info('✓ DemoSeeder ejecutado con éxito (Monterrey Industrial)');
            $this->command->info('================================================================');
            foreach ($resumenReporte as $r) {
                $this->command->info(sprintf(
                    '%-30s | Completadas: %3d | En Riesgo: %d | Score esperado: %s',
                    $r['entidad'],
                    $r['completadas'],
                    $r['en_riesgo'],
                    $r['score_esperado']
                ));
            }
            $this->command->info('----------------------------------------------------------------');

            // Verificación real de la consolidación de promedios (Issue A)
            $scoring = app(\App\Services\ClimaScoringService::class);
            $promedios = $scoring->promediosGeneralesPorEmpresas([$distribuidoraRegio->id]);
            $scoreReal = $promedios->get($distribuidoraRegio->id);

            $this->command->info('Distribuidora Regio — consolidado REAL calculado: '.($scoreReal !== null ? $scoreReal.'%' : 'NULL'));
            $this->command->info(sprintf('Total encuestas completadas creadas: %d', 290));
            $this->command->info(sprintf('Total tokens en riesgo creados: %d', $totalRiesgoCreados));
            $this->command->info('================================================================');
        });
    }

    private function generarPoolOpciones(array $perfil, array $categoriaDeOpcion): array
    {
        $pool = [];
        foreach ($categoriaDeOpcion as $opcionId => $categoria) {
            $repeticiones = $perfil[$categoria] ?? 0;
            for ($i = 0; $i < $repeticiones; $i++) {
                $pool[] = $opcionId;
            }
        }

        return $pool;
    }
}
