<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AntiguedadesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('antiguedades')->insertOrIgnore([
            ['opcion' => '2 años o menos',  'orden' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['opcion' => '3 a 5 años',      'orden' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['opcion' => '6 a 10 años',     'orden' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['opcion' => '11 a 15 años',    'orden' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['opcion' => 'Más de 16 años',  'orden' => 5, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
