<?php

namespace Database\Factories;

use App\Enums\AvailabilityType;
use App\Models\Animal;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->words(3, true);

        return [
            'category_id' => Category::where('type', 'product')->inRandomOrder()->first()?->id,
            'animal_id' => $this->faker->boolean(40) ? Animal::inRandomOrder()->first()?->id : null,
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'description' => $this->faker->paragraph(),
            'attributes' => [
                ['label' => 'Вес', 'value' => '500 г'],
                ['label' => 'Жирность', 'value' => '3.2%'],
                ['label' => 'Срок хранения', 'value' => '7 дней'],
                ['label' => 'Органический', 'value' => 'Да'],
            ],
            'availability_type' => $this->faker->randomElement(AvailabilityType::cases()),
            'is_active' => true,
        ];
    }

    // Variants
    public function configure()
    {
        return $this->afterCreating(function (Product $product) {
            $units = Unit::all();

            for ($i = 0; $i < rand(1, 2); $i++) {
                $unit = $units->random();
                ProductVariant::create([
                    'product_id' => $product->id,
                    'name' => $unit->slug === 'l' ? 'Бутылка' : 'Упаковка',
                    'price' => $this->faker->numberBetween(100, 1500) * 100,
                    'stock' => $this->faker->numberBetween(5, 100),
                    'unit_id' => $unit->id,
                ]);
            }
        });
    }
}
