import type { BaseUnit } from '@/types';

import type { Media } from './Media';

export type CartItemReason = 'not_found' | 'not_active' | 'out_of_stock' | 'quantity_exceeded';

export type ServerCartItem = {
    variant_id: number;
    valid: boolean;
    price: number;
    stock: number;
    reason?: CartItemReason | null;
};

export interface CartItem {
    variant_id: number;

    name: string;
    variant_name: string;

    price: number;
    quantity: number;

    media: Media;
    unit: BaseUnit;

    product_id?: number;
    slug: string; // Чтобы из корзины можно было перейти обратно на товар
    stock: number; // Чтобы не дать добавить больше, чем есть в наличии

    valid: boolean;
    reason?: CartItemReason | null;
}
