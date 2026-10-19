import { computed, ref } from 'vue';

import { defineStore } from 'pinia';

import { useNotificationsStore } from '@/stores/notifications';
import type {
    CartItem,
    DeliveryZone,
    Media,
    Product,
    ProductVariantDTO,
    ServerCartItem,
} from '@/types';
import { formatMoney } from '@/utils/format';

/** Расчёт доставки */
export interface DeliveryCalculation {
    address: string;
    lat: number | null;
    lng: number | null;
    distance: number | null;
    price: number | null;
    is_valid: boolean;
    zone: DeliveryZone | null;
    calculated_at: number | null;
}

/** Вход для setDelivery — без calculated_at */
export type DeliveryCalculationInput = Omit<DeliveryCalculation, 'calculated_at'>;

export const useCartStore = defineStore(
    'cart',
    () => {
        const items = ref<CartItem[]>([]);
        const lastValidatedAt = ref<number | null>(null);
        const delivery = ref<DeliveryCalculation | null>(null);

        const pendingDestroyIds = ref<Set<number>>(new Set());

        const notify = useNotificationsStore();

        // ======================
        // HELPERS
        // ======================
        function round(value: number): number {
            return Math.round(value * 100) / 100;
        }

        /** Округление вниз до ближайшего кратного step */
        function snapDown(value: number, step: number): number {
            if (step <= 0) return round(value);
            return round(Math.floor(value / step) * step);
        }

        /** Округление вверх до ближайшего кратного step */
        function snapUp(value: number, step: number): number {
            if (step <= 0) return round(value);
            return round(Math.ceil(value / step) * step);
        }

        function quantityFallback(slug: string, limit: number): number {
            switch (slug) {
                case 'kg':
                case 'l':
                    return limit >= 0.5 ? 0.5 : limit;
                case 'g':
                case 'ml':
                    return limit >= 50 ? 50 : limit;
                default:
                    return limit >= 1 ? 1 : limit;
            }
        }

        function getStep(variant: ProductVariantDTO): number {
            const step = Number(variant.step ?? 0);
            if (step > 0) return step;
            return quantityFallback(variant.unit?.slug ?? '', Number(variant.stock));
        }

        // ======================
        // GETTERS
        // ======================
        const totalCleanItems = computed(() => items.value.length);

        function isPendingDestroy(variantId: number): boolean {
            return pendingDestroyIds.value.has(variantId);
        }

        const totalItems = computed(() =>
            round(items.value.reduce((acc, item) => acc + item.quantity, 0)),
        );

        const totalPrice = computed(() =>
            Math.round(items.value.reduce((acc, item) => acc + item.price * item.quantity, 0)),
        );

        const totalPriceFormatted = computed(() => formatMoney(totalPrice.value));

        const hasInvalidItems = computed(() => items.value.some((i) => !i.valid));

        // ======================
        // DELIVERY GETTERS
        // ======================
        const deliveryPrice = computed<number | null>(() => {
            const calc = delivery.value;
            if (!calc || !calc.is_valid) return null;
            if (calc.price === null) return null;
            if (calc.zone?.free_from && totalPrice.value >= calc.zone.free_from) {
                return 0;
            }
            return calc.price;
        });

        const deliveryPriceFormatted = computed<string | null>(() => {
            if (deliveryPrice.value === null) return null;
            return formatMoney(deliveryPrice.value);
        });

        const isFreeDelivery = computed(
            () => deliveryPrice.value === 0 && delivery.value?.is_valid === true,
        );

        const hasDelivery = computed(() => delivery.value !== null && delivery.value.is_valid);

        const grandTotal = computed(() => totalPrice.value + (deliveryPrice.value ?? 0));

        const grandTotalFormatted = computed(() => formatMoney(grandTotal.value));

        const freeDeliveryGap = computed<number | null>(() => {
            const freeFrom = delivery.value?.zone?.free_from;
            if (!freeFrom) return null;
            const gap = freeFrom - totalPrice.value;
            return gap > 0 ? gap : null;
        });

        const freeDeliveryGapFormatted = computed<string | null>(() => {
            if (freeDeliveryGap.value === null) return null;
            return formatMoney(freeDeliveryGap.value);
        });

        function isDeliveryStale(ttlMs: number = 5 * 60 * 1000): boolean {
            const calc = delivery.value;
            if (!calc || !calc.calculated_at) return true;
            return Date.now() - calc.calculated_at > ttlMs;
        }

        // ======================
        // GROUPED ITEMS
        // ======================
        const groupedItems = computed(() => {
            return Object.values(
                items.value.reduce(
                    (acc, item) => {
                        const key = item.product_id ?? item.variant_id;
                        if (!acc[key]) acc[key] = [];
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
            const stock = Number(variant.stock ?? 0);
            if (stock <= 0) {
                notify.error(`«${product?.name ?? 'Товар'}» нет в наличии`);
                return;
            }

            const step = getStep(variant);
            const existingItem = items.value.find((i) => i.variant_id === variant.id);

            if (existingItem) {
                increment(variant.id, step);
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

            // Первое количество: шаг, но не больше остатка.
            // Если остаток меньше шага — берём остаток (единственное возможное).
            const initial = Math.min(step, round(stock));

            items.value.push({
                variant_id: variant.id,
                product_id: product?.id ?? 0,
                name: product?.name ?? 'Товар',
                variant_name: variant.name,
                price: variant.price,
                quantity: initial,
                step,
                media: product?.main_photo?.[0] || fallbackMedia,
                unit: variant.unit || { name: '', short: '', slug: '' },
                slug: product?.slug ?? '',
                stock,
                valid: true,
                reason: null,
            });

            notify.success(`«${product?.name ?? 'Товар'}» добавлен в корзину`);
        }

        function destroy(variantId: number, hasUndo: boolean = false) {
            const index = items.value.findIndex((i) => i.variant_id === variantId);
            if (index === -1) return;

            const removed = { ...items.value[index] };
            items.value.splice(index, 1);

            pendingDestroyIds.value.add(variantId);

            if (!hasUndo) {
                pendingDestroyIds.value.delete(variantId);
            } else {
                notify.withUndo(
                    `«${removed.name}» удалён из корзины`,
                    () => {
                        const at = items.value.findIndex((i) => i.variant_id === variantId);
                        const insertAt = at === -1 ? items.value.length : at;
                        items.value.splice(insertAt, 0, removed);
                        pendingDestroyIds.value.delete(variantId);
                    },
                    () => {
                        pendingDestroyIds.value.delete(variantId);
                    },
                    5000,
                );
            }
        }

        function setDelivery(payload: DeliveryCalculationInput) {
            delivery.value = { ...payload, calculated_at: Date.now() };
        }

        function clearDelivery() {
            delivery.value = null;
        }

        function clear() {
            items.value = [];
            delivery.value = null;
        }

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
                            step: Number(serverItem?.step ?? localItem.step ?? 1),
                        };
                    }

                    const stock = Number(serverItem.stock ?? 0);
                    const step = Number(serverItem.step ?? localItem.step ?? 1);

                    if (stock <= 0) {
                        return {
                            ...localItem,
                            valid: false,
                            reason: 'out_of_stock',
                            stock,
                            step,
                        };
                    }

                    // Приводим к кратному step, но не больше stock
                    const capped = Math.min(Number(localItem.quantity), stock);
                    const snapped = snapDown(capped, step);
                    const quantity = snapped > 0 ? snapped : Math.min(step, round(stock));

                    return {
                        ...localItem,
                        price: Number(serverItem.price ?? localItem.price),
                        stock,
                        step,
                        quantity,
                        valid: true,
                        reason: null,
                    };
                });

                lastValidatedAt.value = Date.now();
            } catch (e) {
                console.error('Cart validation failed', e);
            }
        }

        function increment(variantId: number, step?: number) {
            const item = items.value.find((i) => i.variant_id === variantId);
            if (!item) return;

            const actualStep = step ?? item.step ?? 1;

            // Уже максимум
            if (item.quantity >= item.stock) return;

            // Следующее кратное step
            const next = snapUp(item.quantity + actualStep, actualStep);

            if (next <= item.stock) {
                // Влезает — берём кратное
                item.quantity = next;
            } else {
                // Не влезает, но ещё есть остаток — дотягиваем до стока
                // Например: quantity = 480, step = 80, stock = 500 → next = 560 > 500
                // → quantity = 500 (весь остаток)
                item.quantity = round(item.stock);
            }
        }

        function decrement(variantId: number, step?: number, hasUndo?: boolean) {
            const item = items.value.find((i) => i.variant_id === variantId);
            if (!item) return;

            const actualStep = step ?? item.step ?? 1;

            const snapped = snapDown(item.quantity, actualStep);
            if (round(item.quantity) !== snapped) {
                // quantity некратно (например, 500) — снимаем до кратного (480)
                item.quantity = snapped;
                return;
            }

            const next = round(snapped - actualStep);
            if (next > 0) {
                item.quantity = next;
                if (next <= item.stock) {
                    item.valid = true;
                    item.reason = null;
                }
                return;
            }

            destroy(variantId, hasUndo);
        }

        return {
            items,
            delivery,
            totalItems,
            totalCleanItems,
            totalPrice,
            totalPriceFormatted,
            hasInvalidItems,
            groupedItems,
            deliveryPrice,
            deliveryPriceFormatted,
            isFreeDelivery,
            hasDelivery,
            grandTotal,
            grandTotalFormatted,
            freeDeliveryGap,
            freeDeliveryGapFormatted,
            isDeliveryStale,
            add,
            destroy,
            clear,
            clearDelivery,
            setDelivery,
            validate,
            increment,
            decrement,
            isPendingDestroy,
        };
    },
    {
        persist: {
            key: 'cart',
            pick: ['items', 'delivery'],
        },
    },
);
