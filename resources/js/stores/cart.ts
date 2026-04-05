import { computed, ref } from 'vue';

import { defineStore } from 'pinia';

import type { Product } from '@/types';

export const useCartStore = defineStore(
    'cart',
    () => {
        const items = ref<
            Array<{
                id: number;
                name: string;
                price: number;
                image: string | null;
                quantity: number;
            }>
        >([]);

        // Getters
        const totalItems = computed(() =>
            items.value.reduce((acc, item) => acc + item.quantity, 0),
        );
        const totalPrice = computed(() =>
            items.value.reduce((acc, item) => acc + item.price * item.quantity, 0),
        );

        // Actions
        function addToCart(product: Product) {
            const existing = items.value.find((i) => i.id === product.id);
            if (existing) {
                existing.quantity++;
            } else {
                items.value.push({
                    id: product.id,
                    name: product.name,
                    price: product.price_rub,
                    image: product.media?.[0]?.url || null, // Take the first photo from the resource
                    quantity: 1,
                });
            }
        }

        return { items, totalItems, totalPrice, addToCart };
    },
    {
        persist: true, // The cart will be saved in localStorage
    },
);
