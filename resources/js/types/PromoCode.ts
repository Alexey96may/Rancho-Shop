// Когда будешь писать логику применения промокода в контроллере Laravel, тебе нужно будет проверять четыре условия:
//is_active === true
//expires_at (если не null) еще не наступил.
//used_count < usage_limit (если лимит установлен).
//total_price >= min_order_amount.

export type PromoType = "fixed" | "percent";

export interface PromoCode {
    id: number;
    code: string;
    type: PromoType;
    value: number; // 50000 (500 руб) или 10 (10%)
    min_order_amount: number;
}

export interface AdminPromoCode extends PromoCode {
    max_discount?: number;
    usage_limit?: number;
    used_count: number;
    expires_at?: string;
    is_active: boolean;
    created_at: string;
}
