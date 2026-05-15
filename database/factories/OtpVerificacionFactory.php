<?php

namespace Database\Factories;

use App\Models\Empresa;
use App\Models\Lote;
use App\Models\OtpVerificacion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OtpVerificacion>
 */
class OtpVerificacionFactory extends Factory
{
    protected $model = OtpVerificacion::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'numero_e164' => '+52'.fake()->numerify('##########'),
            'otp_hash' => hash('sha256', fake()->numerify('######')),
            'lote_id' => Lote::factory(),
            'empresa_id' => Empresa::factory(),
            'intentos' => 0,
            'expira_en' => now()->addMinutes(10),
        ];
    }

    /**
     * Estado: OTP ya expirado (expira_en en el pasado).
     */
    public function expirada(): static
    {
        return $this->state(fn (array $attributes) => [
            'expira_en' => now()->subMinutes(5),
        ]);
    }

    /**
     * Estado: intentos agotados (>= 3).
     */
    public function agotada(): static
    {
        return $this->state(fn (array $attributes) => [
            'intentos' => 3,
        ]);
    }
}
