<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LugaresTrabajoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('lugares_trabajo')->insertOrIgnore([
            ['opcion' => 'Corporativo', 'orden' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['opcion' => 'Sucursal',    'orden' => 2, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
