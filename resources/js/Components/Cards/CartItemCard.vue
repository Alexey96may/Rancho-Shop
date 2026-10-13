<script setup lang="ts">
    import { computed } from 'vue';

    import { Link } from '@inertiajs/vue3';

    import { ExclamationTriangleIcon, TrashIcon } from '@heroicons/vue/24/outline';

    import QuantityControl from '@/Components/UI/QuantityControl.vue';
    import { useCartStore } from '@/stores/cart';
    import type { CartItem, CartItemReason } from '@/types';
    import { formatMoney } from '@/utils/format';

    const props = defineProps<{ item: CartItem }>();

    const unitPriceFormatted = computed(() => formatMoney(props.item.price));
    const totalPriceFormatted = computed(() => formatMoney(props.item.price * props.item.quantity));

    const cart = useCartStore();

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
</script>

<template>
    <div
        :class="[
            'shadow-sm relative flex flex-col gap-3 rounded-2xl border bg-white p-3 transition-all sm:gap-4 sm:p-4',
            item.valid === false
                ? 'border-rose-300 bg-rose-50/40'
                : 'border-rancho-ink-200 hover:border-rancho-ink-300 hover:shadow-md',
        ]"
    >
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <!-- ЛЕВАЯ ЧАСТЬ: картинка + инфо -->
            <div class="flex min-w-0 flex-1 flex-col gap-3 sm:flex-row sm:items-center">
                <div class="flex items-center gap-3 pr-8 sm:pr-0">
                    <div class="h-20 w-20 shrink-0">
                        <AppImage
                            :src="item.media"
                            :alt="item.name"
                            :type="'previews'"
                            class-name="rounded-xl border border-rancho-ink-200 bg-rancho-ink-50 object-cover sm:h-20 sm:w-20"
                        />
                    </div>

                    <div>
                        <Link
                            :href="route('catalog.show', item.slug)"
                            class="line-clamp-1 text-ellipsis text-sm font-bold leading-snug text-rancho-forest transition-colors hover:text-rancho-pine sm:line-clamp-1 sm:text-base"
                        >
                            {{ item.name }}
                        </Link>

                        <p class="text-rancho-ink-500 mt-0.5 text-xs sm:text-sm">
                            {{ unitPriceFormatted }} / {{ item.unit.short }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Кнопка удаления для мобильных -->
            <button
                type="button"
                @click="cart.destroy(item.variant_id)"
                class="text-rancho-ink-400 absolute right-2.5 top-2.5 flex h-8 w-8 items-center justify-center rounded-full transition-colors hover:bg-rose-50 hover:text-rose-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500/40 sm:hidden"
                title="Удалить из корзины"
                aria-label="Удалить из корзины"
            >
                <TrashIcon class="h-4 w-4" aria-hidden="true" />
            </button>

            <!-- ПРАВАЯ / НИЖНЯЯ ЧАСТЬ -->
            <div
                class="border-rancho-ink-100 flex items-center justify-between gap-3 border-t pt-2 sm:justify-end sm:gap-4 sm:border-t-0 sm:pt-0"
            >
                <!-- Блок с выбором количества -->
                <div class="w-24 shrink-0 sm:w-32">
                    <QuantityControl v-if="item" :item="item" />
                </div>

                <!-- Итоговая стоимость -->
                <div class="min-w-[70px] text-right sm:min-w-[90px]">
                    <p class="text-sm font-black text-rancho-forest sm:text-lg">
                        {{ totalPriceFormatted }}
                    </p>
                </div>

                <!-- Кнопка удаления для десктопа -->
                <button
                    type="button"
                    @click="cart.destroy(item.variant_id)"
                    class="text-rancho-ink-400 hidden h-9 w-9 items-center justify-center rounded-full transition-colors hover:bg-rose-50 hover:text-rose-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500/40 sm:flex"
                    title="Удалить из корзины"
                    aria-label="Удалить из корзины"
                >
                    <TrashIcon class="h-5 w-5" aria-hidden="true" />
                </button>
            </div>
        </div>

        <!-- Сообщение об ошибке (если невалиден) -->
        <div
            v-if="item.valid === false"
            class="text-rancho-ember mt-1.5 line-clamp-1 inline-flex max-w-full flex-1 items-center gap-1 text-ellipsis rounded-md bg-rose-100/80 p-2 text-xs font-medium"
            role="alert"
        >
            <ExclamationTriangleIcon class="h-3.5 w-3.5 shrink-0" aria-hidden="true" />
            <span class="truncate">{{ getErrorMessage(item.reason) }}</span>
        </div>
    </div>
</template>
