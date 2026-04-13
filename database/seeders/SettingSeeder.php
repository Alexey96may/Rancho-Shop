<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // General settings
            ['key' => 'site_name', 'value' => 'Молочная Долина', 'type' => 'string'],
            ['key' => 'site_description', 'value' => 'Натуральные молочные продукты прямиком с нашей фермы в Крыму.', 'type' => 'string'],
            ['key' => 'contact_phone', 'value' => '+7 (978) 000-00-00', 'type' => 'string'],
            ['key' => 'contact_email', 'value' => 'info@moloko-dolina.ru', 'type' => 'string'],
            ['key' => 'contact_telegram', 'value' => 'https://t.me/Aleksei_Farm', 'type' => 'string'],
            ['key' => 'contact_vk', 'value' => 'https://vk.com/moloko_dolina', 'type' => 'string'],
            ['key' => 'address_farm', 'value' => 'с. Доброе, ул. Центральная, 15', 'type' => 'string'],
            ['key' => 'farm_coords', 'value' => '44.8621, 34.2154', 'type' => 'string'],

            // --- Statuses ---
            ['key' => 'shop_status', 'value' => 'open', 'type' => 'string'], // open | closed | maintenance
            ['key' => 'is_accepting_orders', 'value' => '1', 'type' => 'boolean'],

            // --- Delivery logic ---
            ['key' => 'delivery_schedule', 'value' => 'Вт, Пт, Вс: 09:00 - 14:00', 'type' => 'string'],
            ['key' => 'delivery_info', 'value' => 'Доставка по Симферополю бесплатная при заказе от 1500₽.', 'type' => 'string'],

            ['key' => 'delivery_zones', 'value' => json_encode([
                [
                    'name' => 'Основной маршрут',

                    // (polyline)
                    'path' => [
                        [44.8621, 34.2154],
                        [44.9520, 34.1020],
                        [44.9480, 34.0900],
                    ],

                    // corridor width (meters)
                    'radius' => 700,

                    'delivery_price' => 20000,
                    'free_from' => 150000,

                    'enabled' => true,
                    'priority' => 1,
                    'max_distance' => 20000, // for the future
                ],
            ]), 'type' => 'json'],

            // --- Economy (stored in kopecks) ---
            ['key' => 'min_order_amount', 'value' => '50000', 'type' => 'integer'],

            // --- Interface limits (Pagination & UI) ---
            ['key' => 'products_per_page', 'value' => '12', 'type' => 'integer'],
            ['key' => 'cows_per_page', 'value' => '8', 'type' => 'integer'],
            ['key' => 'featured_cows_limit', 'value' => '4', 'type' => 'integer'],
            ['key' => 'featured_products_limit', 'value' => '6', 'type' => 'integer'],
            ['key' => 'featured_comments_limit', 'value' => '6', 'type' => 'integer'],

            // --- Marketing ---
            ['key' => 'header_announcement', 'value' => '🐄 Свежий удой ожидается в этот четверг! Успейте заказать.', 'type' => 'string'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value'], 'type' => $setting['type']]
            );
        }
    }
}
