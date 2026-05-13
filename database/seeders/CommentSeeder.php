<?php

namespace Database\Seeders;

use App\Models\Animal;
use App\Models\Comment;
use App\Models\Product;
use App\Models\Page;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedSpecificReviews();

        Product::all()->each(function ($product) {
            Comment::factory()
                ->count(rand(2, 8))
                ->for($product, 'commentable')
                ->create();
        });

        Animal::all()->each(function ($animal) {
            Comment::factory()
                ->count(rand(1, 5))
                ->for($animal, 'commentable')
                ->create();
        });

        Comment::factory()
            ->count(10)
            ->negative()
            ->for(Product::inRandomOrder()->first(), 'commentable')
            ->create();
    }

    private function seedSpecificReviews(): void
    {
        $cow = Animal::where('slug', 'zorka')->first();
        if ($cow) {
            Comment::create([
                'user_id' => User::first()->id,
                'content' => 'Зорька — душа этой фермы! Молоко просто волшебное.',
                'rating' => 5,
                'status' => 'approved',
                'commentable_id' => $cow->id,
                'commentable_type' => 'animal',
            ]);
        }
    }
}
