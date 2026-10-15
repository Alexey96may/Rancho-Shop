<script setup lang="ts">
    import { computed, ref, watch } from 'vue';

    import { useCartStore } from '@/stores/cart';
    import type { CartItem, ProductVariantDTO } from '@/types';

    interface Props {
        item: ProductVariantDTO | CartItem;
        hasUndo?: boolean;
    }

    const props = withDefaults(defineProps<Props>(), {
        hasUndo: false,
    });

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
        cart.decrement(variantId.value, step.value, props.hasUndo);
    };

    const bump = ref(false);

    watch(quantity, () => {
        bump.value = false;

        requestAnimationFrame(() => {
            bump.value = true;
            setTimeout(() => (bump.value = false), 200);
        });
    });
</script>

<template>
    <div
        class="flex w-full items-center justify-between rounded-xl bg-rancho-forest p-2 text-lg text-white"
    >
        <button
            @click.stop="decrease"
            class="rounded-md px-2 text-lg text-rancho-ink-100 transition-all hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-rancho-buttercup active:scale-90"
            aria-label="Уменьшить количество"
        >
            −
        </button>

        <span
            class="text-nowrap text-xs font-bold tabular-nums transition-transform duration-200 sm:text-sm"
            :class="bump ? 'scale-110' : 'scale-100'"
        >
            {{ quantity }} {{ item.unit?.short }}
        </span>

        <button
            @click.stop="increase"
            class="rounded-md px-2 text-lg text-rancho-ink-100 transition-all hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-rancho-buttercup active:scale-90"
            aria-label="Увеличить количество"
        >
            +
        </button>
    </div>
</template>
