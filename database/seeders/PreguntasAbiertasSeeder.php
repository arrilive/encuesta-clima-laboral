<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PreguntasAbiertasSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('preguntas_abiertas')->insertOrIgnore([
            [
                'texto'             => 'Si pudieras cambiar algo acerca de tu empresa para hacerla un mejor lugar para trabajar, ¿qué cambiarías?',
                'orden'             => 1,
                'limite_caracteres' => 500,
            ],
            [
                'texto'             => '¿Existe algo especial o único en tu empresa que la caracterice como un gran lugar para trabajar?',
                'orden'             => 2,
                'limite_caracteres' => 500,
            ],
            [
                'texto'             => '¿A quién reconocerías como embajador/a de la cultura laboral de la empresa?',
                'orden'             => 3,
                'limite_caracteres' => 300,
            ],
        ]);
    }
}
