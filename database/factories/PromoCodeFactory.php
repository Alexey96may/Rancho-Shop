<?php

namespace Database\Factories;

use App\Models\PromoCode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PromoCode>
 */
class PromoCodeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('????-####')), 
            'type' => fake()->randomElement(['percent', 'fixed']),
            'value' => fake()->randomElement([500, 1000, 1500, 2000, 5000]),
            'min_order_amount' => fake()->numberBetween(1000, 5000) * 100,
            'usage_limit' => fake()->optional(0.7)->numberBetween(10, 500),
            'used_count' => fake()->numberBetween(0, 300),
            'expires_at' => fake()->optional(0.5)->dateTimeBetween('now', '+2 months'),
            'is_active' => fake()->boolean(80),
        ];
    }
}
