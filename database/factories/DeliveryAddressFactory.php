<?php

namespace Database\Factories;

use App\Models\DeliveryAddress;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeliveryAddress>
 */
class DeliveryAddressFactory extends Factory
{
    protected $model = DeliveryAddress::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'address' => fake()->address(),
            'lat' => fake()->latitude(44, 45),
            'lng' => fake()->longitude(33, 35),
            'is_default' => false,
            'label' => fake()->randomElement(['Home', 'Office', null]),
        ];
    }
}
