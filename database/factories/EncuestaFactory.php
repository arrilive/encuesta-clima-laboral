<?php

namespace Database\Factories;

use App\Models\Encuesta;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Encuesta>
 */
class EncuestaFactory extends Factory
{
    protected $model = Encuesta::class;

    public function definition(): array
    {
        return [
            'token' => 'TK-'.Str::upper(Str::random(4)).'-'.Str::upper(Str::random(4)),
            'lote_id' => \App\Models\Lote::factory(),
            'estado' => 'disponible',
            'fecha_asignacion' => null,
            'fecha_completada' => null,
        ];
    }

    /**
     * Estado: asignada — la encuesta fue entregada a un respondiente.
     */
    public function asignada(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'asignado',
            'fecha_asignacion' => now(),
        ]);
    }

    /**
     * Estado: completada — el respondiente terminó la encuesta.
     */
    public function completada(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'completado',
            'fecha_asignacion' => now(),
            'fecha_completada' => now(),
        ]);
    }
}
