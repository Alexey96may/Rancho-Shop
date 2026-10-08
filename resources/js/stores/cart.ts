import { computed, ref } from 'vue';

import { defineStore } from 'pinia';

import type { CartItem, Media, Product, ProductVariantDTO, ServerCartItem } from '@/types';

export const useCartStore = defineStore(
    'cart',
    () => {
        const items = ref<CartItem[]>([]);
        const lastValidatedAt = ref<number | null>(null);

        // ======================
        // GETTERS
        // ======================
        const totalCleanItems = computed(() => items.value.length);

        const totalItems = computed(() =>
            items.value.reduce((acc, item) => acc + item.quantity, 0),
        );

        const totalPrice = computed(() =>
            items.value.reduce((acc, item) => acc + item.price * item.quantity, 0),
        );

        const totalPriceFormatted = computed(() => (totalPrice.value / 100).toFixed(2));

        const hasInvalidItems = computed(() => items.value.some((i) => !i.valid));

        //grouping of variants of a single product
        const groupedItems = computed(() => {
            return Object.values(
                items.value.reduce(
                    (acc, item) => {
                        const key = item.product_id ?? item.variant_id;

                        if (!acc[key]) {
                            acc[key] = [];
                        }

                        acc[key].push(item);
                        return acc;
                    },
                    {} as Record<number, CartItem[]>,
                ),
            );
        });

        // ======================
        // ACTIONS
        // ======================
        function add(variant: ProductVariantDTO, product?: Product) {
            const existingItem = items.value.find((i) => i.variant_id === variant.id);

            if (existingItem) {
                if (existingItem.quantity >= existingItem.stock) {
                    existingItem.valid = false;
                    existingItem.reason = 'quantity_exceeded';
                    return;
                }
                existingItem.quantity = quantityFallback(variant.unit?.slug ?? '');
                return;
            }

            const fallbackMedia: Media = {
                id: 0,
                url: '/images/no-image.jpg',
                thumbnails: { original: '/images/no-image.jpg', webp: null, avif: null },
                previews: { original: null, webp: null, avif: null },
                responsive: {},
                name: 'placeholder',
                mime_type: 'image/jpeg',
                order_column: 0,
            };

            // Если передан продукт — берем имя и главную картинку от него.
            // Если нет (например, если вызывается откуда-то еще) — фоллбечимся.
            items.value.push({
                variant_id: variant.id,
                product_id: product?.id ?? 0, // если ID нет, ставим 0

                name: product?.name ?? 'Товар',
                variant_name: variant.name,

                price: variant.price,
                quantity: quantityFallback(variant.unit?.slug ?? ''),

                media: product?.main_photo?.[0] || fallbackMedia,

                unit: variant.unit || { name: '', short: '', slug: '' },
                amount: variant.amount,

                slug: product?.slug ?? '',
                stock: variant.stock,

                valid: true,
                reason: null,
            });
        }

        // Removing one unit or the entire product
        function remove(variantId: number) {
            const index = items.value.findIndex((i) => i.variant_id === variantId);

            if (index !== -1) {
                if (items.value[index].quantity > 1) {
                    items.value[index].quantity--;
                } else {
                    items.value.splice(index, 1);
                }
            }
        }

        // Complete deletion of a position (trash can)
        function destroy(variantId: number) {
            items.value = items.value.filter((i) => i.variant_id !== variantId);
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
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                    },
                    body: JSON.stringify({
                        items: items.value.map((i) => ({
                            variant_id: i.variant_id,
                            quantity: i.quantity,
                        })),
                    }),
                });

                if (!response.ok) {
                    console.error('Validation failed', response.status);
                    return;
                }

                const data: { items: ServerCartItem[] } = await response.json();

                const serverItems = data.items ?? [];

                items.value = items.value.map((localItem) => {
                    const serverItem = serverItems.find(
                        (i) => Number(i.variant_id) === Number(localItem.variant_id),
                    );

                    if (!serverItem || !serverItem.valid) {
                        return {
                            ...localItem,
                            valid: false,
                            reason: serverItem?.reason ?? 'not_found',
                        };
                    }

                    const stock = Number(serverItem.stock ?? 0);

                    return {
                        ...localItem,
                        price: Number(serverItem.price ?? localItem.price),
                        stock: stock,
                        quantity: Math.min(Number(localItem.quantity), stock),
                        valid: true,
                        reason: null,
                    };
                });

                lastValidatedAt.value = Date.now();
            } catch (e) {
                console.error('Cart validation failed', e);
            }
        }

        function increment(variantId: number, step: number = 1) {
            const item = items.value.find((i) => i.variant_id === variantId);
            if (!item) return;

            const next = item.quantity + step;

            if (next < item.stock) {
                item.quantity = round(next);
            }
        }

        function decrement(variantId: number, step: number = 1) {
            const item = items.value.find((i) => i.variant_id === variantId);

            if (!item) return;

            const next = item.quantity - step;

            if (next <= 0) {
                item.quantity = round(next);
                remove(variantId);
                return;
            }

            item.quantity = round(next);
        }

        function round(value: number) {
            return Math.round(value * 100) / 100;
        }

        function quantityFallback(slug: string) {
            switch (slug) {
                case 'kg':
                case 'l':
                    return 0.5;
                case 'g':
                case 'ml':
                    return 50;
                default:
                    return 1;
            }
        }

        return {
            items,
            totalItems,
            totalCleanItems,
            totalPrice,
            totalPriceFormatted,
            hasInvalidItems,
            groupedItems,
            add,
            remove,
            destroy,
            clear,
            validate,
            increment,
            decrement,
        };
    },
    {
        persist: true, // The cart will be saved in localStorage
    },
);
