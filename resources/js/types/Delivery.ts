//From Frontend: use it in checkout, order table, admin panel
export interface DeliveryDraft {
    address: string;
    lat: number | null;
    lng: number | null;
    is_valid: boolean;
    distance?: number;
}

export interface DeliveryZone {
    name: string;
    path: [number, number][];
    radius: number;

    delivery_price: number;
    free_from: number;

    enabled: boolean;
    priority: number;
    max_distance?: number;
}

export interface DeliveryInfo {
    farm_coords: {
        lat: number;
        lng: number;
    } | null;

    address_farm: string;

    delivery_schedule: string;
    delivery_info: string;

    delivery_zones: DeliveryZone[];
}
