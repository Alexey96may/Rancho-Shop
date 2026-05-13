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

    public function run(): void
    {
        $cowCat = Category::where('slug', 'korovy')->first();
        
        $zorka = Animal::create([
            'category_id' => $cowCat?->id,
            'name' => 'Зорька',
            'type' => 'cow',
            'slug' => 'zorka',
            'is_active' => true,
            'status' => 'healthy',
            'bio' => 'Легенда фермы.',
            'features' => ['breed' => 'Голштинская', 'weight' => '580кг'],
        ]);
        $this->addMedia($zorka, 'cow.jpg');

        Animal::factory()->count(3)->create([
            'parent_id' => $zorka->id,
            'category_id' => $cowCat?->id,
            'type' => 'cow',
        ]);

        Animal::factory()->count(50)->create()->each(function ($animal) {
            $this->addMedia($animal, 'no-animal.jpg');
        });
    }

    private function addMedia(Animal $animal, string $imageName): void
    {
        $path = public_path("images/seeds/{$imageName}");
        if (File::exists($path)) {
            $animal->addMedia($path)
                ->preservingOriginal()
                ->toMediaCollection('avatars');
        }
    }
}
