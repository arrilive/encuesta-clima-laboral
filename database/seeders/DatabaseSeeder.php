<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\AntiguedadesSeeder;
use Database\Seeders\CargosSeeder;
use Database\Seeders\DimensionesSeeder;
use Database\Seeders\EdadesSeeder;
use Database\Seeders\EmpresaSeeder;
use Database\Seeders\GradosAcademicosSeeder;
use Database\Seeders\LugaresTrabajoSeeder;
use Database\Seeders\OpcionesRespuestaSeeder;
use Database\Seeders\PreguntasAbiertasSeeder;
use Database\Seeders\PreguntasSeeder;
use Database\Seeders\SexosSeeder;
use Database\Seeders\SubdimensionesSeeder;

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
            EmpresaSeeder::class,
        ]);
    }
}