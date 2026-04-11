export interface BaseUnit {
    name: string; // килограмм
    code: string; // kg
}

export interface Unit extends BaseUnit {
    id: number;
    position: number;
}

export interface UnitAdmin extends Unit {
    created_at: string;
    updated_at: string;
}
