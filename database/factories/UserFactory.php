<?php

namespace Database\Factories;

use App\Models\Corporativo;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => 'admin_empresa',
            'empresa_id' => null,
            'corporativo_id' => null,
            'sucursal_id' => null,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'super_admin',
            'empresa_id' => null,
        ]);
    }

    public function adminEmpresa(int $empresaId): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin_empresa',
            'empresa_id' => $empresaId,
        ]);
    }

    public function adminCorporativo(?int $corporativoId = null): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin_corporativo',
            'corporativo_id' => $corporativoId ?? Corporativo::factory(),
            'empresa_id' => null,
            'sucursal_id' => null,
        ]);
    }

    public function adminSucursal(int $sucursalId): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin_sucursal',
            'sucursal_id' => $sucursalId,
            'empresa_id' => null,
            'corporativo_id' => null,
        ]);
    }
}
