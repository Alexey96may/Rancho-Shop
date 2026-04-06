<?php

namespace Database\Seeders;

use App\Models\Animal;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Facades\File;

class AnimalSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
            
        $cowCat = Category::where('type', 'animal')->where('slug', 'korovy')->first();
        $goatCat = Category::where('type', 'animal')->where('slug', 'kozy')->first();

        $mother = Animal::create([
            'category_id' => $cowCat?->id,
            'name' => 'Зорька',
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

        $this->addMediaToAnimal($mother, 'cow.jpg', 'cow.mp3');

        $calf = Animal::create([
            'category_id' => $cowCat?->id,
            'parent_id' => $mother->id,
            'name' => 'Лучик',
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

        $this->addMediaToAnimal($mother, 'cow.jpg', 'cow.mp3');

        $others = [
            [
                'name' => 'Белка',
                'category_id' => $goatCat?->id,
                'slug' => 'belka',
                'bio' => 'Зааненская коза с очень спокойным характером.',
                'features' => ['color' => 'white', 'horns' => false],
            ],
            [
                'name' => 'Марта',
                'category_id' => $cowCat?->id,
                'slug' => 'marta',
                'bio' => 'Любимица всей семьи.',
                'features' => ['breed' => 'Джерсейская'],
            ],
        ];

        foreach ($others as $data) {
            $image = $data['image'] ?? 'no-animal.jpg';
            $voice = $data['voice'] ?? null;

            unset($data['image'], $data['voice']);

            $animal = Animal::create(array_merge($data, [
                'is_active' => true,
                'status' => 'healthy',
            ]));

            $this->addMediaToAnimal($animal, $image, $voice);
        }
    }

    private function addMediaToAnimal(Animal $animal, string $imageName, ?string $voiceName = null): void
    {
        $imagePath = public_path("images/seeds/{$imageName}");
        if (File::exists($imagePath)) {
            $animal->addMedia($imagePath)
                ->preservingOriginal()
                ->toMediaCollection('avatars');
        }

        if ($voiceName) {
            $voicePath = public_path("audio/seeds/{$voiceName}");
            if (File::exists($voicePath)) {
                $animal->addMedia($voicePath)
                    ->preservingOriginal()
                    ->toMediaCollection('voice');
            }
        }
    }
}
