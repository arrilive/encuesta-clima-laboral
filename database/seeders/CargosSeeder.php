<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CargosSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('cargos')->insert([
            ['opcion' => 'Director',            'orden' => 1],
            ['opcion' => 'Gerente o subgerente','orden' => 2],
            ['opcion' => 'Jefe de área',        'orden' => 3],
            ['opcion' => 'Administrativo',      'orden' => 4],
            ['opcion' => 'Operativo',           'orden' => 5],
        ]);
    }
}
