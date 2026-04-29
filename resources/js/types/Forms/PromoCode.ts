export interface PromoCodeFormState {
    code: string;
    description: string;
    type: 'fixed' | 'percent';
    value: number;
    min_order_amount: number | null;
    max_discount: number | null;
    usage_limit: number | null;
    expires_at: string | Date | null;
    is_active: boolean;
    create_another: boolean;
}
