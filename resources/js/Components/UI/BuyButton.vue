<script setup lang="ts">
    import { computed } from 'vue';

    import { useCartStore } from '@/stores/cart';
    import type { ProductVariantDTO } from '@/types';
    import type { AvailabilityType } from '@/types';

    interface Props {
        variant: ProductVariantDTO;
        availability_type: AvailabilityType;
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

    // stock
    const isOutOfStock = computed(() => props.variant.stock <= 0);

    // логика действия кнопки
    const action = computed<'cart' | 'preorder'>(() => {
        if (props.availability_type === 'stock') return 'cart';
        return 'preorder'; // daily + preorder
    });

    // текст кнопки
    const buttonText = computed(() => {
        if (isInCart.value) return 'В корзине';

        if (isOutOfStock.value && action.value === 'cart') {
            return 'Нет в наличии';
        }

        switch (action.value) {
            case 'cart':
                return `В корзину`;

            case 'preorder':
                if (props.availability_type === 'daily') {
                    return 'Предзаказ (сегодня нет)';
                }
                return 'Предзаказ';
        }
    });

    const isDisabled = computed(() => {
        if (props.disabled) return true;

        // в корзине блокируем кнопку (пока нет stepper)
        if (isInCart.value) return true;

        // нельзя добавить stock если нет товара
        if (action.value === 'cart' && isOutOfStock.value) return true;

        return false;
    });
</script>

<template>
    <button
        :disabled="isDisabled"
        :aria-label="buttonText"
        :aria-pressed="isInCart"
        class="flex w-full items-center justify-center gap-3 rounded-2xl py-5 text-xl font-bold transition-all duration-300 active:scale-95"
        :class="[
            !isDisabled ? 'shadow-lg bg-slate-900 text-white hover:bg-orange-600' : '',

            isInCart ? 'cursor-default bg-emerald-50 text-emerald-700' : '',

            isDisabled ? 'cursor-not-allowed bg-slate-200 text-slate-400' : '',

            'focus:outline-none focus-visible:ring-4 focus-visible:ring-orange-500/20',
            classes,
        ]"
    >
        <span>{{ buttonText }}</span>
    </button>
</template>
