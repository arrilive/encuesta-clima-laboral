<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AntiguedadesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('antiguedades')->insert([
            ['opcion' => '2 años o menos',  'orden' => 1],
            ['opcion' => '3 a 5 años',      'orden' => 2],
            ['opcion' => '6 a 10 años',     'orden' => 3],
            ['opcion' => '11 a 15 años',    'orden' => 4],
            ['opcion' => 'Más de 16 años',  'orden' => 5],
        ]);
    }
}
