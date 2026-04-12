<script setup lang="ts">
    import { computed } from 'vue';

    import { useCartStore } from '@/stores/cart';
    import type { ProductVariantDTO } from '@/types';

    interface Props {
        variant: ProductVariantDTO;
        classes?: string;
        disabled?: boolean;
    }

    const props = withDefaults(defineProps<Props>(), {
        classes: '',
        disabled: false,
    });

    const cart = useCartStore();

    // есть ли именно этот variant в корзине
    const isInCart = computed(() =>
        cart.items.some((item) => item.variant_id === props.variant.id),
    );

    // проверка наличия
    const isOutOfStock = computed(() => props.variant.stock <= 0);

    const buttonText = computed(() => {
        if (isInCart.value) return 'В корзине';
        if (isOutOfStock.value) return 'Нет в наличии';

        return `Добавить в корзину — ${props.variant.price}₽`;
    });

    const handleClick = () => {
        if (!isInCart.value && !props.disabled && !isOutOfStock.value) {
            cart.add(props.variant);
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
            !isInCart && !isOutOfStock && !disabled
                ? 'shadow-lg bg-slate-900 text-white hover:bg-orange-600'
                : '',

            isInCart ? 'cursor-default bg-emerald-50 text-emerald-700' : '',

            isOutOfStock || disabled ? 'cursor-not-allowed bg-slate-200 text-slate-400' : '',

            'focus:outline-none focus-visible:ring-4 focus-visible:ring-orange-500/20',
            classes,
        ]"
    >
        <span>{{ buttonText }}</span>
    </button>
</template>
