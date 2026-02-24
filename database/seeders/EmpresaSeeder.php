<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmpresaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('empresas')->insertOrIgnore([
            'nombre'      => 'Empresa Demo',
            'password'    => bcrypt('demo1234'),
            'activa'      => true,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }
}
