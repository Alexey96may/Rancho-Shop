<script setup lang="ts">
    import { computed, nextTick, ref } from 'vue';

    import { Head, useForm, usePage } from '@inertiajs/vue3';

    import BaseInput from '@/Components/UI/BaseInput.vue';
    import BaseSubmitButton from '@/Components/UI/BaseSubmitButton.vue';
    import BaseTextarea from '@/Components/UI/BaseTextarea.vue';
    import MainLayout from '@/Layouts/MainLayout.vue';
    import { useCartStore } from '@/stores/cart';
    import { AuthProps, DeliveryDraft, FlashPayload, Permission, SharedData } from '@/types';
    import { formatMoney } from '@/utils/format';

    defineOptions({ layout: MainLayout });

    /**
     * PROPS & INERTIA SHARED DATA
     */
    interface Props {
        delivery_draft: DeliveryDraft | null;
    }

    const props = defineProps<Props>();

    const cart = useCartStore();
    const page = usePage<SharedData>();

    const user = computed(() => page.props.auth.user);
    const delivery = computed(() => props.delivery_draft ?? page.props.delivery_draft ?? null);

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
    };

    const form = useForm<CheckoutForm>({
        customer_name: user.value?.data?.name ?? '',
        customer_phone: user.value?.data?.phone ?? '',
        delivery_address: delivery.value?.address ?? null,
        customer_comment: '',

        is_pickup: false,
        lat: delivery.value?.lat ?? null,
        lng: delivery.value?.lng ?? null,
    });

    /**
     * DELIVERY STATE
     */
    const isPickup = computed(() => form.delivery_address === null);

    const isDeliveryValid = computed(() => {
        return delivery.value?.is_valid ?? false;
    });

    const deliveryError = computed(() => {
        if (!isPickup.value && !isDeliveryValid.value) {
            return 'Выбранный адрес доставки недоступен';
        }
        return null;
    });

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
     * TOTAL
     */
    const computedTotalPrice = computed(() => formatMoney(cart.totalPrice));

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
    <div class="min-h-screen bg-rancho-paper font-sans">
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

                    <!-- DELIVERY BLOCK -->
                    <div class="space-y-3 rounded-xl border bg-white p-4">
                        <!-- CASE: PICKUP -->
                        <div v-if="isPickup">
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
                        <div v-else>
                            <div class="text-sm font-medium text-green-700">
                                🚚 Доставка выбрана
                            </div>

                            <p class="mt-1 text-sm">
                                {{ form.delivery_address }}
                            </p>

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

                        <div
                            v-if="$page.props.errors.cart"
                            class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"
                        >
                            ⚠️ {{ $page.props.errors.cart }}
                        </div>

                        <div
                            v-if="$page.props.errors.delivery"
                            class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"
                        >
                            ⚠️ {{ $page.props.errors.delivery }}
                        </div>

                        <p v-if="deliveryError" class="mt-1 text-sm text-amber-700">
                            {{ deliveryError }}
                        </p>

                        <p v-if="hasError('delivery_address')" class="mt-1 text-sm text-red-600">
                            {{ getError('delivery_address') }}
                        </p>
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
                            :key="item.product_id"
                            class="flex justify-between text-sm"
                        >
                            <span>{{ item.name }} × {{ item.quantity }}</span>
                            <span class="font-medium">{{
                                formatMoney(item.price * item.quantity)
                            }}</span>
                        </div>
                    </div>

                    <div v-if="cart.items.length === 0" class="text-sm text-gray-400">
                        Корзина пуста
                    </div>

                    <div class="flex justify-between border-t pt-4 font-bold text-gray-900">
                        <span>Итого</span>
                        <span>{{ computedTotalPrice }}</span>
                    </div>

                    <BaseSubmitButton
                        @click="submit"
                        :processing="form.processing"
                        :disabled="cart.items.length === 0 || (!isPickup && !isDeliveryValid)"
                        class="mt-6 w-full"
                    >
                        {{ form.processing ? 'Оформление...' : 'Оформить заказ' }}
                    </BaseSubmitButton>
                </aside>
            </div>
        </div>
    </div>
</template>
