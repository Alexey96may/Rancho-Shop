<?php

namespace Database\Seeders;

use App\Models\Animal;
use App\Models\Comment;
use App\Models\Product;
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
        $product = Product::where('slug', 'moloko-korovye-tselnoe')->first();
        $cow = Animal::where('slug', 'zorka')->first();

        $randomUser = User::inRandomOrder()->first();

        // Product review
        if ($product) {
            Comment::create([
                'user_id' => $randomUser->id,
                'content' => 'Очень вкусное молоко, дети в восторге! Будем брать еще.',
                'rating' => 5,
                'is_published' => true,
                'commentable_id' => $product->id,
                'commentable_type' => Product::class,
            ]);
        }

        // Review of the cow (Zorka fans)
        if ($cow) {
            Comment::create([
                'user_id' => $randomUser->id,
                'content' => 'Видел эту корову на ферме — очень ухоженная и добрая.',
                'rating' => 5,
                'is_published' => true,
                'commentable_id' => $cow->id,
                'commentable_type' => Animal::class,
            ]);
        }

        Comment::create([
            'user_id' => $randomUser->id,
            'content' => 'Тестовый отзыв, который никто не должен видеть.',
            'rating' => 1,
            'is_published' => false,
            'commentable_id' => $product?->id ?? 1,
            'commentable_type' => Product::class,
        ]);
    }
}
