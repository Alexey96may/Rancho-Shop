<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pages = [
            [
                'title' => 'Главная',
                'slug' => 'main',
                'type' => 'static',
                'content' => '<h1>Добро пожаловать в Молочную Долину!</h1>',
                'is_active' => true,
            ],
            [
                'title' => 'О нашей ферме',
                'slug' => 'about',
                'type' => 'static',
                'content' => '<h1>Добро пожаловать в нашу ферму!</h1><p>Мы производим самые свежие продукты в Крыму...</p>',
                'is_active' => true,
            ],
            [
                'title' => 'Доставка и оплата',
                'slug' => 'delivery',
                'type' => 'static',
                'content' => '<p>Мы доставляем продукты по вторникам и пятницам.</p>',
                'is_active' => true,
            ],
            [
                'title' => 'Контакты',
                'slug' => 'contacts',
                'type' => 'static',
                'content' => '<p>Связаться с нами можно через Telegram или по телефону.</p>',
                'is_active' => true,
            ],
        ];

        foreach ($pages as $page) {
            Page::updateOrCreate(['slug' => $page['slug']], $page);
        }
    }
}
