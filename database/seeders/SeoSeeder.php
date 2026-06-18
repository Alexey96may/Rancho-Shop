<?php

namespace Database\Seeders;

use App\Models\Animal;
use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use App\Models\Seo;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SeoSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedManualSeo();

        Product::all()->each(function ($product) {
            Seo::updateOrCreate(
                ['seoable_id' => $product->id, 'seoable_type' => Product::class],
                [
                    'title' => "Купить {$product->name} напрямую с фермы | Крым",
                    'description' => "Свежий продукт «{$product->name}» от Молочной Долины. " . str($product->description)->limit(120),
                    'keywords' => "натуральный продукт, фермерская еда, {$product->name}",
                    'og_data' => [
                        'title' => $product->name,
                        'type' => 'product',
                        'image' => $product->getFirstMediaUrl('gallery'),
                    ],
                ]
            );
        });

        Animal::all()->each(function ($animal) {
            Seo::updateOrCreate(
                ['seoable_id' => $animal->id, 'seoable_type' => Animal::class],
                [
                    'title' => "{$animal->name} — обитатель нашей фермы",
                    'description' => "Посмотрите на {$animal->name}. Тип: {$animal->type}. Жизнь животных в Молочной Долине.",
                    'og_data' => ['type' => 'article'],
                ]
            );
        });

        Category::all()->each(function ($category) {
            Seo::updateOrCreate(
                ['seoable_id' => $category->id, 'seoable_type' => Category::class],
                [
                    'title' => "{$category->name} — натуральные фермерские товары",
                    'description' => "Каталог товаров в категории {$category->name}. Только свежее и домашнее.",
                ]
            );
        });
    }

    private function seedManualSeo(): void
    {
        $home = Page::where('slug', 'home')->first();
        if ($home) {
            Seo::updateOrCreate(
                ['seoable_id' => $home->id, 'seoable_type' => Page::class],
                [
                    'title' => 'Молочная Долина — натуральные продукты в Крыму с доставкой',
                    'description' => 'Семейная эко-ферма. Домашнее молоко, сыры, яйца и мясо утреннего сбора. Доставка по Крыму.',
                    'keywords' => 'фермерские продукты, купить молоко крым, доставка еды на дом',
                ]
            );
        }
    }
}
