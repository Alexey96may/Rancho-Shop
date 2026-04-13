export type ShopStatus = 'open' | 'closed' | 'maintenance';

export interface DeliveryZone {
    name: string;

    path: [number, number][]; // [lat, lng]

    radius: number; // meters

    delivery_price: number; // копейки
    free_from: number; // копейки

    enabled: boolean;
    priority: number;
    max_distance?: number;
}

export interface SiteSettings {
    // --- Основная информация ---
    site_name: string;
    site_description: string;

    contact_phone: string;
    contact_email: string;
    contact_telegram: string;
    contact_vk: string;

    address_farm: string;

    // координаты фермы
    farm_coords: { lat: number; lng: number };

    // --- Статус ---
    shop_status: ShopStatus;
    is_accepting_orders: boolean;

    // --- Доставка ---
    delivery_schedule: string;
    delivery_info: string;

    // ❗ НОВОЕ
    delivery_zones: DeliveryZone[];

    // --- Экономика ---
    min_order_amount: number;

    // --- UI ---
    products_per_page: number;
    cows_per_page: number;
    featured_cows_limit: number;
    featured_products_limit: number;
    featured_comments_limit: number;

    // --- Маркетинг ---
    header_announcement?: string;
}
