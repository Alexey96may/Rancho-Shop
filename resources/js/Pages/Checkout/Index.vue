<script setup lang="ts">
    import { computed } from 'vue';

    import { Head, useForm, usePage } from '@inertiajs/vue3';

    import MainLayout from '@/Layouts/MainLayout.vue';
    import { useCartStore } from '@/stores/cart';

    defineOptions({ layout: MainLayout });

    const cart = useCartStore();
    const page = usePage();

    type CheckoutForm = {
        customer_name: string;
        customer_phone: string;
        delivery_address: string;
        customer_comment: string;
    };

    const form = useForm<CheckoutForm>({
        customer_name: '',
        customer_phone: '',
        delivery_address: '',
        customer_comment: '',
    });

    const totalPrice = computed(() => cart.totalPrice);

    // backend errors mapping (Inertia validation + exceptions)
    const errors = computed(() => form.errors);

    // helper: check field error
    function hasError(field: keyof CheckoutForm) {
        return !!errors.value[field];
    }

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
                <h1 class="font-header text-3xl font-bold text-rancho-forest">Оформление заказа</h1>

                <p class="mt-2 text-rancho-olive">Проверьте товары и заполните данные доставки</p>
            </header>

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                <!-- LEFT: FORM -->
                <section class="space-y-6 lg:col-span-2">
                    <!-- NAME -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-rancho-forest">
                            Имя
                        </label>

                        <input
                            id="name"
                            v-model="form.customer_name"
                            type="text"
                            autocomplete="name"
                            aria-label="Имя клиента"
                            :aria-invalid="hasError('customer_name')"
                            aria-describedby="name-error"
                            class="mt-1 w-full rounded-xl border p-3 shadow-rancho-sm focus:outline-none focus:ring-2 focus:ring-rancho-pine"
                            :class="
                                hasError('customer_name') ? 'border-red-500' : 'border-gray-200'
                            "
                        />

                        <p
                            v-if="hasError('customer_name')"
                            id="name-error"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ errors.customer_name }}
                        </p>
                    </div>

                    <!-- PHONE -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-rancho-forest">
                            Телефон
                        </label>

                        <input
                            id="phone"
                            v-model="form.customer_phone"
                            type="tel"
                            autocomplete="tel"
                            aria-label="Телефон"
                            :aria-invalid="hasError('customer_phone')"
                            aria-describedby="phone-error"
                            class="mt-1 w-full rounded-xl border p-3 shadow-rancho-sm focus:outline-none focus:ring-2 focus:ring-rancho-pine"
                            :class="
                                hasError('customer_phone') ? 'border-red-500' : 'border-gray-200'
                            "
                        />

                        <p
                            v-if="hasError('customer_phone')"
                            id="phone-error"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ errors.customer_phone }}
                        </p>
                    </div>

                    <!-- ADDRESS -->
                    <div>
                        <label for="address" class="block text-sm font-medium text-rancho-forest">
                            Адрес доставки
                        </label>

                        <input
                            id="address"
                            v-model="form.delivery_address"
                            type="text"
                            autocomplete="street-address"
                            aria-label="Адрес доставки"
                            :aria-invalid="hasError('delivery_address')"
                            aria-describedby="address-error"
                            class="mt-1 w-full rounded-xl border p-3 shadow-rancho-sm focus:outline-none focus:ring-2 focus:ring-rancho-pine"
                            :class="
                                hasError('delivery_address') ? 'border-red-500' : 'border-gray-200'
                            "
                        />

                        <p
                            v-if="hasError('delivery_address')"
                            id="address-error"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ errors.delivery_address }}
                        </p>
                    </div>

                    <!-- COMMENT -->
                    <div>
                        <label for="comment" class="block text-sm font-medium text-rancho-forest">
                            Комментарий
                        </label>

                        <textarea
                            id="comment"
                            v-model="form.customer_comment"
                            rows="3"
                            aria-label="Комментарий к заказу"
                            class="mt-1 w-full rounded-xl border p-3 shadow-rancho-sm focus:outline-none focus:ring-2 focus:ring-rancho-pine"
                        />
                    </div>
                </section>

                <!-- RIGHT: SUMMARY -->
                <aside class="h-fit rounded-2xl bg-white p-6 shadow-rancho-sm">
                    <h2 class="mb-4 text-lg font-bold text-rancho-forest">Ваш заказ</h2>

                    <!-- ITEMS -->
                    <div class="mb-6 space-y-3">
                        <div
                            v-for="item in cart.items"
                            :key="item.product_id"
                            class="flex justify-between text-sm"
                        >
                            <span class="text-rancho-olive">
                                {{ item.name }} × {{ item.quantity }}
                            </span>

                            <span class="font-medium text-rancho-forest">
                                {{ item.price * item.quantity }} ₽
                            </span>
                        </div>
                    </div>

                    <!-- EMPTY STATE -->
                    <div v-if="cart.items.length === 0" class="mb-4 text-sm text-gray-400">
                        Корзина пуста
                    </div>

                    <!-- TOTAL -->
                    <div class="flex justify-between border-t pt-4 font-bold text-rancho-forest">
                        <span>Итого</span>
                        <span>{{ totalPrice }} ₽</span>
                    </div>

                    <!-- SUBMIT -->
                    <button
                        @click="submit"
                        :disabled="form.processing || cart.items.length === 0"
                        class="mt-6 w-full rounded-xl bg-rancho-pine py-3 font-semibold text-white transition hover:bg-rancho-olive disabled:cursor-not-allowed disabled:opacity-50"
                        aria-label="Оформить заказ"
                    >
                        {{ form.processing ? 'Оформление...' : 'Оформить заказ' }}
                    </button>
                </aside>
            </div>
        </div>
    </div>
</template>
