<script setup lang="ts">
    import { computed, nextTick, ref } from 'vue';

    import { Head, useForm, usePage } from '@inertiajs/vue3';

    import BaseInput from '@/Components/UI/BaseInput.vue';
    import BaseSubmitButton from '@/Components/UI/BaseSubmitButton.vue';
    import BaseSwitch from '@/Components/UI/BaseSwitch.vue';
    import BaseTextarea from '@/Components/UI/BaseTextarea.vue';
    import MainLayout from '@/Layouts/MainLayout.vue';
    import { useCartStore } from '@/stores/cart';
    import { DeliveryDraft, SharedData } from '@/types';
    import { formatMoney } from '@/utils/format';

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
        // при успехе:
        delivery_price?: number;
        zone?: DeliveryZone;
        distance_to_route?: number;
        distance_to_farm?: number;
        // при ошибке:
        error?: string;
    }

    /**
     * PROPS & INERTIA SHARED DATA
     */
    interface Props {
        delivery_draft: DeliveryDraft | null;
        delivery_result: DeliveryResult | null;
    }

    const props = defineProps<Props>();

    const cart = useCartStore();
    const page = usePage<SharedData>();

    const user = computed(() => page.props.auth.user);

    const delivery = computed(() => props.delivery_draft ?? page.props.delivery_draft ?? null);
    const deliveryResult = computed(() => props.delivery_result ?? null);

    /**
     * REFS FOR FOCUS ON ERROR
     */
    const nameRef = ref<InstanceType<typeof BaseInput> | HTMLInputElement | null>(null);
    const phoneRef = ref<InstanceType<typeof BaseInput> | HTMLInputElement | null>(null);
    const commentRef = ref<InstanceType<typeof BaseTextarea> | HTMLTextAreaElement | null>(null);

    /**
     * FORM
     */
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

    /**
     * DELIVERY STATE
     */
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

    /** Стоимость доставки с учётом бесплатной доставки от free_from */
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

    /** До бесплатной доставки осталось */
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
        formatMoney(cart.totalPrice + (deliveryPrice.value ?? 0)),
    );
    /**
     * UI ACTIONS
     */
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
        window.location.href = '/delivery';
    }

    /**
     * ERRORS
     */
    const errors = computed(() => form.errors);

    function hasError(field: keyof CheckoutForm) {
        return !!errors.value[field];
    }

    function getError(field: keyof CheckoutForm) {
        return errors.value[field];
    }

    /**
     * SUBMIT ORDER
     */
    function focusElement(el: any) {
        if (!el) return;
        const target = el.$el ?? el;
        target.focus?.();
        target.scrollIntoView?.({ behavior: 'smooth', block: 'center' });
    }

    function submit() {
        if (cart.items.length === 0) return;

        form.transform((data) => ({
            ...data,
            items: cart.items,
        })).post(route('checkout.store'), {
            preserveScroll: true,
            onSuccess: (responseData) => {
                console.log('Ура!', responseData);
                cart.clear();
            },
            onError: async (errs) => {
                await nextTick();
                console.log('submit error', errs);

                const firstErrorField = Object.keys(errs)[0] as keyof CheckoutForm;

                switch (firstErrorField) {
                    case 'customer_name':
                        focusElement(nameRef.value);
                        break;
                    case 'customer_phone':
                        focusElement(phoneRef.value);
                        break;
                    case 'customer_comment':
                        focusElement(commentRef.value);
                        break;
                }
            },
        });
    }
</script>

<template>
    <AppContainer class="min-h-screen bg-rancho-paper font-sans">
        <Head title="Оформление заказа" />

        <div class="container mx-auto py-10">
            <!-- HEADER -->
            <header class="mb-10">
                <h1 class="text-3xl font-bold text-rancho-forest">Оформление заказа</h1>
                <p class="mt-2 text-rancho-olive">Доставка или самовывоз</p>
            </header>

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                <!-- LEFT -->
                <section class="space-y-6 lg:col-span-2">
                    <BaseInput
                        ref="nameRef"
                        label="Имя"
                        :required="true"
                        placeholder="Введите своё имя"
                        v-model="form.customer_name"
                        :error="errors.customer_name"
                    />

                    <BaseInput
                        ref="phoneRef"
                        type="tel"
                        :required="true"
                        label="Телефон"
                        placeholder="Введите номер телефона"
                        v-model="form.customer_phone"
                        :error="errors.customer_phone"
                    />

                    <BaseSwitch
                        v-if="!user"
                        v-model="form.create_account"
                        :error="errors.create_account"
                        label="Создать аккаунт"
                        active-text="Аккаунт будет создан автоматически"
                        inactive-text="Аккаунт не будет создан автоматически"
                    />

                    <!-- DELIVERY BLOCK -->
                    <div class="space-y-3 rounded-xl border bg-white p-4">
                        <!-- CASE: PICKUP ↔ DELIVERY -->
                        <Transition name="slide-fade" mode="out-in">
                            <!-- CASE: PICKUP -->
                            <div v-if="isPickup" key="pickup">
                                <div class="text-sm font-medium text-gray-700">📦 Самовывоз</div>
                                <p class="mt-1 text-sm text-gray-500">Адрес доставки не выбран</p>

                                <button
                                    type="button"
                                    @click="goToDeliveryPage"
                                    class="mt-3 w-full rounded-lg bg-green-700 px-3 py-2 text-white transition-colors hover:bg-green-800"
                                >
                                    Выбрать адрес доставки
                                </button>
                            </div>

                            <!-- CASE: DELIVERY -->
                            <div v-else key="delivery">
                                <div class="text-sm font-medium text-green-700">
                                    🚚 Доставка выбрана
                                </div>

                                <p class="mt-1 text-sm">{{ form.delivery_address }}</p>

                                <!-- РЕЗУЛЬТАТ РАСЧЁТА -->
                                <Transition name="fade-scale" appear>
                                    <div
                                        v-if="deliveryResult"
                                        class="mt-3 rounded-lg border p-3 text-sm"
                                        :class="
                                            deliveryResult.is_valid
                                                ? 'border-green-200 bg-green-50 text-green-900'
                                                : 'border-red-200 bg-red-50 text-red-700'
                                        "
                                    >
                                        <template v-if="deliveryResult.is_valid">
                                            <div class="flex justify-between">
                                                <span>Стоимость доставки</span>
                                                <span class="font-medium">
                                                    <template v-if="deliveryPrice === 0"
                                                        >бесплатно</template
                                                    >
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
                                                class="mt-1 flex justify-between text-gray-600"
                                            >
                                                <span>Зона</span>
                                                <span>{{ deliveryResult.zone.name }}</span>
                                            </div>

                                            <div
                                                v-if="deliveryResult.distance_to_route != null"
                                                class="mt-1 flex justify-between text-gray-600"
                                            >
                                                <span>До маршрута</span>
                                                <span
                                                    >{{
                                                        Math.round(deliveryResult.distance_to_route)
                                                    }}
                                                    м</span
                                                >
                                            </div>

                                            <div
                                                v-if="deliveryResult.distance_to_farm != null"
                                                class="mt-1 flex justify-between text-gray-600"
                                            >
                                                <span>От фермы</span>
                                                <span
                                                    >{{
                                                        Math.round(deliveryResult.distance_to_farm)
                                                    }}
                                                    м</span
                                                >
                                            </div>

                                            <div
                                                v-if="amountUntilFreeDelivery !== null"
                                                class="mt-2 rounded bg-amber-50 p-2 text-xs text-amber-800"
                                            >
                                                До бесплатной доставки осталось
                                                <strong>{{
                                                    formatMoney(amountUntilFreeDelivery)
                                                }}</strong>
                                            </div>
                                        </template>

                                        <template v-else>
                                            ⚠️
                                            {{ deliveryResult.error ?? 'Адрес вне зоны доставки' }}
                                        </template>
                                    </div>
                                </Transition>

                                <div class="mt-3 flex gap-2">
                                    <button
                                        type="button"
                                        @click="goToDeliveryPage"
                                        class="flex-1 rounded-lg border px-3 py-2 hover:bg-gray-50"
                                    >
                                        Изменить
                                    </button>

                                    <button
                                        type="button"
                                        @click="togglePickup"
                                        class="flex-1 rounded-lg border border-red-300 px-3 py-2 text-red-700 transition-colors hover:bg-red-50"
                                    >
                                        Самовывоз
                                    </button>
                                </div>
                            </div>
                        </Transition>

                        <!-- CART ERROR -->
                        <Transition name="fade-up">
                            <div
                                v-if="$page.props.errors.cart"
                                class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"
                            >
                                ⚠️ {{ $page.props.errors.cart }}
                            </div>
                        </Transition>

                        <!-- DELIVERY ERROR -->
                        <Transition name="fade-up">
                            <div
                                v-if="$page.props.errors.delivery"
                                class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"
                            >
                                ⚠️ {{ $page.props.errors.delivery }}
                            </div>
                        </Transition>

                        <!-- DELIVERY VALIDATION -->
                        <Transition name="fade-up">
                            <p v-if="deliveryError" class="mt-1 text-sm text-amber-700">
                                {{ deliveryError }}
                            </p>
                        </Transition>

                        <!-- DELIVERY ADDRESS ERROR -->
                        <Transition name="fade-up">
                            <p
                                v-if="hasError('delivery_address')"
                                class="mt-1 text-sm text-red-600"
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
                        label="Комментарий к заказу"
                        placeholder="Ваш комментарий для модератора или доставщика"
                        :max-height="500"
                    />
                </section>

                <!-- RIGHT -->
                <aside class="shadow-sm rounded-2xl bg-white p-6">
                    <h2 class="mb-4 font-bold text-gray-900">Ваш заказ</h2>

                    <div class="mb-4 space-y-2">
                        <div
                            v-for="item in cart.items"
                            :key="item.variant_id"
                            class="flex justify-between text-sm"
                        >
                            <span>{{ item.name }} × {{ item.quantity }}</span>
                            <span class="font-medium">{{
                                formatMoney(item.price * item.quantity)
                            }}</span>
                        </div>
                    </div>

                    <!-- PICKUP / DELIVERY ROW -->
                    <Transition name="slide-fade" mode="out-in">
                        <div
                            v-if="isPickup"
                            key="pickup-row"
                            class="mt-2 flex justify-between border-t pt-2 text-sm text-gray-700"
                        >
                            <span>Самовывоз</span>
                            <span>бесплатно</span>
                        </div>

                        <div
                            v-else-if="deliveryPrice !== null"
                            key="delivery-row"
                            class="mt-2 flex justify-between border-t pt-2 text-sm text-gray-700"
                        >
                            <span>Доставка</span>
                            <span>
                                <template v-if="deliveryPrice === 0">бесплатно</template>
                                <template v-else>{{ formatMoney(deliveryPrice) }}</template>
                            </span>
                        </div>
                    </Transition>

                    <Transition name="fade-up">
                        <div v-if="cart.items.length === 0" class="text-sm text-gray-400">
                            Корзина пуста
                        </div>
                    </Transition>

                    <div class="flex justify-between border-t pt-4 font-bold text-gray-900">
                        <span>Итого</span>
                        <span>{{ computedTotalPrice }}</span>
                    </div>

                    <BaseSubmitButton
                        @click="submit"
                        :processing="form.processing"
                        :disabled="cart.items.length === 0 || (!isPickup && !isDeliveryValid)"
                        :label="form.processing ? 'Оформление...' : 'Оформить заказ'"
                        class="mt-6 w-full"
                    />
                </aside>
            </div>
        </div>
    </AppContainer>
</template>

<style scoped>
    /* Slide + fade для переключения pickup/delivery */
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

    /* Fade + лёгкий scale для блока расчёта */
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

    /* Fade-up для подсказок и ошибок */
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
</style>
