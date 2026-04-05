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
            ['name' => 'Молоко и сливки', 'icon' => 'Milk'],
            ['name' => 'Сыры', 'icon' => 'Cheese'],
            ['name' => 'Творог и сметана', 'icon' => 'Beef'], // Lucide Beef 
            ['name' => 'Яйца', 'icon' => 'Egg'],
            ['name' => 'Мёд и сладости', 'icon' => 'Hop'],
            ['name' => 'Масло', 'icon' => 'Container'],
        ];

        foreach ($categories as $index => $cat) {
            Category::create([
                'name' => $cat['name'],
                'slug' => Str::slug($cat['name']),
                'icon' => $cat['icon'],
                'sort_order' => $index,
                'is_active' => true,
            ]);
        }
    }
}
