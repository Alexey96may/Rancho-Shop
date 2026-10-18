<script setup lang="ts">
    import { computed, onBeforeUnmount, ref, watch } from 'vue';

    import { TrashIcon } from '@heroicons/vue/24/outline';

    import { useCartStore } from '@/stores/cart';
    import type { CartItem, ProductVariantDTO } from '@/types';

    interface Props {
        item: ProductVariantDTO | CartItem;
        hasUndo?: boolean;
        repeatDelay?: number;
        repeatInterval?: number;
    }

    const props = withDefaults(defineProps<Props>(), {
        hasUndo: false,
        repeatDelay: 400,
        repeatInterval: 100,
    });

    const cart = useCartStore();

    const variantId = computed(() =>
        'variant_id' in props.item ? props.item.variant_id : props.item.id,
    );

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
            default:
                return 1;
        }
    });

    const stock = computed(() => Number(cartItem.value?.stock ?? 0));
    const isAtMin = computed(() => quantity.value > 0 && quantity.value <= step.value);
    const isInvalid = computed(() => cartItem.value?.valid === false);

    const isPlusDisabled = computed(() => {
        if (!cartItem.value || isInvalid.value) return true;
        if (stock.value <= 0) return true;
        return quantity.value + step.value > stock.value;
    });

    const isMinusDisabled = computed(() => {
        if (!cartItem.value || isInvalid.value) return true;
        return quantity.value <= 0;
    });

    // ─────── Long-press repeat ───────
    let delayTimer: ReturnType<typeof setTimeout> | null = null;
    let repeatTimer: ReturnType<typeof setInterval> | null = null;

    function stopRepeat() {
        if (delayTimer) {
            clearTimeout(delayTimer);
            delayTimer = null;
        }
        if (repeatTimer) {
            clearInterval(repeatTimer);
            repeatTimer = null;
        }
    }

    function startRepeat(action: () => void, shouldStop: () => boolean) {
        if (delayTimer || repeatTimer) return;
        action();
        if (shouldStop()) return;

        delayTimer = setTimeout(() => {
            delayTimer = null;
            repeatTimer = setInterval(() => {
                if (shouldStop()) {
                    stopRepeat();
                    return;
                }
                action();
            }, props.repeatInterval);
        }, props.repeatDelay);
    }

    function onPlusDown() {
        if (isPlusDisabled.value) return;
        startRepeat(
            () => cart.increment(variantId.value, step.value),
            () => isPlusDisabled.value,
        );
    }

    function onMinusDown() {
        if (isMinusDisabled.value) return;
        // trash (минимум) — одно удаление, без автоповтора
        if (isAtMin.value) {
            cart.decrement(variantId.value, step.value, props.hasUndo);
            return;
        }
        startRepeat(
            () => cart.decrement(variantId.value, step.value, props.hasUndo),
            () => isMinusDisabled.value || isAtMin.value,
        );
    }

    // Keyboard (Enter/Space) → click с detail === 0; pointer уже обработан выше
    function onPlusClick(e: MouseEvent) {
        if (e.detail !== 0) return;
        if (isPlusDisabled.value) return;
        cart.increment(variantId.value, step.value);
    }

    function onMinusClick(e: MouseEvent) {
        if (e.detail !== 0) return;
        if (isMinusDisabled.value) return;
        cart.decrement(variantId.value, step.value, props.hasUndo);
    }

    onBeforeUnmount(stopRepeat);

    // ─────── Bump при изменении ───────
    const bump = ref(false);
    let bumpTimeout: ReturnType<typeof setTimeout> | null = null;
    watch(quantity, () => {
        bump.value = false;
        if (bumpTimeout) clearTimeout(bumpTimeout);
        requestAnimationFrame(() => {
            bump.value = true;
            bumpTimeout = setTimeout(() => (bump.value = false), 200);
        });
    });

    onBeforeUnmount(() => {
        if (bumpTimeout) clearTimeout(bumpTimeout);
    });
</script>

<template>
    <div
        class="flex h-12 w-full select-none items-center justify-between rounded-xl bg-rancho-forest p-1.5 text-white"
        style="-webkit-touch-callout: none"
        @contextmenu.prevent
    >
        <!-- MINUS / TRASH -->
        <button
            type="button"
            :disabled="isMinusDisabled"
            :aria-label="isAtMin ? 'Удалить из корзины' : 'Уменьшить количество'"
            class="flex h-9 w-9 touch-none items-center justify-center rounded-md text-lg outline-none transition-all focus-visible:ring-2 focus-visible:ring-rancho-buttercup"
            :class="[
                isMinusDisabled
                    ? 'cursor-not-allowed text-rancho-ink-100/25'
                    : isAtMin
                      ? 'text-rancho-ember hover:text-rancho-ember/80 active:scale-90'
                      : 'text-rancho-ink-100 hover:text-white active:scale-90',
            ]"
            @pointerdown="onMinusDown"
            @pointerup="stopRepeat"
            @pointercancel="stopRepeat"
            @pointerleave="stopRepeat"
            @click="onMinusClick"
        >
            <Transition
                mode="out-in"
                enter-active-class="transition duration-150 ease-out"
                leave-active-class="transition duration-100 ease-in"
                enter-from-class="opacity-0 scale-50 rotate-90"
                enter-to-class="opacity-100 scale-100 rotate-0"
                leave-from-class="opacity-100 scale-100 rotate-0"
                leave-to-class="opacity-0 scale-50 -rotate-90"
            >
                <TrashIcon
                    v-if="isAtMin && !isMinusDisabled"
                    key="trash"
                    class="h-4 w-4"
                    aria-hidden="true"
                />
                <span v-else key="minus" aria-hidden="true">−</span>
            </Transition>
        </button>

        <!-- QTY -->
        <span
            class="text-nowrap text-xs font-bold tabular-nums transition-transform duration-200 sm:text-sm"
            :class="bump ? 'scale-110' : 'scale-100'"
        >
            {{ quantity }} {{ item.unit?.short }}
        </span>

        <!-- PLUS -->
        <button
            type="button"
            :disabled="isPlusDisabled"
            aria-label="Увеличить количество"
            class="flex h-9 w-9 touch-none items-center justify-center rounded-md text-lg outline-none transition-all focus-visible:ring-2 focus-visible:ring-rancho-buttercup"
            :class="[
                isPlusDisabled
                    ? 'cursor-not-allowed text-rancho-ink-100/25'
                    : 'text-rancho-ink-100 hover:text-white active:scale-90',
            ]"
            @pointerdown="onPlusDown"
            @pointerup="stopRepeat"
            @pointercancel="stopRepeat"
            @pointerleave="stopRepeat"
            @click="onPlusClick"
        >
            <span aria-hidden="true">+</span>
        </button>
    </div>
</template>
