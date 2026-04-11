<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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
            AdminSeeder::class,
        ]);
    }
}
