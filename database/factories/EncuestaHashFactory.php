<?php

namespace Database\Factories;

use App\Models\EncuestaHash;
use App\Models\Lote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EncuestaHash>
 */
class EncuestaHashFactory extends Factory
{
    protected $model = EncuestaHash::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'phone_hash' => fake()->sha256(),
            'lote_id' => Lote::factory(),
        ];
    }
}
