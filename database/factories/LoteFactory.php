<?php

namespace Database\Factories;

use App\Models\Empresa;
use App\Models\Lote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lote>
 */
class LoteFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Lote::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'empresa_id' => Empresa::factory(),
            'user_id' => User::factory(),
            'sucursal_id' => null,
            'tokens_total' => $this->faker->numberBetween(10, 100),
            'nombre' => 'Lote '.$this->faker->word(),
            'fecha_inicio' => null,
            'fecha_fin' => null,
            'activo' => true,
        ];
    }
}
