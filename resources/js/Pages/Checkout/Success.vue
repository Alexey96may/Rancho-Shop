<script setup lang="ts">
    import type { PropType } from 'vue';
    import { computed } from 'vue';

    import { Head, Link } from '@inertiajs/vue3';

    import { CheckIcon } from '@heroicons/vue/24/outline';

    import MainLayout from '@/Layouts/MainLayout.vue';
    import type { Order } from '@/types';
    import { formatMoney } from '@/utils/format';

    defineOptions({ layout: MainLayout });

    const props = defineProps({
        order: {
            type: Object as PropType<Order>,
            required: true,
            validator: (value: Order): boolean => {
                return (
                    typeof value === 'object' &&
                    value !== null &&
                    'id' in value &&
                    'total_price' in value
                );
            },
        },
    });

    const computedTotalPrice = computed(() => formatMoney(props.order.total_price));
    const computedDeliveryPrice = computed(() => formatMoney(props.order.delivery_price));
</script>

<template>
    <AppContainer class="min-h-screen bg-rancho-paper">
        <Head title="Заказ успешно оформлен" />

        <div class="mx-auto max-w-3xl py-12 sm:py-16">
            <!-- Карточка подтверждения -->
            <div
                v-scroll-reveal
                class="border-rancho-ink-200 shadow-sm mb-8 rounded-2xl border bg-white p-6 text-center sm:p-10"
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

                <p class="text-rancho-ink-600 text-sm leading-relaxed sm:text-base">
                    Мы получили ваш заказ
                    <span class="font-black text-rancho-forest">#{{ order.id }}</span>
                    и уже начали его обработку.
                </p>
            </div>

            <!-- Детали заказа -->
            <div
                v-scroll-reveal
                class="border-rancho-ink-200 shadow-sm mb-8 rounded-2xl border bg-white p-6 sm:p-8"
            >
                <h2
                    class="border-rancho-ink-100 mb-4 border-b pb-3 text-base font-black uppercase tracking-wider text-rancho-forest sm:text-lg"
                >
                    Состав заказа
                </h2>

                <!-- Список товаров -->
                <div v-if="order.items?.length" class="divide-rancho-ink-100 divide-y">
                    <div
                        v-for="item in order.items"
                        :key="item.id"
                        class="flex items-center justify-between gap-4 py-4"
                    >
                        <div class="flex min-w-0 items-center gap-4">
                            <!-- Превью -->
                            <div
                                v-if="item.images && item.images.length > 0"
                                class="border-rancho-ink-200 bg-rancho-ink-50 h-12 w-12 shrink-0 overflow-hidden rounded-lg border"
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
                                <p class="text-rancho-ink-500 text-sm">
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
                <div class="border-rancho-ink-100 mt-4 space-y-2 border-t pt-4">
                    <div class="text-rancho-ink-600 flex justify-between text-sm">
                        <span>Доставка</span>
                        <span class="font-bold text-rancho-forest">
                            {{ order.delivery_price > 0 ? computedDeliveryPrice : 'Бесплатно' }}
                        </span>
                    </div>

                    <div
                        v-if="order.discount_total > 0"
                        class="text-rancho-ember flex justify-between text-sm"
                    >
                        <span>Скидка</span>
                        <span class="font-bold"> −{{ formatMoney(order.discount_total) }} </span>
                    </div>

                    <div
                        class="border-rancho-ink-100 flex items-baseline justify-between border-t pt-3 text-lg"
                    >
                        <span class="text-rancho-ink-600 font-bold">Итого</span>
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
