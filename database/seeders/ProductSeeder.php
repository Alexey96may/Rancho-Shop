<?php

namespace Database\Seeders;

use App\Models\Animal;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductVariant;
use App\Models\Unit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class ProductSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $zorka = Animal::where('slug', 'zorka')->first();
        $belka = Animal::where('slug', 'belka')->first();

        $milkCat = Category::where('type', 'product')->where('slug', 'moloko')->first();
        $cheeseCat = Category::where('type', 'product')->where('slug', 'syry')->first();
        $sourCreamCat = Category::where('type', 'product')->where('slug', 'smetana')->first();

        $kg = Unit::where('slug', 'kg')->first();
        $g = Unit::where('slug', 'g')->first();
        $l = Unit::where('slug', 'l')->first();

        $products = [
            [
                'data' => [
                    'category_id' => $milkCat?->id,
                    'animal_id' => $zorka?->id,
                    'name' => 'Молоко коровье цельное',
                    'slug' => 'moloko-korovye-tselnoe',
                    'description' => 'Парное молоко от нашей Зорьки.',
                    'availability_type' => 'daily',
                    'attributes' => ['жирность' => '4%', 'пастеризовация' => false],
                ],
                'variants' => [
                    [
                        'name' => '1 л',
                        'price' => 12000,
                        'stock' => 50,
                        'unit_id' => $l?->id,
                    ],
                    [
                        'name' => '0.5 л',
                        'price' => 7000,
                        'stock' => 30,
                        'unit_id' => $l?->id,
                    ],
                ],
            ],
            [
                'data' => [
                    'category_id' => $cheeseCat?->id,
                    'animal_id' => $belka?->id,
                    'name' => 'Сыр козий "Домашний"',
                    'slug' => 'syr-koziy-domashniy',
                    'description' => 'Мягкий молодой сыр.',
                    'availability_type' => 'preorder',
                ],
                'variants' => [
                    [
                        'name' => '1 кг',
                        'price' => 45000,
                        'stock' => 10,
                        'unit_id' => $kg?->id,
                    ],
                    [
                        'name' => '500 г',
                        'price' => 24000,
                        'stock' => 15,
                        'unit_id' => $g?->id,
                    ],
                ],
            ],
            [
                'data' => [
                    'category_id' => $sourCreamCat?->id,
                    'animal_id' => null,
                    'name' => 'Сметана фермерская',
                    'slug' => 'smetana-fermerskaya',
                    'description' => 'Густая сметана.',
                    'availability_type' => 'daily',
                ],
                'variants' => [
                    [
                        'name' => '0.5 кг',
                        'price' => 20000,
                        'stock' => 20,
                        'unit_id' => $kg?->id,
                    ],
                ],
            ],
        ];

        foreach ($products as $item) {
            $product = Product::create(array_merge($item['data'], ['is_active' => true]));

            // variants
            foreach ($item['variants'] as $variant) {
                ProductVariant::create(array_merge($variant, [
                    'product_id' => $product->id,
                ]));
            }

            // image
            $imagePath = public_path('images/seeds/milk.png');

            if (File::exists($imagePath)) {
                $product->addMedia($imagePath)
                    ->preservingOriginal()
                    ->toMediaCollection('gallery');
            }
        }
    }
}
