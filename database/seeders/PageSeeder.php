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
                'slug' => 'home',
                'type' => 'home',
                'content' => '<h1>Натуральные продукты из самого сердца Крыма</h1><p>От фермы до вашего стола за 24 часа.</p>',
                'is_active' => true,
            ],
            [
                'title' => 'О нашей ферме',
                'slug' => 'about',
                'type' => 'system',
                'template' => 'about',
                'content' => '<h2>История Молочной Долины</h2><p>Мы начинали с одной коровы Зорьки в 2015 году...</p>',
                'is_active' => true,
            ],
            [
                'title' => 'Доставка и оплата',
                'slug' => 'delivery',
                'type' => 'system',
                'template' => 'delivery',
                'content' => '<h3>Условия доставки</h3><ul><li>Симферополь: ежедневно</li><li>Севастополь: Вт, Пт</li></ul>',
                'is_active' => true,
            ],
            [
                'title' => 'Наши животные',
                'slug' => 'animals',
                'type' => 'system',
                'template' => 'animal_list',
                'content' => '<h1>Познакомьтесь с нашими обитателями</h1>',
                'is_active' => true,
            ],
            [
                'title' => 'Контакты',
                'slug' => 'contacts',
                'type' => 'contacts',
                'content' => '<p>Адрес: Белогорский район, село Ароматное.</p>',
                'is_active' => true,
            ],
            [
                'title' => 'Политика конфиденциальности',
                'slug' => 'privacy',
                'type' => 'default',
                'content' => '<h1>Политика обработки данных</h1><p>Мы защищаем ваши данные...</p>',
                'is_active' => true,
            ],
        ];

        foreach ($pages as $page) {
            Page::updateOrCreate(
                ['slug' => $page['slug']], 
                $page
            );
        }
    }
}
