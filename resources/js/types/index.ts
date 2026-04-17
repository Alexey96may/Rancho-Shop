export * from './Animal';
export * from './Product';
export * from './Comment';
export * from './Cart';
export * from './Settings';
export * from './Order';
export * from './PromoCode';
export * from './Seo';
export * from './Page';
export * from './Faq';
export * from './Category';
export * from './LandingBlock';
export * from './Media';
export * from './Unit';
export * from './ProductVariant';
export * from './Delivery';

export interface ResourceCollection<T> {
    data: T[];
}

export interface ResourceSingle<T> {
    data: T;
}

export interface Paginated<T> {
    data: T[];
    meta: {
        current_page: number;
        last_page: number;
    };
}

export interface FlashPayload {
    success?: string;
    error?: string;
    message?: string;
    warning?: string;
}

export type Role = 'admin' | 'moderator' | 'customer' | 'worker';
export interface User {
    id: number;
    name: string;
    email: string;
    phone: string;
    role: string;
    avatar?: string; // avatar_url
    is_admin: boolean;
    orders_count?: number;
    created_at: string;
}

export interface AuthProps {
    user: User | null;
}

export interface PageProps {
    [key: string]: unknown;
}

export interface Permission {
    key: string; // MANAGE_USERS
    value: string; // manage-users
    label: string; // Пользователи
}

export interface SharedData extends PageProps {
    auth: AuthProps;
    can: {
        manageProducts: boolean;
        manageOrders: boolean;
        manageComments: boolean;
        manageDelivery: boolean;
        manageAnimals: boolean;
        manageUsers: boolean;
        manageCategories: boolean;
        manageCatalog: boolean;
        managePages: boolean;
        managePromocodes: boolean;
        manageFaq: boolean;
        manageFeatures: boolean;
        manageSettings: boolean;
    };
    flash: {
        success: string | null;
        error: string | null;
        message: string | null;
        warning: string | null;
    };
    permissions: Permission[];
    [key: string]: any;
}
