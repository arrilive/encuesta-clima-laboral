<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LugaresTrabajoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('lugares_trabajo')->insert([
            ['opcion' => 'Corporativo', 'orden' => 1],
            ['opcion' => 'Sucursal',    'orden' => 2],
        ]);
    }
}
