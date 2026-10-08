import type { Delivery } from './Delivery';

export enum UserRole {
    ADMIN = 'admin',
    MODERATOR = 'moderator',
    WORKER = 'worker',
    CUSTOMER = 'customer',
}

export type UserColor = 'emerald' | 'violet' | 'sky' | 'zinc';

export interface RoleInfo {
    value: UserRole;
    label: string;
    color: UserColor;
}

export interface User {
    id: number;
    name: string;
    email: string;
    role: RoleInfo;
    phone: string;
    avatar: string | null;
    is_admin: boolean;
    is_stuff: boolean;
    created_at: string;
    last_delivery_address: string | null;
    last_delivery_lat: number | null;
    last_delivery_lng: number | null;
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
