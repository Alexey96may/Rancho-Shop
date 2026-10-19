<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    /**
     * Генерирует телефон в маске +7 (999) 123-45-67.
     * Уникальный: проверяет, что такого нет в БД.
     */
    private function uniquePhone(): string
    {
        do {
            $phone = sprintf(
                '+7 (%03d) %03d-%02d-%02d',
                fake()->numberBetween(900, 999),
                fake()->numberBetween(100, 999),
                fake()->numberBetween(10, 99),
                fake()->numberBetween(10, 99),
            );
        } while (User::where('phone', $phone)->exists());

        return $phone;
    }

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'phone' => $this->uniquePhone(),  // <-- маска + всегда заполнен
            'role' => fake()->randomElement(UserRole::cases())->value,
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
