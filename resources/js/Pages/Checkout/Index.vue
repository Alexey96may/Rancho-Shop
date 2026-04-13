<script setup lang="ts">
    import { computed } from 'vue';

    import { Head, useForm, usePage } from '@inertiajs/vue3';

    import MainLayout from '@/Layouts/MainLayout.vue';
    import { useCartStore } from '@/stores/cart';

    defineOptions({ layout: MainLayout });

    type DeliveryDraft = {
        address: string;
        point: { lat: number; lng: number } | null;
        is_valid: boolean;
    };

    type PageProps = {
        deliveryDraft: DeliveryDraft | null;
    };

    const cart = useCartStore();
    const page = usePage<PageProps>();

    const delivery = computed(() => page.props.deliveryDraft);

    /**
     * FORM
     */
    type CheckoutForm = {
        customer_name: string;
        customer_phone: string;
        delivery_address: string | null;
        customer_comment: string;
    };

    const form = useForm<CheckoutForm>({
        customer_name: '',
        customer_phone: '',
        delivery_address: delivery.value?.address ?? null,
        customer_comment: '',
    });

    /**
     * DELIVERY STATE
     */
    const isPickup = computed(() => !delivery.value);

    /**
     * UI ACTIONS
     */
    function usePickup() {
        form.delivery_address = null;
    }

    function goToDeliveryPage() {
        window.location.href = '/delivery';
    }

    /**
     * TOTAL
     */
    const totalPrice = computed(() => cart.totalPrice);

    /**
     * ERRORS
     */
    const errors = computed(() => form.errors);

    function hasError(field: keyof CheckoutForm) {
        return !!errors.value[field];
    }

    /**
     * VALIDATION
     */
    const isDeliveryValid = computed(() => {
        return delivery.value?.is_valid ?? false;
    });

    /**
     * SUBMIT ORDER
     */
    function submit() {
        if (cart.items.length === 0) return;

        form.post(route('checkout.store'), {
            preserveScroll: true,
            onSuccess: () => {
                cart.clear();
            },
        });
    }
</script>

<template>
    <Head title="Оформление заказа" />

    <div class="min-h-screen bg-rancho-paper font-sans">
        <div class="container mx-auto py-10">
            <!-- HEADER -->
            <header class="mb-10">
                <h1 class="text-3xl font-bold text-rancho-forest">Оформление заказа</h1>

                <p class="mt-2 text-rancho-olive">Доставка или самовывоз</p>
            </header>

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                <!-- LEFT -->
                <section class="space-y-6 lg:col-span-2">
                    <!-- NAME -->
                    <div>
                        <label class="block text-sm font-medium">Имя</label>
                        <input
                            v-model="form.customer_name"
                            class="mt-1 w-full rounded-xl border p-3"
                        />
                    </div>

                    <!-- PHONE -->
                    <div>
                        <label class="block text-sm font-medium">Телефон</label>
                        <input
                            v-model="form.customer_phone"
                            class="mt-1 w-full rounded-xl border p-3"
                        />
                    </div>

                    <!-- DELIVERY BLOCK -->
                    <div class="space-y-3 rounded-xl border bg-white p-4">
                        <!-- CASE: PICKUP -->
                        <div v-if="isPickup">
                            <div class="text-sm font-medium text-gray-700">📦 Самовывоз</div>

                            <p class="mt-1 text-sm text-gray-500">Адрес доставки не выбран</p>

                            <button
                                type="button"
                                @click="goToDeliveryPage"
                                class="mt-3 w-full rounded-lg bg-green-700 px-3 py-2 text-white"
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
                                {{ delivery?.address }}
                            </p>

                            <div class="mt-3 flex gap-2">
                                <button
                                    type="button"
                                    @click="goToDeliveryPage"
                                    class="flex-1 rounded-lg border px-3 py-2"
                                >
                                    Изменить
                                </button>

                                <button
                                    type="button"
                                    @click="usePickup"
                                    class="flex-1 rounded-lg border border-red-300 px-3 py-2 text-red-600"
                                >
                                    Самовывоз
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- COMMENT -->
                    <div>
                        <label class="block text-sm font-medium">Комментарий</label>
                        <textarea
                            v-model="form.customer_comment"
                            class="mt-1 w-full rounded-xl border p-3"
                        />
                    </div>
                </section>

                <!-- RIGHT -->
                <aside class="rounded-2xl bg-white p-6">
                    <h2 class="mb-4 font-bold">Ваш заказ</h2>

                    <div class="mb-4 space-y-2">
                        <div
                            v-for="item in cart.items"
                            :key="item.product_id"
                            class="flex justify-between text-sm"
                        >
                            <span>{{ item.name }} × {{ item.quantity }}</span>
                            <span>{{ item.price * item.quantity }} ₽</span>
                        </div>
                    </div>

                    <div v-if="cart.items.length === 0" class="text-sm text-gray-400">
                        Корзина пуста
                    </div>

                    <div class="flex justify-between border-t pt-4 font-bold">
                        <span>Итого</span>
                        <span>{{ totalPrice }} ₽</span>
                    </div>

                    <button
                        @click="submit"
                        :disabled="form.processing || cart.items.length === 0"
                        class="mt-6 w-full rounded-xl bg-green-700 py-3 text-white"
                    >
                        {{ form.processing ? 'Оформление...' : 'Оформить заказ' }}
                    </button>
                </aside>
            </div>
        </div>
    </div>
</template>
