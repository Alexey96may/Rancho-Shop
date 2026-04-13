import { BaseUnit } from './';

export type OrderStatus = 'new' | 'confirmed' | 'delivering' | 'completed' | 'cancelled';

export interface Order {
    id: number;

    customer_name: string;

    /**
     * Delivery snapshot (can be null if pickup)
     */
    delivery_address: string | null;

    delivery_lat?: number | null;
    delivery_lng?: number | null;

    /**
     * Pickup flag
     */
    is_pickup?: boolean;

    /**
     * Delivery validation snapshot (zone check at order time)
     */
    delivery_validated?: boolean;

    /**
     * Delivery metadata snapshot (zone, distance, rules, etc.)
     */
    delivery_meta?: Record<string, any> | null;

    total_price: number;
    delivery_price: number;
    total_items: number;

    status: OrderStatus;

    // Items relation
    items?: OrderItem[];

    promo_code_id?: string | null;

    /**
     * Discount snapshot (stored in smallest currency unit)
     */
    discount_total: number;

    created_at: string;
}

export interface AdminOrder extends Order {
    customer_phone: string;
    customer_comment: string | null;
    admin_note: string | null;

    updated_at: string;
}

export interface OrderItem {
    id: number;
    order_id: number;
    product_variant_id: number;
    product_name: string;

    unit_price: number;
    old_unit_price: number | null;

    quantity: number;

    unit?: BaseUnit;

    created_at: string;
    updated_at: string;

    subtotal?: number;
}
