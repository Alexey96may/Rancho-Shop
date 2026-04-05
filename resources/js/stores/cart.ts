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
        function add(product: Product) {
            const item = items.value.find((i) => i.id === product.id);
            if (item) {
                item.quantity++;
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

        // Removing one unit or the entire product
        function remove(productId: number) {
            const index = items.value.findIndex((i) => i.id === productId);
            if (index !== -1) {
                if (items.value[index].quantity > 1) {
                    items.value[index].quantity--;
                } else {
                    items.value.splice(index, 1);
                }
            }
        }

        // Complete deletion of a position (trash can)
        function destroy(productId: number) {
            items.value = items.value.filter((i) => i.id !== productId);
        }

        // Clearing the entire cart (after ordering)
        function clear() {
            items.value = [];
        }

        return { items, totalItems, totalPrice, add, remove, destroy, clear };
    },
    {
        persist: true, // The cart will be saved in localStorage
    },
);
