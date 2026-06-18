<?php

namespace Database\Factories;

use App\Models\Animal;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Animal>
 */
class AnimalFactory extends Factory
{
    protected $model = Animal::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->firstName();

        return [
            'category_id' => Category::where('type', 'animal')->inRandomOrder()->first()?->id,
            'name'        => $name,
            'slug'        => Str::slug($name . '-' . $this->faker->unique()->numberBetween(1, 1000)),
            'is_active'   => true,
            'status'      => $this->faker->randomElement(['healthy', 'young', 'pregnant', 'sick']),
            'bio'         => $this->faker->realText(200),
            'features'    => $this->generateFeatures(),
        ];
    }

    private function generateFeatures(): array
    {
        $common = [
            'weight' => $this->faker->numberBetween(40, 600) . 'кг',
            'birth_date' => $this->faker->date(),
            'breed' => $this->faker->randomElement(['Голштинская', 'Джерсейская', 'Айширская', 'Зааненская', 'Нубийская']),
            'yield' => $this->faker->numberBetween(15, 35) . 'л/день',
            'horns' => $this->faker->boolean(),
        ];

        return $common;
    }
}
