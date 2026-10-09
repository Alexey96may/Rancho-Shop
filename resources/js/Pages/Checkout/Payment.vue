<script setup lang="ts">
    import { computed, onMounted, ref } from 'vue';

    import { Head, Link } from '@inertiajs/vue3';

    import MainLayout from '@/Layouts/MainLayout.vue';
    import type { Order } from '@/types';
    import { formatMoney } from '@/utils/format';

    defineOptions({ layout: MainLayout });

    const props = defineProps<{
        order: Order;
        payment: {
            type: 'redirect' | 'fake' | 'direct';
            action_url?: string;
            params?: Record<string, string>;
            details?: {
                phone: string;
                bank: string;
                recipient: string;
                note: string;
            };
        };
    }>();

    const formRef = ref<HTMLFormElement | null>(null);

    onMounted(() => {
        // Если драйвер - настоящий PayMaster, автоматом отправляем форму
        if (props.payment.type === 'redirect' && formRef.value) {
            formRef.value.submit();
        }
    });

    const computedTotalPrice = computed(() => formatMoney(props.order.total_price));
</script>

<template>
    <Head title="Оплата заказа" />

    <div class="mx-auto max-w-2xl px-4 py-12">
        <div class="shadow-sm rounded-2xl border border-slate-200 bg-white p-6">
            <h1 class="text-xl font-bold text-slate-900">Оплата заказа #{{ order.id }}</h1>
            <p class="mt-1 text-sm text-slate-500">
                К оплате:
                <span class="font-semibold text-slate-900">{{ computedTotalPrice }}</span>
            </p>

            <!-- 1. Режим PayMaster (Авто-перенаправление) -->
            <div v-if="payment.type === 'redirect'" class="mt-8 text-center">
                <div
                    class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-slate-200 border-t-indigo-600"
                ></div>
                <p class="mt-4 text-sm text-slate-600">Перенаправление на платёжный шлюз...</p>

                <form ref="formRef" :action="payment.action_url" method="POST" class="hidden">
                    <input
                        v-for="(value, key) in payment.params"
                        :key="key"
                        type="hidden"
                        :name="key"
                        :value="value"
                    />
                </form>
            </div>

            <!-- 2. Режим Прямого перевода / СБП (Direct) -->
            <div v-else-if="payment.type === 'direct'" class="mt-6 space-y-4">
                <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                    <h3 class="font-medium text-slate-800">Реквизиты для перевода (СБП):</h3>
                    <ul class="mt-3 space-y-2 text-sm text-slate-600">
                        <li><strong>Номер телефона:</strong> {{ payment.details?.phone }}</li>
                        <li><strong>Банк:</strong> {{ payment.details?.bank }}</li>
                        <li><strong>Получатель:</strong> {{ payment.details?.recipient }}</li>
                        <li><strong>Назначение платежа:</strong> {{ payment.details?.note }}</li>
                    </ul>
                </div>

                <p class="text-xs text-slate-500">
                    После выполнения перевода администратор подтвердит получение средств и статус
                    заказа изменится автоматически.
                </p>

                <div class="pt-4">
                    <Link
                        :href="route('profile.orders.index')"
                        class="inline-flex w-full justify-center rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white hover:bg-slate-800"
                    >
                        Перейти к моим заказам
                    </Link>
                </div>
            </div>

            <!-- 3. Фейковый режим для локалки (Fake) -->
            <div v-else-if="payment.type === 'fake'" class="mt-6 text-center">
                <div
                    class="mb-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800"
                >
                    <strong>Тестовый режим (Fake Driver):</strong> Нажмите кнопку ниже, чтобы
                    сымитировать мгновенную успешную оплату.
                </div>

                <a
                    :href="payment.action_url"
                    class="inline-flex w-full justify-center rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white hover:bg-emerald-500"
                >
                    Оплатить (Симуляция)
                </a>
            </div>
        </div>
    </div>
</template>
