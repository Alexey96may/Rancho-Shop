export interface Category {
    id: number;
    name: string;
    slug: string;
    icon: string | null;
}

export interface AdminCategory extends Category {
    sort_order: number;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}
