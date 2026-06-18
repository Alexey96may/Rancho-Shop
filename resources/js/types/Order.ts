import { BaseUnit, DeliveryDraft, Media, PromoCode, User } from './';

export type OrderStatus = 'new' | 'confirmed' | 'delivering' | 'completed' | 'cancelled';
export type OrderStatusLabel = 'Новый' | 'Подтвержден' | 'Доставка' | 'Завершен' | 'Отменен';

export interface Order {
    id: number;
    // Customer
    customer_name: string;
    customer_phone: string;
    customer_comment: string | null;
    // Delivery
    is_pickup: boolean;
    delivery_address: string | null;
    delivery_lat: number | null;
    delivery_lng: number | null;
    delivery_validated: boolean;
    // Financial
    discount_total: number;
    total_price: number;
    delivery_price: number;

    status: OrderStatus;
    status_label: OrderStatusLabel;

    created_at: string;
    updated_at: string;

    // Items relation
    items?: OrderItem[];
    user?: User | null;
    promo_code?: PromoCode | null;
}

export interface AdminOrder extends Order {
    admin_note: string | null;
    delivery_meta: DeliveryDraft | null;
}

export interface OrderItem {
    id: number;
    order_id: number;
    product_id: number;
    product_variant_id: number;
    product_name: string;
    product_slug: string | null;

    unit_price: number;
    old_unit_price: number | null;

    quantity: number;

    images: Media[];

    unit: BaseUnit;
    subtotal: number;

    created_at: string;
    updated_at: string;
}
