import { Media, Unit } from './';

export interface ProductVariant {
    id: number;
    product_id: number;

    name: string;

    price_rub: number;
    old_price_rub: number | null;

    stock: number;

    is_default: boolean;
    position: number;

    attributes: Record<string, string> | null;

    is_active: boolean;
}

export interface ProductVariantWithUnit extends ProductVariant {
    unit: Unit;
}

export interface ProductVariantDTO extends ProductVariant {
    id: number;
    product_id: number;

    name: string;

    price: number;
    stock: number;

    unit: {
        slug: 'kg' | 'g' | 'l' | 'ml' | 'pcs';
    };

    amount: number;

    product: {
        name: string;
        slug: string;
    };

    media?: Media[];
}
