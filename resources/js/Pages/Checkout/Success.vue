<script setup lang="ts">
    import type { PropType } from 'vue';
    import { computed } from 'vue';

    import { Head, Link } from '@inertiajs/vue3';

    import { CheckIcon } from '@heroicons/vue/24/outline';

    import MainLayout from '@/Layouts/MainLayout.vue';
    import type { Order } from '@/types';
    import { formatMoney } from '@/utils/format';

    defineOptions({ layout: MainLayout });

    const props = defineProps<{
        order: Order;
        magicLink?: string | null;
    }>();

    const computedTotalPrice = computed(() => formatMoney(props.order.total_price));
    const computedDeliveryPrice = computed(() => formatMoney(props.order.delivery_price));
</script>

<template>
    <AppContainer class="min-h-screen bg-rancho-paper pt-12 sm:pt-16">
        <Head title="Заказ успешно оформлен" />

        <div>
            <!-- Карточка подтверждения -->
            <div
                v-scroll-reveal
                class="shadow-sm mb-8 rounded-2xl border border-rancho-ink-200 bg-white p-6 text-center sm:p-10"
            >
                <div
                    class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-rancho-pine/10 text-rancho-pine ring-1 ring-inset ring-rancho-pine/20"
                    aria-hidden="true"
                >
                    <CheckIcon class="h-8 w-8" />
                </div>

                <h1 class="mb-2 text-2xl font-black text-rancho-forest sm:text-3xl">
                    Спасибо за заказ!
                </h1>

                <p class="text-sm leading-relaxed text-rancho-ink-600 sm:text-base">
                    Мы получили ваш заказ
                    <span class="font-black text-rancho-forest">#{{ order.id }}</span>
                    и уже начали его обработку.
                </p>
            </div>

            <!-- MAGIC LINK -->
            <div
                v-if="magicLink"
                v-scroll-reveal
                class="shadow-sm mb-8 rounded-2xl border border-rancho-buttercup/40 bg-rancho-buttercup/15 p-6 sm:p-8"
            >
                <p class="text-base font-black text-rancho-forest">Личный кабинет готов</p>
                <p class="mt-2 text-sm leading-relaxed text-rancho-ink-600">
                    Войти можно по ссылке (действует 24 часа). Рекомендуем установить пароль сразу
                    после входа — иначе при следующем визите понадобится новая ссылка.
                </p>
                <a
                    :href="magicLink"
                    class="mt-4 inline-flex w-full items-center justify-center rounded-xl bg-rancho-pine px-6 py-3 text-sm font-bold text-white transition-all hover:bg-rancho-forest focus:outline-none focus-visible:ring-4 focus-visible:ring-rancho-pine/30 active:scale-95 sm:w-auto"
                >
                    Войти в кабинет
                </a>
            </div>

            <!-- Детали заказа -->
            <div
                v-scroll-reveal
                class="shadow-sm mb-8 rounded-2xl border border-rancho-ink-200 bg-white p-6 sm:p-8"
            >
                <h2
                    class="mb-4 border-b border-rancho-ink-100 pb-3 text-base font-black uppercase tracking-wider text-rancho-forest sm:text-lg"
                >
                    Состав заказа
                </h2>

                <!-- Список товаров -->
                <div v-if="order.items?.length" class="divide-y divide-rancho-ink-100">
                    <div
                        v-for="item in order.items"
                        :key="item.id"
                        class="flex items-center justify-between gap-4 py-4"
                    >
                        <div class="flex min-w-0 items-center gap-4">
                            <!-- Превью -->
                            <div
                                v-if="item.images && item.images.length > 0"
                                class="h-12 w-12 shrink-0 overflow-hidden rounded-lg border border-rancho-ink-200 bg-rancho-ink-50"
                            >
                                <AppImage
                                    :src="item.images[0]"
                                    :alt="item.product_name"
                                    context="product"
                                />
                            </div>

                            <div class="min-w-0">
                                <p class="truncate font-bold text-rancho-forest">
                                    {{ item.product_name }}
                                </p>
                                <p class="text-sm text-rancho-ink-500">
                                    {{ item.quantity }} {{ item.unit?.name || 'шт.' }} ×
                                    {{ formatMoney(item.unit_price) }}
                                </p>
                            </div>
                        </div>

                        <p class="shrink-0 font-bold text-rancho-forest">
                            {{ formatMoney(item.quantity * item.unit_price) }}
                        </p>
                    </div>
                </div>

                <!-- Итоговая сумма -->
                <div class="mt-4 space-y-2 border-t border-rancho-ink-100 pt-4">
                    <div class="flex justify-between text-sm text-rancho-ink-600">
                        <span>Доставка</span>
                        <span class="font-bold text-rancho-forest">
                            {{ order.delivery_price > 0 ? computedDeliveryPrice : 'Бесплатно' }}
                        </span>
                    </div>

                    <div
                        v-if="order.discount_total > 0"
                        class="flex justify-between text-sm text-rancho-ember"
                    >
                        <span>Скидка</span>
                        <span class="font-bold"> −{{ formatMoney(order.discount_total) }} </span>
                    </div>

                    <div
                        class="flex items-baseline justify-between border-t border-rancho-ink-100 pt-3 text-lg"
                    >
                        <span class="font-bold text-rancho-ink-600">Итого</span>
                        <span class="text-xl font-black text-rancho-forest sm:text-2xl">
                            {{ computedTotalPrice }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="text-center">
                <Link
                    :href="route('home')"
                    class="shadow-md hover:shadow-lg inline-flex items-center justify-center rounded-xl bg-rancho-pine px-8 py-3 text-sm font-bold text-white transition-all hover:bg-rancho-forest focus:outline-none focus-visible:ring-4 focus-visible:ring-rancho-pine/30 active:scale-95"
                >
                    Вернуться на главную
                </Link>
            </div>
        </div>
    </AppContainer>
</template>
