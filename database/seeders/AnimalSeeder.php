<?php

namespace Database\Seeders;

use App\Models\Animal;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AnimalSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mother = Animal::create([
            'name' => 'Зорька',
            'type' => 'cow',
            'slug' => 'zorka',
            'is_active' => true,
            'status' => 'healthy',
            'bio' => 'Старейшая корова нашего ранчо, дает самое жирное молоко для наших сыров.',
            'features' => [
                'breed' => 'Голштинская',
                'weight' => '580кг',
                'yield' => '25л/день',
            ],
        ]);

        $mother->addMediaFromUrl('https://images.unsplash.com/photo-1546445317-29f4545e9d53')
            ->toMediaCollection('gallery');

        $calf = Animal::create([
            'parent_id' => $mother->id,
            'name' => 'Лучик',
            'type' => 'cow',
            'slug' => 'luchik',
            'is_active' => true,
            'status' => 'young',
            'bio' => 'Первенец Зорьки. Очень любопытный и дружелюбный.',
            'features' => [
                'breed' => 'Голштинская',
                'age' => '6 месяцев',
                'gender' => 'male',
            ],
        ]);

        $animals = [
            [
                'name' => 'Белка',
                'type' => 'goat',
                'slug' => 'belka',
                'bio' => 'Зааненская коза с очень спокойным характером.',
                'features' => ['color' => 'white', 'horns' => false],
            ],
            [
                'name' => 'Марта',
                'type' => 'cow',
                'slug' => 'marta',
                'bio' => 'Любимица всей семьи.',
                'features' => ['breed' => 'Джерсейская'],
            ],
        ];

        foreach ($animals as $data) {
            Animal::create(array_merge($data, [
                'is_active' => true,
                'status' => 'healthy',
            ]));
        }
    }
}
