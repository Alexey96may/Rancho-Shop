import { computed, ref } from 'vue';

import { defineStore } from 'pinia';

import type { CartItem, Media, Product } from '@/types';

type ServerCartItem = {
    product_id: number;
    valid: boolean;
    price: number;
    stock: number;
};

export const useCartStore = defineStore(
    'cart',
    () => {
        const items = ref<CartItem[]>([]);

        // Getters
        const totalItems = computed(() =>
            items.value.reduce((acc, item) => acc + item.quantity, 0),
        );

        const totalPrice = computed(() =>
            items.value.reduce((acc, item) => acc + item.price * item.quantity, 0),
        );

        const lastValidatedAt = ref<number | null>(null);

        // Actions
        function add(item: Product | CartItem) {
            const id = 'id' in item ? item.id : item.product_id;
            const existingItem = items.value.find((i) => i.product_id === id);

            if (existingItem) {
                if (existingItem.quantity < existingItem.stock) {
                    existingItem.quantity++;
                }
            } else {
                const product = item as Product;

                const fallbackMedia: Media = {
                    id: 0,
                    url: '/images/no-image.jpg',
                    thumbnails: { original: '/images/no-image.jpg', webp: null, avif: null },
                    previews: { original: null, webp: null, avif: null },
                    responsive: [],
                    name: 'placeholder',
                    mime_type: 'image/jpeg',
                    order_column: 0,
                };

                items.value.push({
                    product_id: product.id,
                    name: product.name,
                    price: product.price_rub,
                    media: product.media?.[0] || fallbackMedia,
                    quantity: 1,
                    unit: product.unit || 'шт.',
                    slug: product.slug,
                    stock: product.stock,
                    valid: true,
                });
            }
        }

        // Removing one unit or the entire product
        function remove(productId: number) {
            const index = items.value.findIndex((i) => i.product_id === productId);
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
            items.value = items.value.filter((i) => i.product_id !== productId);
        }

        // Clearing the entire cart (after ordering)
        function clear() {
            items.value = [];
        }

        // Validate the cart
        async function validate(force = false) {
            const now = Date.now();

            if (!force && lastValidatedAt.value && now - lastValidatedAt.value < 30000) {
                return;
            }

            if (!items.value.length) return;

            try {
                const response = await fetch('/api/cart/validate', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                    body: JSON.stringify({
                        items: items.value.map((i) => ({
                            product_id: i.product_id,
                            quantity: i.quantity,
                        })),
                    }),
                });

                const data = await response.json();

                console.log(data);

                items.value = items.value
                    .map((localItem) => {
                        const serverItem = data.items.find(
                            (i: ServerCartItem) => i.product_id === localItem.product_id,
                        );

                        return {
                            ...localItem,
                            price: serverItem?.price ?? localItem.price,
                            stock: serverItem?.stock ?? localItem.stock,
                            quantity: serverItem
                                ? Math.min(localItem.quantity, serverItem.stock)
                                : localItem.quantity,
                            valid: serverItem?.valid ?? false,
                            reason: serverItem?.reason ?? null,
                        };
                    })
                    .filter(isCartItem);

                lastValidatedAt.value = now;
            } catch (e) {
                console.error('Cart validation failed', e);
            }
        }

        function isCartItem(item: CartItem | null): item is CartItem {
            return item !== null;
        }

        return { items, totalItems, totalPrice, add, remove, destroy, clear, validate };
    },
    {
        persist: true, // The cart will be saved in localStorage
    },
);
