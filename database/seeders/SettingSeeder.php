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
            ['key' => 'address_farm', 'value' => 'с. Перевальное, ул. Речная, 7', 'type' => 'string'],
            ['key' => 'farm_coords', 'value' => '44.85014, 34.30457', 'type' => 'string'],

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
                        [34.30457, 44.85014],
                        [34.32541, 44.83384],
                        [34.12673, 44.94398],
                        [34.11062, 44.95797],
                        [34.09723, 44.97342],
                        [34.07753, 44.9583],
                        [34.07865, 44.95596],
                        [34.09397, 44.94407],
                        [34.10249, 44.95186],
                        [34.11062, 44.95797],
                        [34.12673, 44.94398],
                        [34.30457, 44.85014],
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
            ['key' => 'admin_per_page', 'value' => '10', 'type' => 'integer'],

            ['key' => 'products_per_page', 'value' => '12', 'type' => 'integer'],
            ['key' => 'animals_per_page', 'value' => '8', 'type' => 'integer'],
            ['key' => 'comments_per_page', 'value' => '8', 'type' => 'integer'],
            ['key' => 'orders_per_page', 'value' => '8', 'type' => 'integer'],

            ['key' => 'featured_animals_limit', 'value' => '4', 'type' => 'integer'],
            ['key' => 'featured_products_limit', 'value' => '6', 'type' => 'integer'],
            ['key' => 'featured_comments_limit', 'value' => '6', 'type' => 'integer'],

            // --- Payment (direct transfer) ---
            ['key' => 'payment_direct_phone',     'value' => '+7 (978) 000-00-0',           'type' => 'string'],
            ['key' => 'payment_direct_bank',      'value' => 'Мой Банк',                     'type' => 'string'],
            ['key' => 'payment_direct_recipient', 'value' => 'Иван И.',                   'type' => 'string'],
            ['key' => 'payment_direct_note_template', 'value' => 'Оплата заказа',            'type' => 'string'],

            // --- Marketing ---
            ['key' => 'header_announcement', 'value' => 'Свежий удой ожидается в этот четверг! Успейте заказать.', 'type' => 'string'],

            // --- AI ---
            ['key' => 'ai_bot_name', 'value' => 'ИИ Бот', 'type' => 'string'],
            ['key' => 'ai_bot_greetings', 'value' => 'Могу вам чем-нибудь помочь?', 'type' => 'string'],
            ['key' => 'ai_bot_prompt', 'value' => <<<'PROMPT'
                Ты — вежливый консультант интернет-магазина «Молочная Долина». Отвечай кратко, дружелюбно и по делу. Не придумывай того, чего нет в инструкции.

                О магазине:
                - Мы продаём натуральные молочные продукты с фермы в Крыму (с. Перевальное).
                - Телефон: +7 (978) 000-00-00
                - Telegram: https://t.me/Aleksei_Farm
                - Сайт: milkvalley.org

                Доставка:
                - График: Вт, Пт, Вс с 09:00 до 14:00.
                - По Симферополю бесплатно при заказе от 1500₽.
                - Есть основной маршрут доставки (зона покрытия — коридор 700 метров вдоль маршрута).

                Оплата:
                - Прямой перевод на карту/телефон: +7 (978) 000-00-0 (Мой Банк, получатель Иван И.).
                - В назначении платежа укажите «Оплата заказа».

                Минимальный заказ: 500₽.

                Если спрашивают про цену или наличие конкретного товара — скажи, что нужно уточнить в каталоге, и предложи посмотреть на сайте. Не выдумывай цены.
                PROMPT, 'type' => 'string',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value'], 'type' => $setting['type']]
            );
        }
    }
}
