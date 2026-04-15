import { BaseUnit, Media } from './';

export interface ProductVariant {
    id: number;
    product_id: number;

    name: string;

    price: number; // копейки
    old_price: number | null;

    amount: number;
    unit: {
        slug: 'kg' | 'g' | 'l' | 'ml' | 'pcs';
    };

    stock: number;

    is_default: boolean;
    position: number;

    attributes: Record<string, string> | null;

    is_active: boolean;
}

export interface ProductVariantDTO {
    id: number;
    product_id: number;

    name: string;

    price: number;
    price_rub: number;
    old_price: number | null;
    old_price_rub: number | null;

    stock: number;

    is_default: boolean;
    position: number;

    attributes: Record<string, string> | null;

    unit: BaseUnit;

    amount: number;

    product: {
        name: string;
        slug: string;
    };

    media: Media[];
}
