<script setup lang="ts">
    import { computed } from 'vue';

    import QuantityControl from '@/Components/UI/QuantityControl.vue';
    import { useCartStore } from '@/stores/cart';
    import type { Product } from '@/types';

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

    // Проверяем, в корзине ли именно этот вариант этого продукта
    const isInCart = computed(() => {
        if (!props.product.default_variant) return false;
        return cart.items.some((item) => item.variant_id === props.product.default_variant?.id);
    });

    const isOutOfStock = computed(() => {
        return props.product.default_variant ? props.product.default_variant.stock <= 0 : true;
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
        return 'В корзину';
    });

    const isDisabled = computed(() => {
        if (props.disabled || !props.product.default_variant) return true;
        if (action.value === 'cart' && isOutOfStock.value) return true;
        return false;
    });

    const handleClick = () => {
        if (isDisabled.value || !props.product.default_variant) return;

        cart.add(props.product.default_variant, props.product);
    };
</script>

<template>
    <div class="w-full">
        <!-- STEP CONTROL -->
        <QuantityControl
            v-if="isInCart && props.product.default_variant"
            :item="props.product.default_variant"
        />

        <!-- BUTTON -->
        <button
            v-else
            @click.stop="handleClick"
            :disabled="isDisabled"
            class="flex w-full items-center justify-center rounded-xl p-2 text-lg font-bold transition-all duration-300 active:scale-95"
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
