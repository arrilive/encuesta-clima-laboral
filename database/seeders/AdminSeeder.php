<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $password = env('ADMIN_PASSWORD')
            ?? throw new \RuntimeException('ADMIN_PASSWORD no está definida en .env');

        // Super Admin
        User::firstOrCreate(
            ['email' => env('SUPER_ADMIN_EMAIL')],
            [
                'name' => 'Super Admin',
                'password' => bcrypt($password),
                'role' => 'super_admin',
                'empresa_id' => null,
            ]
        );

        // Admin Empresa Demo
        $empresa = Empresa::where('nombre', 'Empresa Demo')->firstOrFail();

        User::firstOrCreate(
            ['email' => env('ADMIN_EMPRESA_EMAIL')],
            [
                'name' => 'Admin Empresa Demo',
                'password' => bcrypt($password),
                'role' => 'admin_empresa',
                'empresa_id' => $empresa->id,
            ]
        );
    }
}
