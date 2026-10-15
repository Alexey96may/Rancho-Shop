import type { Animal, Attribute, Category, Media, ProductVariantDTO, SeoData, Unit } from './';

export type AvailabilityType = 'stock' | 'daily' | 'weekly' | 'preorder';

export interface Availability {
    value: AvailabilityType;
    label: string;
}

export type ProductAttribute = Attribute;

export interface ProductSchedule {
    next_delivery: string | null; // YYYY-MM-DD
    interval_days: number | null; // 1..365
    note: string | null; // «Каждую вторую среду»
}

export interface Product {
    id: number;
    category_id: number;

    name: string;
    slug: string;
    description: string | null;
    is_commentable: boolean;

    availability: Availability;

    attributes: ProductAttribute[];
    schedule: ProductSchedule;
    next_delivery_date: string | null;

    // with())
    main_photo: Media[];
    gallery: Media[];
    variants?: ProductVariantDTO[];
    default_variant?: ProductVariantDTO | null;
    category?: Category;
    animals?: Animal[];
    unit?: Unit | null;
    seo?: SeoData | null;
}

export interface AdminProduct extends Product {
    is_active: boolean;

    created_at: string;
    updated_at: string;
    deleted_at: string | null;

    variants_count?: number; //  whenCounted
    comments_count?: number; //  whenCounted

    is_trashed: boolean;
    can_delete: boolean;

    rating_avg?: number | string;
}
