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
                'title' => 'О ферме',
                'slug' => 'about',
                'type' => 'system',
                'template' => 'about',
                'content' => '<h2>История Молочной Долины</h2><p>...</p>',
                'is_active' => true,
                'show_in_menu' => true,
                'menu_order' => 30,
            ],
            [
                'title' => 'Доставка',
                'slug' => 'delivery',
                'type' => 'system',
                'template' => 'delivery',
                'content' => '<h3>Условия доставки</h3>...',
                'is_active' => true,
                'show_in_menu' => true,
                'menu_order' => 40,
            ],
            [
                'title' => 'Каталог',
                'slug' => 'catalog',
                'type' => 'system',
                'template' => 'default',
                'content' => null,
                'is_active' => true,
                'show_in_menu' => true,
                'menu_order' => 10,
            ],
            [
                'title' => 'Наши Животные',
                'slug' => 'animals',
                'type' => 'system',
                'template' => 'default',
                'content' => null,
                'is_active' => true,
                'show_in_menu' => true,
                'menu_order' => 20,
            ],
            [
                'title' => 'Контакты',
                'slug' => 'contacts',
                'type' => 'contacts',
                'content' => '<p>Адрес: ...</p>',
                'is_active' => true,
                'show_in_menu' => false,
                'menu_order' => 0,
            ],
            [
                'title' => 'Политика конфиденциальности',
                'slug' => 'privacy',
                'type' => 'default',
                'template' => 'default',
                'content' => '<h1>Политика обработки данных</h1><p>Мы защищаем ваши данные...</p>',
                'is_active' => true,
                'show_in_menu' => false,
                'menu_order' => 0,
            ],
            [
                'title' => 'Публичная оферта',
                'slug' => 'offer',
                'type' => 'default',
                'template' => 'default',
                'content' => '<h1>Публичная оферта</h1><p>Настоящий документ является публичной офертой...</p>',
                'is_active' => true,
                'show_in_menu' => false,
                'menu_order' => 0,
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
