<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\User>
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

            // ✅ production-safe password reuse
            'password' => static::$password ??= Hash::make('password'),

            'remember_token' => Str::random(10),

            // 🔥 OPTIONAL (recommended fields)
            'is_admin' => false,
            'avatar' => fake()->imageUrl(200, 200),
        ];
    }

    /**
     * Mark email as unverified
     */
    public function unverified(): static
    {
        return $this->state(fn() => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Create admin user
     */
    public function admin(): static
    {
        return $this->state(fn() => [
            'is_admin' => true,
        ]);
    }
}
