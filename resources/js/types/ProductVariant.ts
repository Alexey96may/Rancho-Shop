import { Unit } from './';

export interface ProductVariant {
    id: number;
    product_id: number;

    name: string;

    price_rub: number;
    old_price_rub: number | null;
    unit_id: number;

    stock: number;

    attributes: Record<string, string> | null;

    is_active: boolean;
}

export interface ProductVariantWithUnit extends ProductVariant {
    unit: Unit;
}
