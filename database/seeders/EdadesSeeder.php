<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EdadesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('edades')->insert([
            ['opcion' => '18 a 20 años',    'orden' => 1],
            ['opcion' => '21 a 25 años',    'orden' => 2],
            ['opcion' => '26 a 34 años',    'orden' => 3],
            ['opcion' => '35 a 44 años',    'orden' => 4],
            ['opcion' => '45 años o más',   'orden' => 5],
        ]);
    }
}
