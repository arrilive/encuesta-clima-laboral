<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SexosSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('sexos')->insertOrIgnore([
            ['opcion' => 'Mujer',  'orden' => 1],
            ['opcion' => 'Hombre', 'orden' => 2],
        ]);
    }
}
