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
        $product = Product::where('slug', 'moloko-korovye-tselnoe')->first();
        $cow = Animal::where('slug', 'zorka')->first();
        $mainPage = Page::where('slug', 'main')->orWhere('slug', 'home')->first();

        // Product review
        if ($product) {
            Comment::create([
                'user_id' => User::inRandomOrder()->first()->id,
                'content' => 'Очень вкусное молоко, дети в восторге! Будем брать еще.',
                'rating' => 5,
                'status' => 'approved',
                'commentable_id' => $product->id,
                'commentable_type' => 'product',
            ]);
        }

        // Review of the cow (Zorka fans)
        if ($cow) {
            Comment::create([
                'user_id' => User::inRandomOrder()->first()->id,
                'content' => 'Видел эту корову на ферме — очень ухоженная и добрая.',
                'rating' => 5,
                'status' => 'approved',
                'commentable_id' => $cow->id,
                'commentable_type' => 'animal',
            ]);
        }

        if ($mainPage) {
            $siteReviews = [
                ['text' => 'Отличный сервис и всегда свежая продукция!'],
                ['text' => 'Удобно заказывать, доставка в Симферополь быстрая.'],
                ['text' => 'Лучшая молочка в Крыму, рекомендую всем знакомым.'],
                ['text' => 'Спасибо за ваш труд, всё очень натуральное и вкусное.'],
                ['text' => 'Чистое хозяйство, прозрачные процессы. Доверяю.'],
            ];

            foreach ($siteReviews as $review) {
                Comment::create([
                    'user_id' => User::inRandomOrder()->first()->id,
                    'content' => $review['text'],
                    'rating' => 5,
                    'status' => 'approved',
                    'commentable_id' => $mainPage->id,
                    'commentable_type' => 'page',
                ]);
            }
        }

        Comment::create([
            'user_id' => User::inRandomOrder()->first()->id,
            'content' => 'Тестовый отзыв, который никто не должен видеть.',
            'rating' => 1,
            'status' => 'pending',
            'commentable_id' => $product?->id ?? 1,
            'commentable_type' => 'product',
        ]);
    }
}
