<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GradosAcademicosSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('grados_academicos')->insertOrIgnore([
            ['opcion' => 'Preparatoria trunca',             'orden' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['opcion' => 'Preparatoria / carrera técnica',  'orden' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['opcion' => 'Licenciatura trunca',             'orden' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['opcion' => 'Licenciatura / Ingeniería',       'orden' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['opcion' => 'Post grado',                      'orden' => 5, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
