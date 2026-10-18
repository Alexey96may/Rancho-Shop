<script setup lang="ts">
    import { computed, onMounted, ref, watch } from 'vue';

    import { Link, router } from '@inertiajs/vue3';

    import { ShoppingCartIcon } from '@heroicons/vue/24/outline';

    import CartItemCard from '@/Components/Cards/CartItemCard.vue';
    import BaseSubmitButton from '@/Components/UI/BaseSubmitButton.vue';
    import MainLayout from '@/Layouts/MainLayout.vue';
    import { useCartStore } from '@/stores/cart';

    defineOptions({ layout: MainLayout });

    const cart = useCartStore();

    const isValidating = ref(false);

    const hasInvalidItems = computed(() => cart.items.some((item) => item.valid === false));
    const isCartEmpty = computed(() => cart.items.length === 0);

    async function validateCart() {
        if (cart.items.length === 0) return;

        isValidating.value = true;
        try {
            await cart.validate(true);
        } catch (e) {
            console.error('Cart validation failed', e);
        } finally {
            isValidating.value = false;
        }
    }

    onMounted(validateCart);

    watch(
        () => cart.items.map((i) => i.variant_id).join('|'),
        () => {
            if (cart.items.length > 0) validateCart();
        },
    );

    function goToCheckout() {
        if (cart.items.length === 0 || hasInvalidItems.value) return;
        router.visit(route('checkout.index'));
    }
</script>

<template>
    <AppContainer class="bg-rancho-paper">
        <h1
            class="my-10 text-lg font-black uppercase tracking-tight text-rancho-forest sm:text-2xl"
        >
            Корзина
        </h1>

        <Transition name="fade-up" mode="out-in" appear>
            <!-- КОРЗИНА -->
            <div v-if="cart.items.length > 0" key="cart" class="grid gap-8 lg:grid-cols-3">
                <!-- ЛЕВАЯ КОЛОНКА: товары -->
                <div class="space-y-4 lg:col-span-2">
                    <TransitionGroup name="cart-item" tag="div" class="relative space-y-4">
                        <CartItemCard
                            v-for="item in cart.items"
                            :key="item.variant_id"
                            :item="item"
                        />
                    </TransitionGroup>
                </div>

                <!-- ПРАВАЯ КОЛОНКА: итог -->
                <aside
                    class="shadow-xl h-fit space-y-5 rounded-2xl bg-rancho-forest p-5 text-rancho-paper sm:space-y-6 sm:p-6 sm:pb-8"
                    aria-label="Итог заказа"
                >
                    <h2
                        class="text-base font-black uppercase tracking-[0.15em] text-rancho-paper/90 sm:text-lg"
                    >
                        Заказ
                    </h2>

                    <Transition name="collapse">
                        <div
                            v-if="hasInvalidItems"
                            class="rounded-xl border border-rose-300/25 bg-rose-500/10 p-3 text-xs font-medium leading-relaxed text-rose-100"
                            role="alert"
                        >
                            Удалите недоступные товары из корзины, чтобы продолжить оформление.
                        </div>
                    </Transition>

                    <dl class="space-y-2.5 border-b border-white/10 pb-4 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="font-medium text-rancho-paper/65">Позиций</dt>
                            <div
                                class="relative inline-flex h-[1.25em] min-w-[2.5rem] items-center justify-end"
                            >
                                <Transition name="price-swap">
                                    <dd
                                        :key="cart.totalCleanItems"
                                        class="absolute right-0 font-semibold text-rancho-paper"
                                    >
                                        {{ cart.totalCleanItems }}
                                    </dd>
                                </Transition>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <dt class="font-medium text-rancho-paper/65">Сумма</dt>
                            <div
                                class="relative inline-flex h-[1.25em] min-w-[6rem] items-center justify-end sm:min-w-[7rem]"
                            >
                                <Transition name="price-swap">
                                    <dd
                                        :key="cart.totalPriceFormatted"
                                        class="absolute right-0 whitespace-nowrap font-semibold text-rancho-paper"
                                    >
                                        {{ cart.totalPriceFormatted }}
                                    </dd>
                                </Transition>
                            </div>
                        </div>

                        <div
                            v-if="cart.hasDelivery"
                            class="flex items-center justify-between gap-3"
                        >
                            <dt class="font-medium text-rancho-paper/65">Доставка</dt>
                            <div
                                class="relative inline-flex h-[1.25em] min-w-[6rem] items-center justify-end sm:min-w-[7rem]"
                            >
                                <Transition name="price-swap">
                                    <dd
                                        :key="cart.deliveryPriceFormatted ?? 'none'"
                                        class="absolute right-0 whitespace-nowrap font-semibold text-rancho-paper"
                                    >
                                        {{ cart.deliveryPriceFormatted }}
                                    </dd>
                                </Transition>
                            </div>
                        </div>
                    </dl>

                    <div class="flex items-center justify-between gap-3">
                        <span
                            class="text-sm font-black uppercase tracking-[0.15em] text-rancho-paper"
                            >Итого</span
                        >
                        <div
                            class="relative inline-flex h-[1.6em] min-w-[7rem] items-center justify-end sm:min-w-[8rem]"
                        >
                            <Transition name="price-swap">
                                <span
                                    :key="cart.grandTotalFormatted"
                                    class="absolute right-0 whitespace-nowrap text-xl font-black text-rancho-paper sm:text-2xl"
                                >
                                    {{ cart.grandTotalFormatted }}
                                </span>
                            </Transition>
                        </div>
                    </div>

                    <BaseSubmitButton
                        type="button"
                        :processing="isValidating"
                        :disabled="isCartEmpty || hasInvalidItems"
                        label="Оформить заказ"
                        :icon="ShoppingCartIcon"
                        class="shadow-md hover:shadow-lg disabled:!shadow-none w-full !bg-rancho-buttercup !text-rancho-forest hover:!bg-rancho-buttercup/90 focus-visible:!ring-4 focus-visible:!ring-rancho-buttercup/30 active:!scale-[0.98] disabled:!bg-rancho-buttercup/70 disabled:!text-rancho-paper/40"
                        @click="goToCheckout"
                    />

                    <Transition name="collapse">
                        <p
                            v-if="cart.freeDeliveryGapFormatted && cart.hasDelivery"
                            class="flex items-center gap-2 text-center text-[11px] font-medium leading-relaxed text-rancho-paper/40"
                        >
                            До бесплатной доставки:
                            <span
                                class="relative inline-flex h-[1.25em] min-w-[4.5rem] items-center justify-start"
                            >
                                <Transition name="price-swap">
                                    <span
                                        :key="cart.freeDeliveryGapFormatted"
                                        class="absolute inset-y-0 left-0 flex items-center whitespace-nowrap font-bold text-rancho-ember"
                                    >
                                        {{ cart.freeDeliveryGapFormatted }}
                                    </span>
                                </Transition>
                            </span>
                        </p>
                    </Transition>
                </aside>
            </div>

            <!-- ПУСТАЯ КОРЗИНА -->
            <div
                v-else
                key="empty"
                class="flex flex-col items-center justify-center pt-16 text-center"
                role="status"
            >
                <div class="mb-6 rounded-full bg-rancho-ink-100 p-6 text-rancho-ink-400 sm:p-8">
                    <ShoppingCartIcon class="h-12 w-12 sm:h-16 sm:w-16" aria-hidden="true" />
                </div>

                <h2 class="mb-2 text-xl font-black text-rancho-forest sm:text-2xl">
                    В корзине пока пусто
                </h2>

                <p class="mb-6 max-w-sm text-sm text-rancho-ink-600">
                    Загляните в каталог — там свежие продукты с нашей фермы.
                </p>

                <Link
                    :href="route('catalog.index')"
                    class="shadow-md hover:shadow-lg inline-flex items-center gap-2 rounded-xl bg-rancho-pine px-8 py-3 text-sm font-bold text-white transition-all hover:bg-rancho-forest focus:outline-none focus-visible:ring-4 focus-visible:ring-rancho-pine/30 active:scale-95"
                >
                    Перейти в каталог
                </Link>
            </div>
        </Transition>
    </AppContainer>
</template>

<style scoped>
    /* ===== Смена Корзина ↔ Пусто ===== */
    .fade-up-enter-active,
    .fade-up-leave-active {
        transition:
            opacity 0.3s ease,
            transform 0.3s ease;
    }
    .fade-up-enter-from {
        opacity: 0;
        transform: translateY(8px);
    }
    .fade-up-leave-to {
        opacity: 0;
        transform: translateY(-8px);
    }

    /* ===== Плавная высота (алерт, gap) ===== */
    .collapse-enter-active,
    .collapse-leave-active {
        transition:
            opacity 0.25s ease,
            max-height 0.35s cubic-bezier(0.4, 0, 0.2, 1),
            margin 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
    }
    .collapse-enter-from,
    .collapse-leave-to {
        opacity: 0;
        max-height: 0;
        margin-top: 0;
        margin-bottom: 0;
    }
    .collapse-enter-to,
    .collapse-leave-from {
        opacity: 1;
        max-height: 200px;
    }

    /* ===== Плавная смена значений (цена/итог) ===== */
    .fade-enter-active,
    .fade-leave-active {
        transition: opacity 0.2s ease;
    }
    .fade-enter-from,
    .fade-leave-to {
        opacity: 0;
    }

    /* ===== Карточки в списке ===== */
    .cart-item-enter-active,
    .cart-item-leave-active {
        transition:
            opacity 0.3s ease,
            transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .cart-item-enter-from {
        opacity: 0;
        transform: translateX(-20px) scale(0.98);
    }
    .cart-item-leave-to {
        opacity: 0;
        transform: translateX(20px) scale(0.98);
    }
    .cart-item-leave-active {
        position: absolute;
        width: 100%;
    }
    .cart-item-move {
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    @media (prefers-reduced-motion: reduce) {
        .fade-up-enter-active,
        .fade-up-leave-active,
        .collapse-enter-active,
        .collapse-leave-active,
        .fade-enter-active,
        .fade-leave-active,
        .cart-item-enter-active,
        .cart-item-leave-active,
        .cart-item-move {
            transition: none;
        }
    }
</style>
