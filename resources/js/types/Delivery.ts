//From Backend
export interface DeliveryInfo {
    farm_coords: string;
    delivery_cost: number;
    free_delivery_from: number;
    address_farm: string;
}

//From Frontend: use it in checkout, order table, admin panel
export interface DeliveryDraft {
    address: string;
    lat: number | null;
    lng: number | null;
    is_valid: boolean;
    distance?: number;
}

/*
1. Backend
orders.delivery_address
orders.delivery_lat
orders.delivery_lng
orders.delivery_price
orders.is_delivery_valid

2. Session / cart (Laravel)
session('delivery') or cart.delivery

3. LocalStorage (fallback)
localStorage.setItem('deliveryDraft', JSON.stringify({...}))
*/
