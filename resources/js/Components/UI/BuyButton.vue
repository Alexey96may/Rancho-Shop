<script setup lang="ts">
    import { computed } from 'vue';

    import { useCartStore } from '@/stores/cart';
    import type { Product } from '@/types';

    import QuantityControl from './QuantityControl.vue';

    interface Props {
        product: Product;
        classes?: string;
        disabled?: boolean;
    }

    const props = withDefaults(defineProps<Props>(), {
        classes: '',
        disabled: false,
    });

    const cart = useCartStore();

    // Вычисляем дефолтный вариант прямо из продукта
    const defaultVariant = computed(() => {
        return (
            props.product.variants?.find((v) => v.is_default) ?? props.product.variants?.[0] ?? null
        );
    });

    // Проверяем, в корзине ли именно этот вариант этого продукта
    const isInCart = computed(() => {
        if (!defaultVariant.value) return false;
        return cart.items.some((item) => item.variant_id === defaultVariant.value?.id);
    });

    const isOutOfStock = computed(() => {
        return defaultVariant.value ? defaultVariant.value.stock <= 0 : true;
    });

    const action = computed<'cart' | 'preorder'>(() => {
        if (props.product.availability.value === 'stock') return 'cart';
        return 'preorder';
    });

    const buttonText = computed(() => {
        if (isOutOfStock.value && action.value === 'cart') {
            return 'Нет в наличии';
        }

        if (action.value === 'preorder') {
            return 'Предзаказ';
        }

        const priceFormatted = defaultVariant.value
            ? (defaultVariant.value.price / 100).toFixed(2)
            : '0.00';
        return `В корзину — ${priceFormatted}₽`;
    });

    const isDisabled = computed(() => {
        if (props.disabled || !defaultVariant.value) return true;
        if (action.value === 'cart' && isOutOfStock.value) return true;
        return false;
    });

    const handleClick = () => {
        if (isDisabled.value || !defaultVariant.value) return;

        // Передаем в стор вариант И сам продукт, чтобы стор взял имя и картинку продукта
        cart.add(defaultVariant.value, props.product);
    };
</script>

<template>
    <div class="w-full">
        <!-- STEP CONTROL -->
        <QuantityControl v-if="isInCart && defaultVariant" :variant="defaultVariant" />

        <!-- BUTTON -->
        <button
            v-else
            @click.stop="handleClick"
            :disabled="isDisabled"
            class="flex w-full items-center justify-center rounded-2xl py-5 text-xl font-bold transition-all duration-300 active:scale-95"
            :class="[
                !isDisabled
                    ? 'shadow-lg bg-slate-900 text-white hover:bg-orange-600'
                    : 'cursor-not-allowed bg-slate-200 text-slate-400',
                'focus:outline-none focus-visible:ring-4 focus-visible:ring-orange-500/20',
                classes,
            ]"
        >
            {{ buttonText }}
        </button>
    </div>
</template>
