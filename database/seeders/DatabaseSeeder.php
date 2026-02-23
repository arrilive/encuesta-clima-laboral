<?php
namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\OpcionesRespuestaSeeder;
use Database\Seeders\DimensionesSeeder;
use Database\Seeders\SubdimensionesSeeder;
use Database\Seeders\PreguntasSeeder;
use Database\Seeders\PreguntasAbiertasSeeder;
use Database\Seeders\AntiguedadesSeeder;
use Database\Seeders\CargosSeeder;
use Database\Seeders\EdadesSeeder;
use Database\Seeders\GradosAcademicosSeeder;
use Database\Seeders\LugaresTrabajoSeeder;
use Database\Seeders\SexosSeeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            OpcionesRespuestaSeeder::class,
            DimensionesSeeder::class,
            SubdimensionesSeeder::class,
            PreguntasSeeder::class,
            PreguntasAbiertasSeeder::class,
            AntiguedadesSeeder::class,
            CargosSeeder::class,
            EdadesSeeder::class,
            GradosAcademicosSeeder::class,
            LugaresTrabajoSeeder::class,
            SexosSeeder::class,
        ]);
    }
}