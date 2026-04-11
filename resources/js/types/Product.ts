// Совет: В будущем можно добавить в attributes или отдельным полем is_infinite (бесконечный товар).
// Если оно true, то stock игнорируется (например, для услуг или свежего удоя, который есть всегда).
import type { Category, Comment, Media, ProductVariant, ProductVariantWithUnit, SeoData } from './';

export type AvailabilityType = 'stock' | 'daily' | 'preorder';

export interface ProductSchedule {
    days: number[]; // [1, 3, 5] — Пн, Ср, Пт
    time?: string; // "08:00"
    limit_per_day?: number;
}

export interface Product {
    id: number;

    animal_id: number | null;
    category_id: number | null;

    name: string;
    slug: string;
    description: string | null;

    availability_type: AvailabilityType;

    next_delivery_date?: string | null; // "2026-04-05"
    schedule: ProductSchedule | null;
    attributes: Record<string, string> | null;
    media: Media[];

    seo?: SeoData | null;
    variants: ProductVariant[] | ProductVariantWithUnit[];
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

export interface ProductWithComments extends Product {
    comments: Comment;
}
