<script setup lang="ts">
    import type { PropType } from 'vue';
    import { computed } from 'vue';

    import { Head, Link } from '@inertiajs/vue3';

    import type { Order } from '@/types';
    import { formatMoney } from '@/utils/format';

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
    <Head title="Заказ успешно оформлен" />

    <div class="min-h-screen bg-gray-50 px-4 py-12 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl">
            <!-- Карточка подтверждения -->
            <div
                class="shadow-sm mb-8 rounded-2xl border border-gray-100 bg-white p-6 text-center sm:p-10"
            >
                <div
                    class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-green-100 text-green-600"
                >
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M5 13l4 4L19 7"
                        />
                    </svg>
                </div>

                <h1 class="mb-2 text-2xl font-bold text-gray-900 sm:text-3xl">Спасибо за заказ!</h1>
                <p class="mb-6 text-gray-600">
                    Мы получили ваш заказ
                    <span class="font-semibold text-gray-900">#{{ order.id }}</span> и уже начали
                    его обработку.
                </p>
            </div>

            <!-- Детали заказа -->
            <div class="shadow-sm mb-8 rounded-2xl border border-gray-100 bg-white p-6 sm:p-8">
                <h2 class="mb-4 border-b border-gray-100 pb-3 text-lg font-semibold text-gray-900">
                    Состав заказа
                </h2>

                <!-- Список товаров -->
                <div v-if="order.items?.length" class="divide-y divide-gray-100">
                    <div
                        v-for="item in order.items"
                        :key="item.id"
                        class="flex items-center justify-between gap-4 py-4"
                    >
                        <div class="flex items-center gap-4">
                            <!-- Берем первое изображение из массива images (Media) -->
                            <div
                                v-if="item.images && item.images.length > 0"
                                class="h-12 w-12 flex-shrink-0 overflow-hidden rounded-lg bg-gray-100"
                            >
                                <AppImage
                                    :src="item.images[0]"
                                    :alt="item.product_name"
                                    context="product"
                                />
                            </div>

                            <div>
                                <p class="font-medium text-gray-900">
                                    {{ item.product_name }}
                                </p>
                                <p class="text-sm text-gray-500">
                                    <span>
                                        {{ item.quantity }} {{ item.unit?.name || 'шт.' }} ×
                                        {{ formatMoney(item.unit_price) }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <p class="whitespace-nowrap font-semibold text-gray-900">
                            {{ formatMoney(item.quantity * item.unit_price) }}

                            {{ item.subtotal }}
                        </p>
                    </div>
                </div>

                <!-- Итоговая сумма -->
                <div class="mt-4 space-y-2 border-t border-gray-100 pt-4">
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Доставка</span>
                        <span>{{
                            order.delivery_price > 0 ? computedDeliveryPrice : 'Бесплатно'
                        }}</span>
                    </div>
                    <div
                        v-if="order.discount_total > 0"
                        class="flex justify-between text-sm text-green-600"
                    >
                        <span>Скидка</span>
                        <span>-{{ formatMoney(order.discount_total) }}</span>
                    </div>
                    <div
                        class="flex justify-between border-t border-gray-100 pt-2 text-lg font-bold text-gray-900"
                    >
                        <span>Итого</span>
                        <span>{{ computedTotalPrice }}</span>
                    </div>
                </div>
            </div>

            <!-- Кнопка возврата -->
            <div class="text-center">
                <Link
                    href="/"
                    class="shadow-sm inline-flex items-center justify-center rounded-xl border border-transparent bg-indigo-600 px-6 py-3 text-base font-medium text-white transition-colors hover:bg-indigo-700"
                >
                    Вернуться на главную
                </Link>
            </div>
        </div>
    </div>
</template>
