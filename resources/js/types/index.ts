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

export interface DeliveryInfo {
    farm_coords: string;
    delivery_cost: number;
    free_delivery_from: number;
    address_farm: string;
}
