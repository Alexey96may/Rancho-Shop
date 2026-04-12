import type { Media } from './Media';

export type CartItemReason = 'not_found' | 'not_active' | 'out_of_stock' | 'quantity_exceeded';

export interface CartItem {
    variant_id: number;

    name: string;
    variant_name: string;

    price: number;
    quantity: number;

    media: Media;
    unit: 'kg' | 'g' | 'l' | 'ml' | 'pcs';
    amount: number;

    product_id?: number;
    slug: string; // Чтобы из корзины можно было перейти обратно на товар
    stock: number; // Чтобы не дать добавить больше, чем есть в наличии

    valid: boolean;
    reason?: CartItemReason | null;
}
