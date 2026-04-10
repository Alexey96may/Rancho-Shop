import type { Media } from './Media';

export interface CartItem {
    product_id: number;
    name: string;
    price: number; // Актуальная цена в копейках
    quantity: number; // Кол-во
    media: Media;
    unit: string; // 'литр', 'кг'

    slug: string; // Чтобы из корзины можно было перейти обратно на товар
    stock: number; // Чтобы не дать добавить больше, чем есть в наличии

    valid: boolean;
    reason?: string | null;
}
