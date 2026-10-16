<script setup lang="ts">
    import { computed } from 'vue';

    import { Link } from '@inertiajs/vue3';

    import { ExclamationTriangleIcon, TrashIcon } from '@heroicons/vue/24/outline';

    import QuantityControl from '@/Components/UI/QuantityControl.vue';
    import SwipeToDelete from '@/Components/UI/SwipeToDelete.vue';
    import { useCanHover } from '@/composables/features/useTouch';
    import { useCartStore } from '@/stores/cart';
    import type { CartItem, CartItemReason } from '@/types';
    import { formatMoney } from '@/utils/format';

    const props = defineProps<{ item: CartItem }>();

    const unitPriceFormatted = computed(() => formatMoney(props.item.price));
    const totalPriceFormatted = computed(() => formatMoney(props.item.price * props.item.quantity));

    const cart = useCartStore();
    const { canHover } = useCanHover();

    const getErrorMessage = (reason: CartItemReason | string | null | undefined) => {
        switch (reason) {
            case 'not_available':
            case 'not_found':
                return 'Товар недоступен или снят с продажи';
            case 'out_of_stock':
                return 'Товар закончился на складе';
            default:
                return 'Недоступен для заказа';
        }
    };

    const onDelete = () => {
        cart.destroy(props.item.variant_id, true);
    };
</script>

<template>
    <!-- Свайп — только на тач-устройствах -->
    <SwipeToDelete v-if="!canHover" @delete="onDelete">
        <div
            :class="[
                'shadow-sm relative flex flex-col gap-3 rounded-2xl border bg-white p-3 transition-all sm:gap-4 sm:p-4',
                item.valid === false
                    ? 'border-rose-300 bg-rose-50/40'
                    : 'hover:shadow-md border-rancho-ink-200 hover:border-rancho-ink-300',
            ]"
        >
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <!-- ЛЕВАЯ ЧАСТЬ -->
                <div class="flex min-w-0 flex-1 flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="flex items-center gap-3">
                        <div class="h-20 w-20 shrink-0">
                            <AppImage
                                :src="item.media"
                                :alt="item.name"
                                :type="'previews'"
                                class-name="rounded-xl border border-rancho-ink-200 bg-rancho-ink-50 object-cover sm:h-20 sm:w-20"
                            />
                        </div>

                        <div class="min-w-0 flex-1">
                            <Link
                                :href="route('catalog.show', item.slug)"
                                class="line-clamp-1 text-ellipsis text-sm font-bold leading-snug text-rancho-forest transition-colors hover:text-rancho-pine sm:text-base"
                            >
                                {{ item.name }}
                            </Link>

                            <p class="mt-0.5 text-xs text-rancho-ink-500 sm:text-sm">
                                {{ unitPriceFormatted }} / {{ item.unit.short }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- ПРАВАЯ / НИЖНЯЯ ЧАСТЬ -->
                <div
                    class="flex items-center justify-between gap-3 border-t border-rancho-ink-100 pt-2 sm:justify-end sm:gap-4 sm:border-t-0 sm:pt-0"
                >
                    <div class="w-24 shrink-0 sm:w-32">
                        <QuantityControl v-if="item" :has-undo="true" :item="item" />
                    </div>

                    <div class="min-w-[70px] text-right sm:min-w-[90px]">
                        <p class="text-sm font-black text-rancho-forest sm:text-lg">
                            {{ totalPriceFormatted }}
                        </p>
                    </div>
                </div>
            </div>

            <div
                v-if="item.valid === false"
                class="mt-1.5 line-clamp-1 inline-flex max-w-full flex-1 items-center gap-1 text-ellipsis rounded-md bg-rose-100/80 p-2 text-xs font-medium text-rancho-ember"
                role="alert"
            >
                <ExclamationTriangleIcon class="h-3.5 w-3.5 shrink-0" aria-hidden="true" />
                <span class="truncate">{{ getErrorMessage(item.reason) }}</span>
            </div>
        </div>
    </SwipeToDelete>

    <!-- Обычная карточка с кнопкой — на устройствах с hover -->
    <div
        v-else
        :class="[
            'shadow-sm relative flex flex-col gap-3 rounded-2xl border bg-white p-3 transition-all sm:gap-4 sm:p-4',
            item.valid === false
                ? 'border-rose-300 bg-rose-50/40'
                : 'hover:shadow-md border-rancho-ink-200 hover:border-rancho-ink-300',
        ]"
    >
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="flex min-w-0 flex-1 flex-col gap-3 sm:flex-row sm:items-center">
                <div class="flex items-center gap-3">
                    <div class="h-20 w-20 shrink-0">
                        <AppImage
                            :src="item.media"
                            :alt="item.name"
                            :type="'previews'"
                            class-name="rounded-xl border border-rancho-ink-200 bg-rancho-ink-50 object-cover sm:h-20 sm:w-20"
                        />
                    </div>

                    <div class="min-w-0 flex-1">
                        <Link
                            :href="route('catalog.show', item.slug)"
                            class="line-clamp-1 text-ellipsis text-sm font-bold leading-snug text-rancho-forest transition-colors hover:text-rancho-pine sm:text-base"
                        >
                            {{ item.name }}
                        </Link>

                        <p class="mt-0.5 text-xs text-rancho-ink-500 sm:text-sm">
                            {{ unitPriceFormatted }} / {{ item.unit.short }}
                        </p>
                    </div>
                </div>
            </div>

            <div
                class="flex items-center justify-between gap-3 border-t border-rancho-ink-100 pt-2 sm:justify-end sm:gap-4 sm:border-t-0 sm:pt-0"
            >
                <div class="w-24 shrink-0 sm:w-32">
                    <QuantityControl v-if="item" :has-undo="true" :item="item" />
                </div>

                <div class="min-w-[70px] text-right sm:min-w-[90px]">
                    <p class="text-sm font-black text-rancho-forest sm:text-lg">
                        {{ totalPriceFormatted }}
                    </p>
                </div>

                <!-- Кнопка удаления -->
                <button
                    type="button"
                    class="flex h-9 w-9 items-center justify-center rounded-full text-rancho-ink-400 transition-colors hover:bg-rose-50 hover:text-rose-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500/40"
                    title="Удалить из корзины"
                    aria-label="Удалить из корзины"
                    @click="onDelete"
                >
                    <TrashIcon class="h-5 w-5" aria-hidden="true" />
                </button>
            </div>
        </div>

        <div
            v-if="item.valid === false"
            class="mt-1.5 line-clamp-1 inline-flex max-w-full flex-1 items-center gap-1 text-ellipsis rounded-md bg-rose-100/80 p-2 text-xs font-medium text-rancho-ember"
            role="alert"
        >
            <ExclamationTriangleIcon class="h-3.5 w-3.5 shrink-0" aria-hidden="true" />
            <span class="truncate">{{ getErrorMessage(item.reason) }}</span>
        </div>
    </div>
</template>
