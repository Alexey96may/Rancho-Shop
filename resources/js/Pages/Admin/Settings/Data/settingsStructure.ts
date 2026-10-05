import type { Component } from 'vue';

import {
    Cog6ToothIcon,
    DevicePhoneMobileIcon,
    GlobeAltIcon,
    PresentationChartLineIcon,
    TruckIcon,
} from '@heroicons/vue/24/outline';

export interface SettingSection {
    id: string;
    label: string;
    icon: Component;
    keys: string[];
}

export const SETTINGS_SECTIONS: SettingSection[] = [
    {
        id: 'general',
        label: 'Общие',
        icon: GlobeAltIcon,
        keys: ['site_name', 'site_description', 'header_announcement'],
    },
    {
        id: 'contacts',
        label: 'Контакты',
        icon: DevicePhoneMobileIcon,
        keys: [
            'contact_phone',
            'contact_email',
            'contact_telegram',
            'contact_vk',
            'address_farm',
            'farm_coords',
        ],
    },
    {
        id: 'shop',
        label: 'Магазин',
        icon: Cog6ToothIcon,
        keys: ['shop_status', 'is_accepting_orders', 'min_order_amount'],
    },
    {
        id: 'delivery',
        label: 'Доставка',
        icon: TruckIcon,
        keys: ['delivery_schedule', 'delivery_info', 'delivery_zones'],
    },
    {
        id: 'ui',
        label: 'Интерфейс',
        icon: PresentationChartLineIcon,
        keys: [
            'admin_per_page',
            'products_per_page',
            'animals_per_page',
            'comments_per_page',
            'users_per_page',
            'orders_per_page',
            'featured_animals_limit',
            'featured_products_limit',
            'featured_comments_limit',
        ],
    },
];

export const SETTINGS_LABELS: Record<string, string> = {
    site_name: 'Название сайта',
    site_description: 'Описание (SEO)',
    header_announcement: 'Текст в шапке',
    contact_phone: 'Телефон',
    contact_email: 'Email',
    contact_telegram: 'Telegram (ссылка)',
    contact_vk: 'VK (ссылка)',
    address_farm: 'Адрес фермы',
    farm_coords: 'Координаты (Через запятую: шир., дол.)',
    shop_status: 'Статус магазина',
    is_accepting_orders: 'Прием заказов',
    min_order_amount: 'Мин. сумма заказа (коп.)',
    delivery_schedule: 'График доставки',
    delivery_info: 'Информация о доставке',
    delivery_zones: 'Зоны доставки',
    admin_per_page: 'Карточек в Админке на страницу',
    products_per_page: 'Товаров на страницу',
    animals_per_page: 'Животных на страницу',
    comments_per_page: 'Отзывов на страницу',
    users_per_page: 'Пользователей на страницу',
    orders_per_page: 'Заказов на страницу',
    featured_animals_limit: 'Лимит "Популярные животные"',
    featured_products_limit: 'Лимит "Популярные товары"',
    featured_comments_limit: 'Лимит отзывов на главной',
};
