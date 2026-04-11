export interface Unit {
    id: number;
    name: string; // килограмм
    code: string; // kg
    position: number;
}

export interface UnitAdmin extends Unit {
    created_at: string;
    updated_at: string;
}
