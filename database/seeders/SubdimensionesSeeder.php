<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubdimensionesSeeder extends Seeder
{
    public function run(): void
    {
        $credibilidad   = DB::table('dimensiones')->where('nombre', 'Credibilidad')->value('id');
        $respeto        = DB::table('dimensiones')->where('nombre', 'Respeto')->value('id');
        $imparcialidad  = DB::table('dimensiones')->where('nombre', 'Imparcialidad')->value('id');
        $orgullo        = DB::table('dimensiones')->where('nombre', 'Orgullo')->value('id');
        $compañerismo   = DB::table('dimensiones')->where('nombre', 'Compañerismo')->value('id');
        $seguridad      = DB::table('dimensiones')->where('nombre', 'Seguridad y Capacitación')->value('id');

        DB::table('subdimensiones')->insert([
            // Credibilidad
            ['dimension_id' => $credibilidad,  'nombre' => 'Comunicación',          'orden' => 1],
            ['dimension_id' => $credibilidad,  'nombre' => 'Capacidad',              'orden' => 2],
            ['dimension_id' => $credibilidad,  'nombre' => 'Integridad',             'orden' => 3],
            // Respeto
            ['dimension_id' => $respeto,       'nombre' => 'Apoyo',                  'orden' => 1],
            ['dimension_id' => $respeto,       'nombre' => 'Valoración',             'orden' => 2],
            ['dimension_id' => $respeto,       'nombre' => 'Colaboración',           'orden' => 3],
            // Imparcialidad
            ['dimension_id' => $imparcialidad, 'nombre' => 'Equidad',                'orden' => 1],
            ['dimension_id' => $imparcialidad, 'nombre' => 'Ausencia de favoritismo','orden' => 2],
            ['dimension_id' => $imparcialidad, 'nombre' => 'Justicia',               'orden' => 3],
            // Orgullo
            ['dimension_id' => $orgullo,       'nombre' => 'Del Equipo',             'orden' => 1],
            ['dimension_id' => $orgullo,       'nombre' => 'Del Trabajo',            'orden' => 2],
            ['dimension_id' => $orgullo,       'nombre' => 'De la Empresa',          'orden' => 3],
            // Compañerismo
            ['dimension_id' => $compañerismo,  'nombre' => 'Hospitalidad',           'orden' => 1],
            ['dimension_id' => $compañerismo,  'nombre' => 'Cercanía',               'orden' => 2],
            ['dimension_id' => $compañerismo,  'nombre' => 'Sentido de Familia',     'orden' => 3],
            // Seguridad y Capacitación
            ['dimension_id' => $seguridad,     'nombre' => 'Seguridad',              'orden' => 1],
            ['dimension_id' => $seguridad,     'nombre' => 'Capacitación',           'orden' => 2],
        ]);
    }
}
