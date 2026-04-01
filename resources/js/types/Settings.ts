export type ShopStatus = "open" | "closed" | "maintenance";

export interface SiteSettings {
    // Основная информация
    site_name: string;
    site_description: string;
    contact_phone: string;
    contact_email: string;
    contact_telegram: string; // Ссылка на личку или бота
    contact_vk: string; // Ссылка на группу ВК
    address_farm: string; // Адрес самой фермы (для самовывоза)
    farm_coords: string;

    shop_status: ShopStatus;

    // Логика доставки (вместо работы магазина)
    delivery_schedule: string; // Например: "Пт, Вс: 08:00 - 12:00"
    delivery_info: string; // Текст: "Доставка по Симферополю бесплатная от 1000₽"

    // Экономика (в копейках)
    min_order_amount: number; // Минималка для заказа
    delivery_cost: number; // Стоимость, если не набрали на бесплатную
    free_delivery_from: number; // Порог бесплатной доставки

    // Лимиты интерфейса
    products_per_page: number;
    cows_per_page: number;
    featured_cows_limit: number; // Сколько коров показать на главной
    featured_products_limit: number; // Сколько товаров на главной

    // Маркетинг
    header_announcement?: string; // Текст в шапке: "Свежий удой ожидается в четверг!"
    is_accepting_orders: boolean; // Глобальный рубильник (вместо shop_status)
}
