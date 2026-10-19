<script setup lang="ts">
    import { type Ref, computed, nextTick, ref } from 'vue';
    import type { ComponentPublicInstance } from 'vue';

    import { Head, router, useForm, usePage } from '@inertiajs/vue3';

    import { ShoppingCartIcon, TruckIcon } from '@heroicons/vue/24/outline';

    import BaseInput from '@/Components/UI/BaseInput.vue';
    import BaseSubmitButton from '@/Components/UI/BaseSubmitButton.vue';
    import BaseSwitch from '@/Components/UI/BaseSwitch.vue';
    import BaseTextarea from '@/Components/UI/BaseTextarea.vue';
    import PhoneInput from '@/Components/UI/PhoneInput.vue';
    import MainLayout from '@/Layouts/MainLayout.vue';
    import { useCartStore } from '@/stores/cart';
    import { useNotificationsStore } from '@/stores/notifications';
    import { DeliveryDraft, SharedData } from '@/types';
    import { formatDistance, formatMoney } from '@/utils/format';

    defineOptions({ layout: MainLayout });

    interface DeliveryZone {
        name: string;
        path: [number, number][];
        radius: number;
        delivery_price: number;
        free_from: number;
        enabled: boolean;
        priority: number;
        max_distance: number;
    }

    interface DeliveryResult {
        is_valid: boolean;
        delivery_price?: number;
        zone?: DeliveryZone;
        distance_to_route?: number;
        distance_to_farm?: number;
        error?: string;
    }

    interface Props {
        delivery_draft: DeliveryDraft | null;
        delivery_result: DeliveryResult | null;
    }

    const props = defineProps<Props>();

    const cart = useCartStore();
    const notify = useNotificationsStore();
    const page = usePage<SharedData>();

    const user = computed(() => page.props.auth.user);

    const appliedPromo = computed(() => (page.props.promo as any) ?? null);

    const promoCode = ref('');
    const isChecking = ref(false);

    const applyPromo = () => {
        if (!promoCode.value.trim()) return;

        isChecking.value = true;

        router.post(
            route('promo-code.check'),
            {
                code: promoCode.value.trim(),
                subtotal: cart.totalPrice,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    promoCode.value = '';
                },
                onFinish: () => {
                    isChecking.value = false;
                },
            },
        );
    };

    const removePromo = () => {
        router.post(route('promo-code.remove'), {}, { preserveScroll: true });
    };

    const delivery = computed(() => props.delivery_draft ?? page.props.delivery_draft ?? null);
    const deliveryResult = computed(() => props.delivery_result ?? null);

    const nameRef = ref<ComponentPublicInstance<typeof BaseInput> | HTMLInputElement | null>(null);

    const computedDistanceToRoute = computed(() => {
        return formatDistance(deliveryResult.value?.distance_to_route) ?? '';
    });

    const computedDistanceToFarm = computed(() => {
        return formatDistance(deliveryResult.value?.distance_to_farm) ?? '';
    });

    const phoneRef = ref<ComponentPublicInstance<typeof BaseInput> | HTMLInputElement | null>(null);
    const commentRef = ref<
        ComponentPublicInstance<typeof BaseTextarea> | HTMLTextAreaElement | null
    >(null);

    type CheckoutForm = {
        customer_name: string;
        customer_phone: string;
        delivery_address: string | null;
        customer_comment: string;
        is_pickup: boolean;
        lat: number | null;
        lng: number | null;
        create_account: boolean;
    };

    const form = useForm<CheckoutForm>({
        customer_name: user.value?.data?.name ?? '',
        customer_phone: user.value?.data?.phone ?? '',
        delivery_address: delivery.value?.address ?? null,
        customer_comment: '',
        create_account: false,
        is_pickup: !delivery.value?.address,
        lat: delivery.value?.lat ?? null,
        lng: delivery.value?.lng ?? null,
    });

    const isPickup = computed(() => form.is_pickup);

    const isDeliveryValid = computed(() => {
        if (deliveryResult.value) return deliveryResult.value.is_valid;
        return delivery.value?.is_valid ?? false;
    });

    const deliveryError = computed(() => {
        if (isPickup.value) return null;

        const r = deliveryResult.value;
        if (r && !r.is_valid) return r.error ?? 'Выбранный адрес доставки недоступен';
        if (!r && !isDeliveryValid.value) return 'Выбранный адрес доставки недоступен';
        return null;
    });

    const deliveryPrice = computed<number | null>(() => {
        if (isPickup.value) return 0;

        const r = deliveryResult.value;
        if (!r || !r.is_valid) return null;

        const price = r.delivery_price ?? r.zone?.delivery_price ?? null;
        if (price === null) return null;

        const freeFrom = r.zone?.free_from ?? null;
        if (freeFrom !== null && cart.totalPrice >= freeFrom) return 0;

        return price;
    });

    const amountUntilFreeDelivery = computed<number | null>(() => {
        if (isPickup.value) return null;

        const r = deliveryResult.value;
        if (!r || !r.is_valid) return null;

        const freeFrom = r.zone?.free_from ?? null;
        if (freeFrom === null) return null;

        const left = freeFrom - cart.totalPrice;
        return left > 0 ? left : null;
    });

    const computedTotalPrice = computed(() =>
        formatMoney(
            cart.totalPrice - (appliedPromo.value?.discount ?? 0) + (deliveryPrice.value ?? 0),
        ),
    );

    function togglePickup() {
        form.is_pickup = !form.is_pickup;

        if (form.is_pickup) {
            form.delivery_address = null;
            form.lat = null;
            form.lng = null;
        } else {
            form.delivery_address = delivery.value?.address ?? null;
            form.lat = delivery.value?.lat ?? null;
            form.lng = delivery.value?.lng ?? null;
        }
    }

    function goToDeliveryPage() {
        if (typeof window === 'undefined') return;
        window.location.href = '/delivery';
    }

    const errors = computed(() => form.errors);

    function hasError(field: keyof CheckoutForm) {
        return !!errors.value[field];
    }

    function getError(field: keyof CheckoutForm) {
        return errors.value[field];
    }

    function focusElement(el: HTMLElement | ComponentPublicInstance | null | undefined): void {
        if (!el) return;

        const target = (el as ComponentPublicInstance).$el ?? el;

        if (!(target instanceof HTMLElement)) return;

        target.focus();
        target.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    const fieldRefs: Record<string, Ref<HTMLElement | ComponentPublicInstance | null>> = {
        customer_name: nameRef,
        customer_phone: phoneRef,
        customer_comment: commentRef,
    };

    function submit() {
        if (cart.items.length === 0) return;

        form.transform((data) => ({
            ...data,
            items: cart.items,
        })).post(route('checkout.store'), {
            onSuccess: () => {
                cart.clear();
            },
            onError: async (errs) => {
                await nextTick();

                // 1. Глобальное сообщение
                const globalMsg = errs.checkout ?? errs.cart ?? errs.delivery;
                if (globalMsg) {
                    notify.error(globalMsg);
                }

                // 2. Фокус на первое поле с ошибкой
                const firstField = Object.keys(fieldRefs).find((f) => errs[f]);
                if (firstField) {
                    focusElement(fieldRefs[firstField].value);
                }
            },
        });
    }
</script>

<template>
    <AppContainer class="min-h-screen bg-rancho-paper">
        <Head title="Оформление заказа" />

        <div class="pt-10 sm:pt-12">
            <!-- HEADER -->
            <header class="mb-8 sm:mb-10">
                <h1 class="text-2xl font-black text-rancho-forest sm:text-4xl">
                    Оформление заказа
                </h1>
                <p class="mt-2 text-xs text-rancho-ink-600 sm:text-sm">Доставка или самовывоз</p>
            </header>

            <div class="relative grid grid-cols-1 items-start gap-8 lg:grid-cols-3">
                <!-- LEFT -->
                <section v-scroll-reveal class="space-y-6 lg:col-span-2">
                    <BaseInput
                        ref="nameRef"
                        label="Имя"
                        :required="true"
                        placeholder="Введите своё имя"
                        v-model="form.customer_name"
                        @input-start="form.clearErrors('customer_name')"
                        :error="errors.customer_name"
                        theme="light"
                        :disabled="form.processing"
                    />

                    <PhoneInput
                        v-model="form.customer_phone"
                        label="Телефон"
                        placeholder="+7 (___) ___-__-__"
                        :error="form.errors.customer_phone"
                        required
                        theme="light"
                        @input-start="form.clearErrors('customer_phone')"
                        :disabled="form.processing"
                    />

                    <BaseSwitch
                        v-if="!user"
                        v-model="form.create_account"
                        :error="errors.create_account"
                        label="Создать аккаунт"
                        active-text="Аккаунт будет создан автоматически"
                        inactive-text="Аккаунт не будет создан автоматически"
                        theme="light"
                        :disabled="form.processing"
                    />

                    <!-- DELIVERY BLOCK -->
                    <div
                        class="shadow-sm space-y-3 rounded-2xl border border-rancho-ink-200 bg-white p-4 sm:p-5"
                    >
                        <Transition name="slide-fade" mode="out-in">
                            <!-- CASE: PICKUP -->
                            <div v-if="isPickup" key="pickup">
                                <div class="text-sm font-bold text-rancho-forest">📦 Самовывоз</div>
                                <p class="mt-1 text-sm text-rancho-ink-600">
                                    Адрес доставки не выбран
                                </p>

                                <button
                                    type="button"
                                    @click="goToDeliveryPage"
                                    :disabled="form.processing"
                                    class="shadow-md hover:shadow-lg mt-3 w-full rounded-xl bg-rancho-pine px-4 py-3 text-sm font-bold text-white transition-all hover:bg-rancho-forest focus:outline-none focus-visible:ring-4 focus-visible:ring-rancho-pine/30 active:scale-95"
                                >
                                    Выбрать адрес доставки
                                </button>
                            </div>

                            <!-- CASE: DELIVERY -->
                            <div v-else key="delivery">
                                <div
                                    class="flex items-center gap-2 text-sm font-bold text-rancho-pine"
                                >
                                    <TruckIcon class="h-4 w-4" /> Доставка выбрана
                                </div>

                                <p class="mt-1 text-sm text-rancho-ink-700">
                                    {{ form.delivery_address }}
                                </p>

                                <!-- РЕЗУЛЬТАТ РАСЧЁТА -->
                                <Transition name="fade-scale" appear>
                                    <div
                                        v-if="deliveryResult"
                                        class="mt-3 rounded-xl border p-3 text-sm"
                                        :class="
                                            deliveryResult.is_valid
                                                ? 'border-rancho-pine/30 bg-rancho-pine/5 text-rancho-forest'
                                                : 'border-rose-300 bg-rose-50 text-rose-700'
                                        "
                                    >
                                        <template v-if="deliveryResult.is_valid">
                                            <div class="flex justify-between gap-3">
                                                <span class="text-rancho-ink-600">
                                                    Стоимость доставки
                                                </span>
                                                <span class="font-bold text-rancho-forest">
                                                    <template v-if="deliveryPrice === 0">
                                                        бесплатно
                                                    </template>
                                                    <template v-else>
                                                        {{
                                                            deliveryPrice !== null
                                                                ? formatMoney(deliveryPrice)
                                                                : '—'
                                                        }}
                                                    </template>
                                                </span>
                                            </div>

                                            <div
                                                v-if="deliveryResult.zone?.name"
                                                class="mt-1 flex justify-between gap-3 text-rancho-ink-500"
                                            >
                                                <span>Зона</span>
                                                <span>{{ deliveryResult.zone.name }}</span>
                                            </div>

                                            <div
                                                v-if="deliveryResult.distance_to_route != null"
                                                class="mt-1 flex justify-between gap-3 text-rancho-ink-500"
                                            >
                                                <span>До маршрута</span>
                                                <span>
                                                    {{ computedDistanceToRoute }}
                                                </span>
                                            </div>

                                            <div
                                                v-if="deliveryResult.distance_to_farm != null"
                                                class="mt-1 flex justify-between gap-3 text-rancho-ink-500"
                                            >
                                                <span>От фермы</span>
                                                <span>
                                                    {{ computedDistanceToFarm }}
                                                </span>
                                            </div>

                                            <div
                                                v-if="amountUntilFreeDelivery !== null"
                                                class="mt-2 rounded-lg bg-rancho-buttercup/15 p-2 text-xs font-medium text-rancho-ink-800 ring-1 ring-inset ring-rancho-buttercup/30"
                                            >
                                                До бесплатной доставки осталось
                                                <strong class="font-black">
                                                    {{ formatMoney(amountUntilFreeDelivery) }}
                                                </strong>
                                            </div>
                                        </template>

                                        <template v-else>
                                            ⚠️
                                            {{ deliveryResult.error ?? 'Адрес вне зоны доставки' }}
                                        </template>
                                    </div>
                                </Transition>

                                <div class="mt-3 flex flex-wrap gap-2">
                                    <button
                                        type="button"
                                        @click="goToDeliveryPage"
                                        :disabled="form.processing"
                                        class="flex-1 rounded-xl border-2 border-rancho-ink-300 bg-white px-4 py-2.5 text-xs font-black uppercase tracking-widest text-rancho-forest outline-none transition-all hover:border-rancho-forest hover:bg-rancho-ink-50 focus-visible:ring-4 focus-visible:ring-rancho-pine/30 active:scale-95"
                                    >
                                        Изменить
                                    </button>

                                    <button
                                        type="button"
                                        @click="togglePickup"
                                        :disabled="form.processing"
                                        class="hover:rancho-ember/40 flex-1 rounded-xl border-2 border-rancho-ember bg-white px-4 py-2.5 text-xs font-black uppercase tracking-widest text-rancho-ember outline-none transition-all hover:bg-rancho-ember/30 focus-visible:ring-4 focus-visible:ring-rose-500/30 active:scale-95"
                                    >
                                        Самовывоз
                                    </button>
                                </div>
                            </div>
                        </Transition>

                        <!-- DELIVERY ERROR -->
                        <Transition name="fade-up">
                            <div
                                v-if="$page.props.errors.delivery"
                                class="rounded-xl border border-rose-300 bg-rose-50 p-3 text-sm font-medium text-rose-700"
                                role="alert"
                            >
                                ⚠️ {{ $page.props.errors.delivery }}
                            </div>
                        </Transition>

                        <!-- DELIVERY VALIDATION -->
                        <Transition name="fade-up">
                            <p
                                v-if="deliveryError"
                                class="rounded-xl bg-rancho-buttercup/15 px-3 py-2 text-sm font-medium text-rancho-ink-800 ring-1 ring-inset ring-rancho-buttercup/30"
                            >
                                {{ deliveryError }}
                            </p>
                        </Transition>

                        <!-- DELIVERY ADDRESS ERROR -->
                        <Transition name="fade-up">
                            <p
                                v-if="hasError('delivery_address')"
                                class="text-sm font-medium text-rose-600"
                            >
                                {{ getError('delivery_address') }}
                            </p>
                        </Transition>
                    </div>

                    <!-- COMMENT -->
                    <BaseTextarea
                        ref="commentRef"
                        v-model="form.customer_comment"
                        :error="errors.customer_comment"
                        @input-start="form.clearErrors('customer_comment')"
                        :disabled="form.processing"
                        label="Комментарий к заказу"
                        placeholder="Ваш комментарий для модератора или доставщика"
                        :max-height="500"
                        theme="light"
                    />
                </section>

                <!-- RIGHT -->
                <aside
                    v-scroll-reveal
                    class="shadow-sm sticky right-0 top-0 rounded-2xl border border-rancho-ink-200 bg-white p-6"
                    aria-label="Ваш заказ"
                >
                    <h2
                        class="mb-4 text-base font-black uppercase tracking-wider text-rancho-forest"
                    >
                        Ваш заказ
                    </h2>

                    <div class="mb-4 space-y-2">
                        <div
                            v-for="item in cart.items"
                            :key="item.variant_id"
                            class="flex justify-between gap-2 text-sm"
                        >
                            <span class="text-rancho-ink-700">
                                {{ item.name }} × {{ item.quantity }}
                            </span>
                            <span class="font-bold text-rancho-forest">
                                {{ formatMoney(item.price * item.quantity) }}
                            </span>
                        </div>
                    </div>

                    <!-- PICKUP / DELIVERY ROW -->
                    <Transition name="slide-fade" mode="out-in">
                        <div
                            v-if="isPickup"
                            key="pickup-row"
                            class="mt-2 flex justify-between gap-3 border-t border-rancho-ink-100 pt-3 text-sm text-rancho-ink-700"
                        >
                            <span>Самовывоз</span>
                            <span class="font-bold text-rancho-pine">бесплатно</span>
                        </div>

                        <div
                            v-else-if="deliveryPrice !== null"
                            key="delivery-row"
                            class="mt-2 flex justify-between gap-3 border-t border-rancho-ink-100 pt-3 text-sm text-rancho-ink-700"
                        >
                            <span>Доставка</span>
                            <span class="font-bold text-rancho-forest">
                                <template v-if="deliveryPrice === 0">бесплатно</template>
                                <template v-else>{{ formatMoney(deliveryPrice) }}</template>
                            </span>
                        </div>
                    </Transition>

                    <!-- PROMO CODE -->
                    <div class="mt-4 border-t border-rancho-ink-100 pt-4">
                        <Transition name="slide-fade" mode="out-in">
                            <div v-if="!appliedPromo">
                                <BaseInput
                                    v-model="promoCode"
                                    label="Промокод"
                                    placeholder="Введите код"
                                    theme="light"
                                    :error="page.props.errors.promo_code"
                                    :disabled="isChecking || form.processing"
                                />

                                <button
                                    type="button"
                                    @click="applyPromo"
                                    :disabled="!promoCode.trim() || isChecking"
                                    class="mt-2 w-full rounded-xl bg-rancho-pine py-2.5 text-xs font-bold uppercase tracking-wider text-white transition-colors hover:bg-rancho-forest disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    {{ isChecking ? 'Проверка...' : 'Применить' }}
                                </button>
                            </div>

                            <div
                                v-else
                                class="flex items-center justify-between rounded-xl bg-rancho-pine/10 px-3 py-2.5"
                            >
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-bold text-rancho-forest">
                                        {{ appliedPromo.code }}
                                    </p>
                                    <p class="text-xs font-black text-rancho-ink-600">
                                        −{{ formatMoney(appliedPromo.discount) }}
                                    </p>
                                </div>

                                <button
                                    type="button"
                                    @click="removePromo"
                                    :disabled="form.processing"
                                    class="text-xs font-bold text-rancho-ember hover:underline"
                                >
                                    Убрать
                                </button>
                            </div>
                        </Transition>
                    </div>

                    <Transition name="fade-up">
                        <div
                            v-if="cart.items.length === 0"
                            class="py-3 text-center text-sm text-rancho-ink-500"
                        >
                            Корзина пуста
                        </div>
                    </Transition>

                    <div
                        class="mt-2 flex items-baseline justify-between gap-2 border-t border-rancho-ink-200 pt-4"
                    >
                        <span class="text-sm font-bold text-rancho-ink-600">Итого</span>
                        <span class="text-xl font-black text-rancho-forest sm:text-2xl">
                            {{ computedTotalPrice }}
                        </span>
                    </div>

                    <BaseSubmitButton
                        @click="submit"
                        :processing="form.processing"
                        :disabled="cart.items.length === 0 || (!isPickup && !isDeliveryValid)"
                        :label="form.processing ? 'Оформление...' : 'Оформить заказ'"
                        class="mt-10 w-full"
                        :icon="ShoppingCartIcon"
                        theme="dark"
                    />

                    <!-- CART ERROR -->
                    <Transition name="fade-up">
                        <div
                            v-if="$page.props.errors.cart"
                            class="mt-4 rounded-xl border border-rose-300 bg-rose-50 p-3 text-sm font-medium text-rose-700"
                            role="alert"
                        >
                            {{ $page.props.errors.cart }}
                        </div>
                    </Transition>
                </aside>
            </div>
        </div>
    </AppContainer>
</template>

<style scoped>
    .slide-fade-enter-active {
        transition:
            opacity 0.25s ease,
            transform 0.25s ease;
    }
    .slide-fade-leave-active {
        transition:
            opacity 0.15s ease,
            transform 0.15s ease;
    }
    .slide-fade-enter-from {
        opacity: 0;
        transform: translateY(8px);
    }
    .slide-fade-leave-to {
        opacity: 0;
        transform: translateY(-8px);
    }

    .fade-scale-enter-active {
        transition:
            opacity 0.3s ease,
            transform 0.3s ease;
    }
    .fade-scale-leave-active {
        transition:
            opacity 0.2s ease,
            transform 0.2s ease;
    }
    .fade-scale-enter-from {
        opacity: 0;
        transform: scale(0.98);
    }
    .fade-scale-leave-to {
        opacity: 0;
        transform: scale(0.98);
    }

    .fade-up-enter-active {
        transition:
            opacity 0.25s ease,
            transform 0.25s ease;
    }
    .fade-up-leave-active {
        transition:
            opacity 0.15s ease,
            transform 0.15s ease;
    }
    .fade-up-enter-from,
    .fade-up-leave-to {
        opacity: 0;
        transform: translateY(6px);
    }

    @media (prefers-reduced-motion: reduce) {
        .slide-fade-enter-active,
        .slide-fade-leave-active,
        .fade-scale-enter-active,
        .fade-scale-leave-active,
        .fade-up-enter-active,
        .fade-up-leave-active {
            transition: none;
        }
    }
</style>
