export type CategoryType = 'product' | 'animal';

export interface Category {
    id: number;
    name: string;
    slug: string;
    icon: string | null;
    type: CategoryType;
}

export interface AdminCategory extends Category {
    sort_order: number;
    is_active: boolean;
    description: string;
    created_at: string;
    updated_at: string;
}
