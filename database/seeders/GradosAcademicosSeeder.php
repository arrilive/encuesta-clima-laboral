<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GradosAcademicosSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('grados_academicos')->insert([
            ['opcion' => 'Preparatoria trunca',             'orden' => 1],
            ['opcion' => 'Preparatoria / carrera técnica',  'orden' => 2],
            ['opcion' => 'Licenciatura trunca',             'orden' => 3],
            ['opcion' => 'Licenciatura / Ingeniería',       'orden' => 4],
            ['opcion' => 'Post grado',                      'orden' => 5],
        ]);
    }
}
