<script setup lang="ts">
    import { computed } from 'vue';

    import { useCartStore } from '@/stores/cart';
    import type { CartItem, ProductVariantDTO } from '@/types';

    const props = defineProps<{
        item: ProductVariantDTO | CartItem;
    }>();

    const cart = useCartStore();

    const variantId = computed(() => {
        return 'variant_id' in props.item ? props.item.variant_id : props.item.id;
    });

    const cartItem = computed(() => cart.items.find((i) => i.variant_id === variantId.value));

    const quantity = computed(() => cartItem.value?.quantity ?? 0);

    const step = computed(() => {
        switch (props.item.unit?.slug) {
            case 'kg':
            case 'l':
                return 0.5;
            case 'g':
            case 'ml':
                return 50;
            case 'pcs':
            case 'ten':
            case 'jar':
            case 'head':
            case 'pack':
            case 'tray':
            case 'mesh':
            default:
                return 1;
        }
    });

    const increase = () => {
        cart.increment(variantId.value, step.value);
    };

    const decrease = () => {
        cart.decrement(variantId.value, step.value);
    };
</script>

<template>
    <div
        class="flex w-full items-center justify-between rounded-xl bg-slate-900 p-2 text-lg text-white"
    >
        <button @click.stop="decrease" class="px-2 text-lg transition-transform active:scale-90">
            −
        </button>

        <span class="text-lg font-bold"> {{ quantity }} {{ item.unit?.short }} </span>

        <button @click.stop="increase" class="px-2 text-lg transition-transform active:scale-90">
            +
        </button>
    </div>
</template>
