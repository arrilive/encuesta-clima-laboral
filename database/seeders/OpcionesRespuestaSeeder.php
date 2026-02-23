<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class OpcionesRespuestaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('opciones_respuesta')->insertOrIgnore([
            [
                'opcion' => 'Falso',
                'valor_numerico' => 1,
                'orden' => 1,
            ],
            [
                'opcion' => 'A veces falso/a veces verdadero',
                'valor_numerico' => 2,
                'orden' => 2,
            ],
            [
                'opcion' => 'Verdadero',
                'valor_numerico' => 3,
                'orden' => 3,
            ],
            [
                'opcion' => 'Prefiero no responder',
                'valor_numerico' => 0,
                'orden' => 4,
            ],
        ]);
    }
}