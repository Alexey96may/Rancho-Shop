export type OrderStatus = 'new' | 'confirmed' | 'delivering' | 'completed' | 'cancelled';

export interface Order {
    id: number;

    customer_name: string;

    delivery_address: string | null;

    total_price: number;
    delivery_price: number;

    total_items: number;

    status: OrderStatus;

    // Связь: Order has many OrderItems
    items?: OrderItem[];

    promo_code_id?: string | null; // Какой код применили
    discount_total: number; // Сколько конкретно сэкономил клиент (в копейках) // всегда должно быть целым числом! floor() или round()

    created_at: string;
}

export interface AdminOrder extends Order {
    customer_phone: string;
    customer_comment: string | null;
    admin_note: string | null; // Твои пометки: "Вредный клиент" или "Положить подарок"

    // Полные метки времени для логов
    updated_at: string;
}

export interface OrderItem {
    id: number;
    order_id: number;
    product_id: number | null;
    product_name: string;
    price_at_purchase: number; //в копейках / финальная цена
    base_price_at_purchase: number; //в копейках / цена без скидки
    quantity: number;

    created_at: string;
    updated_at: string;

    // Virtual property (вычисляемое на фронте или в ресурсе Laravel)
    subtotal?: number;
}
