import type { BaseUnit, Media, UnitAdmin } from './';

export interface ProductVariant {
    id: number;
    product_id: number;

    name: string;

    price: number;
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
    name: string;

    price: number;
    old_price: number | null;
    stock: number;

    is_in_stock: boolean;
    is_default: boolean;
    attributes: Record<string, string> | null;

    unit?: BaseUnit;
}

export interface AdminProductVariantDTO extends ProductVariantDTO {
    product_id: number;
    unit_id: number;
    position: number;
    created_at: string; // ISO Date

    unit?: UnitAdmin;
    media?: {
        main: Media;
    };
    product?: {
        id: number;
        name: string;
        slug: string;
    };
}
