// Когда будешь писать логику применения промокода в контроллере Laravel, тебе нужно будет проверять четыре условия:
//is_active === true
//expires_at (если не null) еще не наступил.
//used_count < usage_limit (если лимит установлен).
//total_price >= min_order_amount.

export type PromoType = 'fixed' | 'percent';
export type PromoCodeStatusValue = 'active' | 'expired' | 'depleted' | 'inactive';

export interface PromoCodeStatus {
    value: PromoCodeStatusValue;
    label: string;
    color: 'green' | 'red' | 'orange' | 'slate' | 'gray';
}

export interface PromoCode {
    code: string;
    type: PromoType;
    symbol: string;
    value: number;
    min_order_amount: number;
    max_discount: number | null;
}

export interface AdminPromoCode extends PromoCode {
    id: number;
    description: string;
    type_label: string;
    usage_limit: number | null;
    used_count: number;
    expires_at: string | null;
    is_active: boolean;
    is_valid: boolean;
    status: PromoCodeStatus;
    created_at: string;
    usage_percent: number;
}
