export interface BaseUnit {
    name: string;
    short: string;
    slug: string;
    low_stock_threshold: number;
}

export interface Unit extends BaseUnit {
    id: number;
    position: number;
}

export interface UnitAdmin extends Unit {
    created_at: string;
    product_variants_count?: number;
    can_delete: boolean;
}

export interface UnitFilters {
    search?: string;
    sort?: 'latest' | 'position';
}
