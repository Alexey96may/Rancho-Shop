<?php

namespace Database\Seeders;

use App\Models\Animal;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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

        $products = [
            [
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

            $product->addMedia(public_path('images/seeds/milk.png'))
                ->preservingOriginal()
                ->toMediaCollection('gallery');
        }
    }
}
