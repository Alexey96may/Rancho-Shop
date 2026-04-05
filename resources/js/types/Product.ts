// Совет: В будущем можно добавить в attributes или отдельным полем is_infinite (бесконечный товар).
// Если оно true, то stock игнорируется (например, для услуг или свежего удоя, который есть всегда).
import type { Category } from './Category';
import type { Media } from './Media';
import type { SeoData } from './Seo';

export type AvailabilityType = 'stock' | 'daily' | 'preorder';

export interface ProductSchedule {
    days: number[]; // [1, 3, 5] — Пн, Ср, Пт
    time?: string; // "08:00"
    limit_per_day?: number;
}

export interface Product {
    id: number;
    animal_id: number | null;
    name: string;
    slug: string;
    description: string | null;
    availability_type: AvailabilityType;
    next_delivery_date?: string | null; // "2026-04-05"
    schedule: ProductSchedule | null;
    price: number; // Текущая цена (в копейках)
    old_price: number | null; // Цена до скидки (в копейках)
    unit: string; // 'литр', 'кг', 'десяток'
    attributes: Record<string, string> | null;
    stock: number;
    media: Media[]; // Массив из MediaLibrary

    main_image?: string;

    seo?: SeoData | null;
}

export interface AdminProduct extends Product {
    is_active: boolean;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;

    animal?: {
        name: string;
        type: string;
    } | null;
}

export interface ProductWithCategory extends Product {
    category: Category;
}
