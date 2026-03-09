<?php

namespace Database\Factories;

use App\Models\Encuesta;
use App\Models\Antiguedad;
use App\Models\Edad;
use App\Models\LugarTrabajo;
use App\Models\Sexo;
use App\Models\GradoAcademico;
use App\Models\Cargo;
use Illuminate\Database\Eloquent\Factories\Factory;

class DatoDemograficoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'encuesta_id'       => Encuesta::factory(),
            'antiguedad_id'     => Antiguedad::inRandomOrder()->first()?->id ?? 1,
            'edad_id'           => Edad::inRandomOrder()->first()?->id ?? 1,
            'lugar_trabajo_id'  => LugarTrabajo::inRandomOrder()->first()?->id ?? 1,
            'sexo_id'           => Sexo::inRandomOrder()->first()?->id ?? 1,
            'grado_academico_id'=> GradoAcademico::inRandomOrder()->first()?->id ?? 1,
            'cargo_id'          => Cargo::inRandomOrder()->first()?->id ?? 1,
        ];
    }
}