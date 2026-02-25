<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CargosSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('cargos')->insertOrIgnore([
            ['opcion' => 'Director',            'orden' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['opcion' => 'Gerente o subgerente','orden' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['opcion' => 'Jefe de área',        'orden' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['opcion' => 'Administrativo',      'orden' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['opcion' => 'Operativo',           'orden' => 5, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
