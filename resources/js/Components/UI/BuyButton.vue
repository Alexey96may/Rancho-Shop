<script setup lang="ts">
    import { computed } from 'vue';

    import { useCartStore } from '@/stores/cart';
    import type { ProductVariantDTO } from '@/types';
    import type { AvailabilityType } from '@/types';

    import QuantityControl from './QuantityControl.vue';

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

    const isInCart = computed(() =>
        cart.items.some((item) => item.variant_id === props.variant.id),
    );

    const isOutOfStock = computed(() => props.variant.stock <= 0);

    const action = computed<'cart' | 'preorder'>(() => {
        if (props.availability_type === 'stock') return 'cart';
        return 'preorder';
    });

    const buttonText = computed(() => {
        if (isOutOfStock.value && action.value === 'cart') {
            return 'Нет в наличии';
        }

        if (action.value === 'preorder') {
            return 'Предзаказ';
        }

        return `В корзину — ${props.variant.price_rub}₽`;
    });

    const isDisabled = computed(() => {
        if (props.disabled) return true;
        if (action.value === 'cart' && isOutOfStock.value) return true;
        return false;
    });

    const handleClick = () => {
        if (isDisabled.value) return;

        cart.add({
            ...props.variant,
            // quantity: 1,
        });
    };
</script>

<template>
    <div class="w-full">
        <!-- STEP CONTROL -->
        <QuantityControl v-if="isInCart" :variant="variant" />

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
