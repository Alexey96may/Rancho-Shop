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
        // Products
        $productGroups = [
            'Молочка' => [
                ['name' => 'Молоко', 'icon' => 'Milk'],
                ['name' => 'Сыры', 'icon' => 'Pizza'],
                ['name' => 'Сметана', 'icon' => 'GlassWater'],
                ['name' => 'Сливки', 'icon' => 'Droplets'],
                ['name' => 'Творог', 'icon' => 'CircleDot'],
                ['name' => 'Йогурты', 'icon' => 'CupSoda'],
                ['name' => 'Кефир', 'icon' => 'Waves'],
                ['name' => 'Масло', 'icon' => 'Container'],
            ],
            'Мясной цех' => [
                ['name' => 'Говядина', 'icon' => 'Beef'],
                ['name' => 'Свинина', 'icon' => 'Drumstick'],
                ['name' => 'Птица', 'icon' => 'Bird'],
                ['name' => 'Колбасы', 'icon' => 'Dna'], // Схоже визуально
                ['name' => 'Паштеты', 'icon' => 'UtilityPole'],
            ],
            'Бакалея и прочее' => [
                ['name' => 'Мёд', 'icon' => 'Hop'],
                ['name' => 'Яйца', 'icon' => 'Egg'],
                ['name' => 'Варенье', 'icon' => 'Jar'],
                ['name' => 'Травы', 'icon' => 'Leaf'],
                ['name' => 'Орехи', 'icon' => 'Nut'],
                ['name' => 'Чай', 'icon' => 'Coffee'],
                ['name' => 'Хлеб', 'icon' => 'Bread'],
            ]
        ];
        
        $order = 0;

        foreach ($productGroups as $groupName => $items) {
            foreach ($items as $item) {
                Category::create([
                    'name'       => $item['name'],
                    'slug'       => Str::slug($item['name']),
                    'icon'       => $item['icon'],
                    'type'       => 'product',
                    'description'=> "Свежий продукт категории {$groupName}",
                    'sort_order' => $order++,
                    'is_active'  => true,
                ]);
            }
        }

        // Animals
        $animalGroups = [
            ['name' => 'Коровы', 'icon' => 'Cow'],
            ['name' => 'Козы', 'icon' => 'Goat'],
            ['name' => 'Овцы', 'icon' => 'Shell'],
            ['name' => 'Птицы', 'icon' => 'Bird'],
            ['name' => 'Свиньи', 'icon' => 'Dna'],
            ['name' => 'Кролики', 'icon' => 'Rabbit'],
            ['name' => 'Лошади', 'icon' => 'VenetianMask'],
            ['name' => 'Пчёлы', 'icon' => 'Bug'],
        ];

        foreach ($animalGroups as $index => $cat) {
            Category::create([
                'name'       => $cat['name'],
                'slug'       => Str::slug($cat['name']),
                'icon'       => $cat['icon'],
                'type'       => 'animal',
                'description'=> "Наши прекрасные {$cat['name']}",
                'sort_order' => $index,
                'is_active'  => true,
            ]);
        }

        // Services
        $serviceCategories = [
            ['name' => 'Экскурсии', 'icon' => 'Map'],
            ['name' => 'Мастер-классы', 'icon' => 'GraduationCap'],
            ['name' => 'Проживание', 'icon' => 'Home'],
            ['name' => 'Фотосессии', 'icon' => 'Camera'],
        ];

        foreach ($serviceCategories as $index => $cat) {
            Category::create([
                'name'       => $cat['name'],
                'slug'       => Str::slug($cat['name']),
                'icon'       => $cat['icon'],
                'type'       => 'service',
                'sort_order' => $index,
                'is_active'  => true,
            ]);
        }
    }
}
