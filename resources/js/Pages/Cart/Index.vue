<script setup lang="ts">
    import { computed, onMounted } from 'vue';

    import { Link } from '@inertiajs/vue3';

    import { ShoppingCartIcon } from '@heroicons/vue/24/outline';

    import CartItemCard from '@/Components/Cards/CartItemCard.vue';
    import MainLayout from '@/Layouts/MainLayout.vue';
    import { useCartStore } from '@/stores/cart';
    import type { CartItemReason } from '@/types';
    import { formatMoney } from '@/utils/format';

    defineOptions({ layout: MainLayout });

    const cart = useCartStore();

    const computedTotalPrice = computed(() => formatMoney(cart.totalPrice));

    // Проверка на наличие невалидных товаров для блокировки оформления
    const hasInvalidItems = computed(() => cart.items.some((item) => item.valid === false));

    // Вспомогательная функция для понятных текстов ошибок
    const getErrorMessage = (reason: CartItemReason | string | null | undefined) => {
        switch (reason) {
            case 'not_available':
            case 'not_found':
                return 'Товар временно недоступен или снят с продажи';
            case 'out_of_stock':
                return 'Товар закончился на складе';
            default:
                return 'Товар недоступен для заказа';
        }
    };

    onMounted(() => {
        cart.validate(true);
    });
</script>

<template>
    <div class="mx-auto max-w-4xl p-6">
        <h1 class="mb-8 text-3xl font-black uppercase tracking-tight text-slate-900">
            Ваша корзина
        </h1>

        <Transition name="fade-up" appear>
            <div v-if="cart.items.length > 0" class="grid gap-8 lg:grid-cols-3">
                <div class="space-y-4 lg:col-span-2">
                    <TransitionGroup name="cart-item" tag="div" class="space-y-4">
                        <CartItemCard
                            v-for="item in cart.items"
                            :key="item.variant_id"
                            :item="item"
                    /></TransitionGroup>
                </div>

                <!-- Правая колонка с итогом -->
                <div class="shadow-xl h-fit space-y-8 rounded-xl bg-slate-900 p-6 pb-10 text-white">
                    <h2 class="text-xl font-bold">Итог заказа</h2>

                    <!-- Оповещение в блоке заказа, если есть невалидные товары -->
                    <div
                        v-if="hasInvalidItems"
                        class="rounded-xl border border-red-500/30 bg-red-500/10 p-3 text-xs text-red-300"
                    >
                        Удалите недоступные товары из корзины, чтобы продолжить оформление.
                    </div>

                    <div class="space-y-2 border-b border-slate-700 pb-4 text-sm opacity-80">
                        <div class="flex justify-between">
                            <span>Позиций:</span>
                            <span>{{ cart.totalCleanItems }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Доставка:</span>
                            <span
                                class="text-[10px] font-bold uppercase tracking-widest text-emerald-400"
                            >
                                Бесплатно
                            </span>
                        </div>
                    </div>

                    <div class="flex justify-between py-2 text-2xl font-black">
                        <span>Всего:</span>
                        <span>{{ computedTotalPrice }}</span>
                    </div>

                    <div>
                        <Link
                            :href="route('checkout.index')"
                            :disabled="hasInvalidItems"
                            class="shadow-lg w-full rounded-xl bg-orange-600 p-4 font-bold uppercase tracking-widest shadow-orange-900/20 transition-all hover:bg-orange-500 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50 disabled:active:scale-100"
                        >
                            Оформить заказ
                        </Link>
                    </div>
                </div>
            </div>

            <div v-else class="flex flex-col items-center justify-center py-20 text-center">
                <div class="mb-6 rounded-full bg-slate-100 p-8 text-slate-300">
                    <ShoppingCartIcon class="h-16 w-16" />
                </div>
                <h2 class="mb-2 text-2xl font-bold text-slate-900">В корзине пока пусто</h2>
                <Link
                    :href="route('catalog.index')"
                    class="rounded-xl bg-slate-900 px-8 py-3 font-bold text-white hover:bg-orange-600"
                >
                    Перейти в каталог
                </Link>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
    /* 1. Появление заголовка */
    .page-title {
        animation: fade-up 0.4s ease both;
    }

    /* 2. Универсальный fade-up для крупных блоков */
    .fade-up-enter-active {
        transition:
            opacity 0.4s ease,
            transform 0.4s ease;
    }
    .fade-up-leave-active {
        transition:
            opacity 0.2s ease,
            transform 0.2s ease;
    }
    .fade-up-enter-from {
        opacity: 0;
        transform: translateY(12px);
    }
    .fade-up-leave-to {
        opacity: 0;
        transform: translateY(-12px);
    }

    /* 3. Анимация карточек корзины (добавление/удаление) */
    .cart-item-enter-active {
        transition:
            opacity 0.3s ease,
            transform 0.3s ease;
    }
    .cart-item-leave-active {
        transition:
            opacity 0.2s ease,
            transform 0.2s ease;
        position: absolute; /* чтобы остальные карточки не прыгали */
        width: 100%;
    }
    .cart-item-enter-from {
        opacity: 0;
        transform: translateX(-20px) scale(0.98);
    }
    .cart-item-leave-to {
        opacity: 0;
        transform: translateX(20px) scale(0.98);
    }
    .cart-item-move {
        transition: transform 0.3s ease;
    }

    @keyframes fade-up {
        from {
            opacity: 0;
            transform: translateY(12px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
