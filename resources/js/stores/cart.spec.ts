import { createPinia, setActivePinia } from 'pinia';
import { beforeEach, describe, expect, it } from 'vitest';

import { Product } from '@/types';

import { useCartStore } from './cart';

describe('Cart Store', () => {
    beforeEach(() => {
        // Create a fresh instance of Pinia before each test
        setActivePinia(createPinia());
    });

    const mockProduct = {
        id: 1,
        name: 'Свежее молоко',
        price_rub: 150,
        media: [{ url: 'milk.jpg' }],
    } as Product;

    it('must add the product to the cart', () => {
        const cart = useCartStore();
        cart.add(mockProduct);

        expect(cart.items.length).toBe(1);
        expect(cart.items[0].name).toBe('Свежее молоко');
        expect(cart.items[0].quantity).toBe(1);
    });

    it('must increase the quantity if the product is already available', () => {
        const cart = useCartStore();
        cart.add(mockProduct);
        cart.add(mockProduct);

        expect(cart.items.length).toBe(1); // The number of positions does not change
        expect(cart.items[0].quantity).toBe(2); // Only quantity increases
    });

    it('must calculate totalPrice correctly', () => {
        const cart = useCartStore();
        cart.add(mockProduct); // 150
        cart.add({ ...mockProduct, id: 2, price_rub: 100 }); // +100

        expect(cart.totalPrice).toBe(250);
    });

    it('must completely empty the recycle bin', () => {
        const cart = useCartStore();
        cart.add(mockProduct);
        cart.clear();

        expect(cart.items.length).toBe(0);
        expect(cart.totalItems).toBe(0);
    });
});
