<?php

namespace Database\Seeders;

use App\Models\Animal;
use App\Models\Product;
use App\Models\Category;
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

        $products = [
            [
                'category_id' => $milkCat?->id,
                'animal_id' => $zorka?->id,
                'name' => 'Молоко коровье цельное',
                'slug' => 'moloko-korovye-tselnoe',
                'description' => 'Парное молоко от нашей Зорьки. Жирность 3.8-4.2%.',
                'price' => 12000, // 120.00 rub
                'unit' => 'литр',
                'stock' => 50,
                'availability_type' => 'daily',
                'attributes' => ['fat' => '4%', 'pasteurized' => false],
            ],
            [
                'category_id' => $cheeseCat?->id,
                'animal_id' => $belka?->id,
                'name' => 'Сыр козий "Домашний"',
                'slug' => 'syr-koziy-domashniy',
                'description' => 'Мягкий молодой сыр из козьего молока.',
                'price' => 45000, // 450.00 rub
                'unit' => 'кг',
                'stock' => 10,
                'availability_type' => 'on_order',
                'schedule' => ['monday', 'thursday'], // Delivery/preparation days
                'attributes' => ['aging' => '3 дня', 'salt' => 'medium'],
            ],
            [
                'category_id' => $sourCreamCat?->id,
                'animal_id' => null, // General goods
                'name' => 'Сметана фермерская',
                'slug' => 'smetana-fermerskaya',
                'description' => 'Густая сметана, в которой ложка стоит.',
                'price' => 20000,
                'unit' => '0.5 кг',
                'stock' => 20,
                'availability_type' => 'daily',
            ],
        ];

        foreach ($products as $item) {
            $product = Product::create(array_merge($item, ['is_active' => true]));

            $imagePath = public_path('images/seeds/milk.png');

            if (File::exists($imagePath)) {
                $product->addMedia($imagePath)
                    ->preservingOriginal()
                    ->toMediaCollection('gallery');
            }
        }
    }
}
