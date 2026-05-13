import type {
    Animal,
    Category,
    Comment,
    Media,
    ProductVariant,
    ProductVariantDTO,
    SeoData,
    Unit,
} from './';

export type AvailabilityType = 'stock' | 'daily' | 'weekly' | 'preorder';

export interface Availability {
    value: AvailabilityType;
    label: string;
}

// проследить!
export interface ProductSchedule {
    days?: number[]; // [1, 3, 5] — Пн, Ср, Пт
    time?: string; // "08:00"
    limit_per_day?: number;
}

export interface ProductAttributes {
    fat_content?: string;
    shelf_life?: string;
    storage_temp?: string;
    [key: string]: any;
}

export interface Product {
    id: number;
    category_id: number;

    name: string;
    slug: string;
    description: string | null;

    availability_type: Availability;
    schedule: ProductSchedule;
    next_delivery_date: string | null; // "2024-05-20"

    attributes: ProductAttributes;

    // with())
    main_photo: Media[];
    gallery: Media[];
    variants?: ProductVariantDTO[];
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
