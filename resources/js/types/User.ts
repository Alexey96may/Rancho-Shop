export type Role = 'admin' | 'moderator' | 'customer' | 'worker';

export interface User {
    id: number;
    name: string;
    email: string;
    phone: string;
    role: Role;
    avatar?: string;
    is_admin: boolean;
    orders_count?: number;
    created_at: string;
}
