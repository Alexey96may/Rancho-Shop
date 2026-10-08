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
            'shadow-sm relative flex flex-col gap-3 rounded-2xl border bg-white p-3 transition-all sm:flex-row sm:items-center sm:gap-4 sm:p-4',
            item.valid === false
                ? 'border-red-200 bg-red-50/30'
                : 'hover:shadow-md border-slate-100',
        ]"
    >
        <!-- Верхняя/Левая часть: Изображение + Название + Кнопка удаления (на моб.) -->
        <div class="flex items-start gap-3 sm:flex-1 sm:items-center">
            <AppImage
                :src="item.media"
                :alt="item.name"
                :type="'previews'"
                :class-name="'h-16 w-16 sm:h-20 sm:w-20 flex-shrink-0 rounded-xl bg-slate-50 object-cover border border-slate-100'"
            />

            <div class="min-w-0 flex-1 pr-6 sm:pr-0">
                <Link
                    :href="route('catalog.show', item.slug)"
                    class="line-clamp-2 text-sm font-bold leading-snug text-slate-900 hover:text-orange-600 sm:line-clamp-1 sm:text-base"
                >
                    {{ item.name }}
                </Link>

                <p class="mt-0.5 text-xs text-slate-500 sm:text-sm">
                    {{ unitPriceFormatted }} / {{ item.unit.short }}
                </p>

                <!-- Сообщение об ошибке (если невалиден) -->
                <div
                    v-if="item.valid === false"
                    class="mt-1.5 inline-flex items-center gap-1 rounded-md bg-red-100/80 px-2 py-0.5 text-xs font-medium text-red-600"
                >
                    <ExclamationTriangleIcon class="h-3.5 w-3.5 flex-shrink-0" />
                    <span class="truncate">{{ getErrorMessage(item.reason) }}</span>
                </div>
            </div>

            <!-- Кнопка удаления для мобильных (абсолютное позиционирование в углу) -->
            <button
                @click="cart.destroy(item.variant_id)"
                class="absolute right-3 top-3 text-slate-300 hover:text-red-500 sm:hidden"
                title="Удалить из корзины"
            >
                <TrashIcon class="h-5 w-5" />
            </button>
        </div>

        <!-- Нижняя/Правая часть: Количество, Цена и Удаление (на десктопе) -->
        <div
            class="flex items-center justify-between border-t border-slate-100 pt-2 sm:justify-end sm:gap-4 sm:border-t-0 sm:pt-0"
        >
            <!-- Блок с выбором количества -->
            <div class="w-28 flex-shrink-0 sm:w-32">
                <QuantityControl v-if="item" :item="item" />
            </div>

            <!-- Итоговая стоимость позиций -->
            <div class="text-right sm:min-w-[90px]">
                <p class="text-base font-black text-slate-900 sm:text-lg">
                    {{ totalPriceFormatted }}
                </p>
            </div>

            <!-- Кнопка удаления для десктопа -->
            <button
                @click="cart.destroy(item.variant_id)"
                class="hidden text-slate-300 transition-colors hover:text-red-500 sm:block"
                title="Удалить из корзины"
            >
                <TrashIcon class="h-5 w-5" />
            </button>
        </div>
    </div>
</template>
