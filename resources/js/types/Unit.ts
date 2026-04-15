type unitSlug = 'kg' | 'g' | 'l' | 'ml' | 'pcs';

export interface BaseUnit {
    name?: string; // килограмм
    slug: unitSlug; // kg
    short: string; // кг
}

export interface Unit extends BaseUnit {
    id: number;
    position: number;
}

export interface UnitAdmin extends Unit {
    created_at: string;
    updated_at: string;
}
