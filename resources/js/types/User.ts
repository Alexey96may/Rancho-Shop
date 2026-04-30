import type { Delivery } from './Delivery';

export enum UserRole {
    ADMIN = 'admin',
    MODERATOR = 'moderator',
    WORKER = 'worker',
    CUSTOMER = 'customer',
}

export interface User {
    id: number;
    name: string;
    email: string;
    role: RoleInfo;
    phone: string;
    avatar: string | null;
    is_admin: boolean;
    created_at: string;
}

export interface AdminUser extends User {
    google_id: string | null;
    vkontakte_id: string | null;

    email_verified_at: string | null;
    updated_at: string;

    orders_count?: number;
    comments_count?: number;

    is_staff: boolean;
    can_manage_orders: boolean;

    addresses?: Delivery[];
}

export interface RoleInfo {
    value: UserRole;
    label: string;
    color: string;
}
