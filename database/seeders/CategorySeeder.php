<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    use WithoutModelEvents;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Молоко', 'icon' => 'Milk'],
            ['name' => 'Сыры', 'icon' => 'Cheese'],
            ['name' => 'Сметана', 'icon' => 'Beef'], // Lucide Beef 
            ['name' => 'Яйца', 'icon' => 'Egg'],
            ['name' => 'Мёд', 'icon' => 'Hop'],
            ['name' => 'Масло', 'icon' => 'Container'],
        ];

        foreach ($categories as $index => $cat) {
            Category::create([
                'name' => $cat['name'],
                'slug' => Str::slug($cat['name']),
                'icon' => $cat['icon'],
                'type' => 'product',
                'sort_order' => $index,
                'is_active' => true,
            ]);
        }

        $animalCategories = [
            ['name' => 'Коровы', 'icon' => 'Cow'],
            ['name' => 'Козы', 'icon' => 'Goat'],
            ['name' => 'Овцы', 'icon' => 'Sheep'],
            ['name' => 'Птица', 'icon' => 'Bird'],
        ];

        foreach ($animalCategories as $index => $cat) {
            Category::create([
                'name'       => $cat['name'],
                'slug'       => Str::slug($cat['name']), 
                'icon'       => $cat['icon'],
                'type'       => 'animal',
                'sort_order' => $index,
                'is_active'  => true,
            ]);
        }
    }
}
