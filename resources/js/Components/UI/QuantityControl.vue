<script setup lang="ts">
    import { computed } from 'vue';

    import { useCartStore } from '@/stores/cart';
    import type { ProductVariantDTO } from '@/types';

    const props = defineProps<{
        variant: ProductVariantDTO;
    }>();

    const cart = useCartStore();

    const cartItem = computed(() => cart.items.find((i) => i.variant_id === props.variant.id));

    const quantity = computed(() => cartItem.value?.quantity ?? 0);

    const step = computed(() => {
        switch (props.variant.unit.slug) {
            case 'pcs':
                return 1;
            case 'kg':
            case 'l':
                return 0.1;
            case 'g':
            case 'ml':
                return 50;
            default:
                return 1;
        }
    });

    const increase = () => {
        cart.increment(props.variant.id, step.value);
    };

    const decrease = () => {
        cart.decrement(props.variant.id, step.value);
    };
</script>

<template>
    <div
        class="flex w-full items-center justify-between rounded-2xl bg-slate-900 px-3 py-3 text-white"
    >
        <button @click.stop="decrease" class="px-3 text-xl active:scale-90">−</button>

        <span class="text-lg font-bold">
            {{ quantity }}
            {{ variant.unit.short }}
        </span>

        <button @click.stop="increase" class="px-3 text-xl active:scale-90">+</button>
    </div>
</template>
