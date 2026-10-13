<script setup lang="ts">
    import { computed, onMounted, ref, watch } from 'vue';

    import { router } from '@inertiajs/vue3';

    import { ShoppingCartIcon } from '@heroicons/vue/24/outline';

    import CartItemCard from '@/Components/Cards/CartItemCard.vue';
    import BaseSubmitButton from '@/Components/UI/BaseSubmitButton.vue';
    import MainLayout from '@/Layouts/MainLayout.vue';
    import { useCartStore } from '@/stores/cart';
    import { formatMoney } from '@/utils/format';

    defineOptions({ layout: MainLayout });

    const cart = useCartStore();

    const computedTotalPrice = computed(() => formatMoney(cart.totalPrice));

    /** Идёт проверка корзины */
    const isValidating = ref(false);

    /** Есть невалидные товары */
    const hasInvalidItems = computed(() => cart.items.some((item) => item.valid === false));

    /** Корзина пуста */
    const isCartEmpty = computed(() => cart.items.length === 0);

    /** Кнопка заблокирована */
    const isCheckoutBlocked = computed(
        () => isValidating.value || isCartEmpty.value || hasInvalidItems.value,
    );

    /** Причина блокировки */
    const blockReason = computed<string | null>(() => {
        if (isValidating.value) return 'Проверяем наличие товаров...';
        if (isCartEmpty.value) return 'Корзина пуста';
        if (hasInvalidItems.value) return 'Удалите недоступные товары';
        return null;
    });

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

    /** Перепроверка при изменении состава корзины */
    watch(
        () => cart.items.map((i) => i.variant_id).join('|'),
        () => {
            if (cart.items.length > 0) {
                validateCart();
            }
        },
    );

    /** Переход к оформлению */
    function goToCheckout() {
        if (isCheckoutBlocked.value) return;
        router.visit(route('checkout.index'));
    }
</script>

<template>
    <AppContainer class="min-h-screen bg-rancho-paper">
        <h1
            class="my-10 text-lg font-black uppercase tracking-tight text-rancho-forest sm:text-2xl"
        >
            Ваша корзина
        </h1>

        <Transition name="fade-up" appear>
            <div v-if="cart.items.length > 0" class="grid gap-8 lg:grid-cols-3">
                <!-- ЛЕВАЯ КОЛОНКА: товары -->
                <div v-scroll-reveal class="space-y-4 lg:col-span-2">
                    <TransitionGroup name="cart-item" tag="div" class="space-y-4">
                        <CartItemCard
                            v-for="item in cart.items"
                            :key="item.variant_id"
                            :item="item"
                        />
                    </TransitionGroup>
                </div>

                <!-- ПРАВАЯ КОЛОНКА: итог -->
                <aside
                    v-scroll-reveal
                    class="shadow-xl h-fit space-y-6 rounded-2xl bg-rancho-forest p-6 text-white sm:space-y-8 sm:pb-10"
                    aria-label="Итог заказа"
                >
                    <h2 class="text-lg font-black uppercase tracking-wider sm:text-xl">
                        Итог заказа
                    </h2>

                    <!-- Оповещение о невалидных товарах -->
                    <div
                        v-if="hasInvalidItems"
                        class="rounded-xl border border-rose-400/30 bg-rose-500/15 p-3 text-xs font-medium text-rose-200"
                        role="alert"
                    >
                        Удалите недоступные товары из корзины, чтобы продолжить оформление.
                    </div>

                    <dl class="space-y-2 border-b border-white/10 pb-4 text-sm text-white/70">
                        <div class="flex justify-between">
                            <dt>Позиций:</dt>
                            <dd>{{ cart.totalCleanItems }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt>Доставка:</dt>
                            <dd
                                class="text-[10px] font-black uppercase tracking-widest text-rancho-buttercup"
                            >
                                Бесплатно
                            </dd>
                        </div>
                    </dl>

                    <div class="flex items-baseline justify-between py-2">
                        <span class="text-sm font-bold text-white/80">Всего:</span>
                        <span class="text-xl font-black text-white sm:text-2xl">
                            {{ computedTotalPrice }}
                        </span>
                    </div>

                    <!-- КНОПКА ОФОРМЛЕНИЯ -->
                    <BaseSubmitButton
                        type="button"
                        :processing="isValidating"
                        :disabled="isCartEmpty || hasInvalidItems"
                        :label="'Оформить заказ'"
                        :with-icon="false"
                        class="!text-rancho-ink-950 disabled:!bg-rancho-ink-200 disabled:!text-rancho-ink-500 w-full !bg-rancho-buttercup hover:!bg-rancho-buttercup/90"
                        @click="goToCheckout"
                    />
                </aside>
            </div>

            <!-- ПУСТАЯ КОРЗИНА -->
            <div
                v-else
                v-scroll-reveal
                class="flex flex-col items-center justify-center py-20 text-center"
                role="status"
            >
                <div class="bg-rancho-ink-100 text-rancho-ink-400 mb-6 rounded-full p-6 sm:p-8">
                    <ShoppingCartIcon class="h-12 w-12 sm:h-16 sm:w-16" aria-hidden="true" />
                </div>

                <h2 class="mb-2 text-xl font-black text-rancho-forest sm:text-2xl">
                    В корзине пока пусто
                </h2>

                <p class="text-rancho-ink-600 mb-6 max-w-sm text-sm">
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
