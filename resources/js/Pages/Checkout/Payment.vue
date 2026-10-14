<script setup lang="ts">
    import { computed, onMounted, ref } from 'vue';

    import { Head, Link } from '@inertiajs/vue3';

    import MainLayout from '@/Layouts/MainLayout.vue';
    import type { Order } from '@/types';
    import { formatMoney } from '@/utils/format';

    defineOptions({ layout: MainLayout });

    interface PaymentPayload {
        type: 'redirect' | 'fake' | 'direct' | 'error';

        /** ЮKassa / Тинькофф — простой редирект */
        url?: string;

        /** PayMaster — POST-форма с параметрами */
        action_url?: string;
        params?: Record<string, string>;

        /** Ошибка инициализации платежа */
        error?: string;

        /** Реквизиты прямого перевода (СБП / карта) */
        details?: {
            phone: string;
            bank: string;
            recipient: string;
            note: string;
        };
    }

    const props = defineProps<{
        order: Order;
        payment: PaymentPayload;
    }>();

    const formRef = ref<HTMLFormElement | null>(null);

    onMounted(() => {
        // 1. Простой редирект (ЮKassa, Тинькофф): confirmation_url / PaymentURL
        if (props.payment.type === 'redirect' && props.payment.url) {
            if (typeof window === 'undefined') return;
            window.location.href = props.payment.url;
            return;
        }

        // 2. POST-форма (PayMaster): action_url + params
        if (props.payment.type === 'redirect' && props.payment.action_url && formRef.value) {
            formRef.value.submit();
        }
    });

    const computedTotalPrice = computed(() => formatMoney(props.order.total_price));
</script>

<template>
    <AppContainer class="min-h-screen bg-rancho-paper">
        <Head title="Оплата заказа" />

        <div class="mx-auto max-w-2xl py-12">
            <div class="shadow-sm rounded-2xl border border-rancho-ink-200 bg-white p-6 sm:p-8">
                <h1 v-scroll-reveal class="text-xl font-black text-rancho-forest sm:text-2xl">
                    Оплата заказа #{{ order.id }}
                </h1>

                <p v-scroll-reveal class="mt-1.5 text-sm text-rancho-ink-600">
                    К оплате:
                    <span class="font-black text-rancho-forest">
                        {{ computedTotalPrice }}
                    </span>
                </p>

                <!-- 1. REDIRECT: ЮKassa / Тинькофф / PayMaster -->
                <div v-if="payment.type === 'redirect'" v-scroll-reveal class="mt-8 text-center">
                    <div
                        class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-rancho-ink-200 border-t-rancho-pine"
                        aria-hidden="true"
                    ></div>

                    <p class="mt-4 text-sm text-rancho-ink-600">
                        Перенаправление на платёжный шлюз...
                    </p>

                    <!-- ЮKassa / Тинькофф: прямая ссылка + резервный клик -->
                    <a
                        v-if="payment.url"
                        :href="payment.url"
                        class="mt-4 inline-block text-sm font-bold text-rancho-pine underline transition-colors hover:text-rancho-forest hover:no-underline"
                    >
                        Если не перенаправляет — нажмите здесь
                    </a>

                    <!-- PayMaster: скрытая POST-форма -->
                    <form
                        v-if="payment.action_url"
                        ref="formRef"
                        :action="payment.action_url"
                        method="POST"
                        class="hidden"
                    >
                        <input
                            v-for="(value, key) in payment.params"
                            :key="key"
                            type="hidden"
                            :name="key"
                            :value="value"
                        />
                    </form>
                </div>

                <!-- 2. DIRECT: ручной перевод (СБП / карта) -->
                <div v-else-if="payment.type === 'direct'" v-scroll-reveal class="mt-6 space-y-4">
                    <div class="rounded-xl border border-rancho-ink-200 bg-rancho-ink-50 p-4">
                        <h3 class="text-sm font-black uppercase tracking-wider text-rancho-forest">
                            Реквизиты для перевода
                        </h3>

                        <dl class="mt-3 space-y-2 text-sm">
                            <div class="flex flex-col gap-0.5 sm:flex-row sm:gap-2">
                                <dt class="font-bold text-rancho-ink-600 sm:w-40">Телефон:</dt>
                                <dd class="text-rancho-forest">{{ payment.details?.phone }}</dd>
                            </div>
                            <div class="flex flex-col gap-0.5 sm:flex-row sm:gap-2">
                                <dt class="font-bold text-rancho-ink-600 sm:w-40">Банк:</dt>
                                <dd class="text-rancho-forest">{{ payment.details?.bank }}</dd>
                            </div>
                            <div class="flex flex-col gap-0.5 sm:flex-row sm:gap-2">
                                <dt class="font-bold text-rancho-ink-600 sm:w-40">Получатель:</dt>
                                <dd class="text-rancho-forest">
                                    {{ payment.details?.recipient }}
                                </dd>
                            </div>
                            <div
                                v-if="payment.details?.note"
                                class="flex flex-col gap-0.5 sm:flex-row sm:gap-2"
                            >
                                <dt class="font-bold text-rancho-ink-600 sm:w-40">Заметка:</dt>
                                <dd class="text-rancho-forest">
                                    {{ payment.details.note }}
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <p class="text-xs leading-relaxed text-rancho-ink-500">
                        После выполнения перевода администратор подтвердит получение средств, и
                        статус заказа изменится автоматически.
                    </p>

                    <div class="pt-4">
                        <Link
                            :href="route('profile.orders.index')"
                            class="shadow-md hover:shadow-lg inline-flex w-full items-center justify-center rounded-xl bg-rancho-pine px-4 py-3 text-sm font-bold text-white transition-all hover:bg-rancho-forest focus:outline-none focus-visible:ring-4 focus-visible:ring-rancho-pine/30 active:scale-95"
                        >
                            Перейти к моим заказам
                        </Link>
                    </div>
                </div>

                <!-- 3. FAKE: симуляция оплаты (локалка) -->
                <div v-else-if="payment.type === 'fake'" v-scroll-reveal class="mt-6 text-center">
                    <div
                        class="mb-6 rounded-xl bg-rancho-buttercup/15 p-4 text-sm font-medium text-rancho-ink-800 ring-1 ring-inset ring-rancho-buttercup/30"
                        role="status"
                    >
                        <strong class="font-black">Тестовый режим (Fake Driver):</strong>
                        Нажмите кнопку ниже, чтобы сымитировать мгновенную успешную оплату.
                    </div>

                    <a
                        :href="payment.action_url"
                        class="shadow-md hover:shadow-lg inline-flex w-full items-center justify-center rounded-xl bg-rancho-pine px-4 py-3 text-sm font-bold text-white transition-all hover:bg-rancho-forest focus:outline-none focus-visible:ring-4 focus-visible:ring-rancho-pine/30 active:scale-95"
                    >
                        Оплатить (симуляция)
                    </a>
                </div>

                <!-- 4. ERROR: не удалось создать платёж -->
                <div v-else-if="payment.type === 'error'" v-scroll-reveal class="mt-6">
                    <div
                        class="rounded-xl border border-rose-300 bg-rose-50 p-4 text-sm font-medium text-rose-700"
                        role="alert"
                    >
                        ⚠️ {{ payment.error ?? 'Не удалось создать платёж. Попробуйте позже.' }}
                    </div>

                    <div class="mt-4 flex flex-col gap-2">
                        <Link
                            :href="route('checkout.index')"
                            class="shadow-md hover:shadow-lg inline-flex w-full items-center justify-center rounded-xl bg-rancho-pine px-4 py-3 text-sm font-bold text-white transition-all hover:bg-rancho-forest focus:outline-none focus-visible:ring-4 focus-visible:ring-rancho-pine/30 active:scale-95"
                        >
                            Вернуться к оформлению
                        </Link>

                        <Link
                            :href="route('home')"
                            class="inline-flex w-full items-center justify-center rounded-xl border-2 border-rancho-ink-300 bg-white px-4 py-3 text-sm font-bold text-rancho-forest transition-all hover:border-rancho-forest hover:bg-rancho-ink-50 focus:outline-none focus-visible:ring-4 focus-visible:ring-rancho-pine/30 active:scale-95"
                        >
                            На главную
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppContainer>
</template>
