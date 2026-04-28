export interface PromoCodeFormState {
    code: string;
    type: 'fixed' | 'percent';
    value: number;
    min_order_amount: number;
    max_discount: number | null;
    usage_limit: number | null;
    expires_at: string | Date | null;
    is_active: boolean;
}
