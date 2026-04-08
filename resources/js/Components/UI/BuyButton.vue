<script setup lang="ts">
    import { computed } from 'vue';

    import { useCartStore } from '@/stores/cart';
    import type { Product } from '@/types';

    interface Props {
        product: Product;
        price?: string | number;
        classes?: string;
        disabled?: boolean;
    }

    const props = withDefaults(defineProps<Props>(), {
        classes: '',
        disabled: false,
    });

    const cart = useCartStore();

    // 1. Проверка: находится ли товар уже в Pinia
    const isInCart = computed(() => cart.items.some((item) => item.id === props.product.id));

    // 2. Логика доступности: если это 'stock', проверяем количество
    const isOutOfStock = computed(
        () => props.product.availability_type === 'stock' && props.product.stock === 0,
    );

    // 3. Динамический текст кнопки
    const buttonText = computed(() => {
        if (isInCart.value) return 'В корзине';
        if (isOutOfStock.value) return 'Нет в наличии';

        // Главное условие по типу доступности
        if (props.product.availability_type === 'preorder') {
            return 'Забронировать продукт';
        }

        // По умолчанию (stock)
        return props.price ? `Добавить в корзину — ${props.price}₽` : 'Добавить в корзину';
    });

    const handleClick = () => {
        if (!isInCart.value && !props.disabled && !isOutOfStock.value) {
            cart.add(props.product);
        }
    };
</script>

<template>
    <button
        @click.stop="handleClick"
        :disabled="disabled || isOutOfStock || isInCart"
        :aria-label="buttonText"
        :aria-pressed="isInCart"
        class="flex w-full items-center justify-center gap-3 rounded-2xl py-5 text-xl font-bold transition-all duration-300 active:scale-95"
        :class="[
            // Состояние: Доступен для покупки или брони
            !isInCart && !isOutOfStock && !disabled
                ? 'shadow-lg bg-slate-900 text-white shadow-slate-200 hover:bg-orange-600 hover:shadow-orange-200'
                : '',

            // Состояние: Уже в корзине
            isInCart
                ? 'cursor-default border border-emerald-100 bg-emerald-50 text-emerald-700'
                : '',

            // Состояние: Недоступен (закончился)
            isOutOfStock || disabled ? 'cursor-not-allowed bg-slate-200 text-slate-400' : '',

            // Аксессуарика (A11y)
            'focus:outline-none focus-visible:ring-4 focus-visible:ring-orange-500/20',

            classes,
        ]"
    >
        <span class="relative z-10">
            {{ buttonText }}
        </span>
    </button>
</template>
