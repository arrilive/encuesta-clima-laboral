<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmpresaSeeder extends Seeder
{
    public function run(): void
    {
        $password = env('EMPRESA_DEMO_PASSWORD')
            ?? throw new \RuntimeException('EMPRESA_DEMO_PASSWORD no está definida en .env');

        DB::table('empresas')->insertOrIgnore([
            'nombre' => 'Empresa Demo',
            'password' => bcrypt($password),
            'activa' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
