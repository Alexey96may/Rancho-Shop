<?php

namespace Database\Seeders;

use App\Models\Animal;
use App\Models\Page;
use App\Models\Product;
use App\Models\Seo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SeoSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. SEO for the "About the Farm" page
        $aboutPage = Page::where('slug', 'about')->first();
        if ($aboutPage) {
            Seo::create([
                'seoable_id' => $aboutPage->id,
                'seoable_type' => Page::class,
                'title' => 'О нашей семейной ферме | Молочная Долина',
                'description' => 'Узнайте историю Молочной Долины: как мы заботимся о коровах и производим натуральные продукты.',
                'keywords' => 'ферма Крым, натуральное молоко, история фермы',
                'og_data' => [
                    'og:title' => 'Молочная Долина — традиции качества',
                    'og:image' => '/images/og/about-farm.jpg',
                ],
            ]);
        }

        // 2. SEO for a specific product
        $product = Product::where('slug', 'moloko-korovye-tselnoe')->first();
        if ($product) {
            Seo::create([
                'seoable_id' => $product->id,
                'seoable_type' => Product::class,
                'title' => 'Купить домашнее коровье молоко в Симферополе',
                'description' => 'Цельное коровье молоко утреннего удоя. Стеклянная тара, 100% натурально.',
                'og_data' => [
                    'og:type' => 'product',
                    'product:price:amount' => $product->price / 100,
                    'product:price:currency' => 'RUB',
                ],
            ]);
        }

        // 3. SEO for a specific animal (for example, Zorka)
        $cow = Animal::where('slug', 'zorka')->first();
        if ($cow) {
            Seo::create([
                'seoable_id' => $cow->id,
                'seoable_type' => Animal::class,
                'title' => "Корова {$cow->name} — Наша гордость | Молочная Долина",
                'description' => "Познакомьтесь с {$cow->name}. Узнайте её историю, особенности и посмотрите галерею нашей любимицы.",
                'keywords' => "корова {$cow->name}, животные фермы, домашний скот Крым",
                'og_data' => [
                    'og:title' => "Звезда нашей фермы: {$cow->name}",
                    'og:description' => "Посмотрите, как живёт {$cow->name} в Молочной Долине.",
                    'og:image' => $cow->getFirstMediaUrl('gallery') ?: '/images/og/default-animal.jpg',
                    'og:type' => 'profile',
                ],
            ]);
        }
    }
}
