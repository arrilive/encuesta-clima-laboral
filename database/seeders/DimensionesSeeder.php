<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DimensionesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('dimensiones')->insertOrIgnore([
            [
                'nombre' => 'Credibilidad',
                'descripcion' => 'Mide la confianza en la dirección de la empresa',
                'orden' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Respeto',
                'descripcion' => 'Mide el apoyo, valoración y colaboración hacia los colaboradores',
                'orden' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Imparcialidad',
                'descripcion' => 'Mide la equidad y justicia en el trato a los colaboradores',
                'orden' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Orgullo',
                'descripcion' => 'Mide el sentido de pertenencia y orgullo por el trabajo',
                'orden' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Compañerismo',
                'descripcion' => 'Mide la calidad de las relaciones entre colaboradores',
                'orden' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Seguridad y Capacitación',
                'descripcion' => 'Mide la seguridad física, emocional y capacitación recibida',
                'orden' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
