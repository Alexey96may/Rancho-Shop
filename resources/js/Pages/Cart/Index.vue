<script setup lang="ts">
    import { onMounted } from 'vue';

    import { Link } from '@inertiajs/vue3';

    import { MinusIcon, PlusIcon, TrashIcon } from '@heroicons/vue/24/outline';

    import MainLayout from '@/Layouts/MainLayout.vue';
    import { useCartStore } from '@/stores/cart';

    defineOptions({ layout: MainLayout });

    const cart = useCartStore();

    // Форматирование цены (из копеек в рубли)
    const formatPrice = (price: number) => price.toLocaleString('ru-RU');

    const cartStore = useCartStore();

    onMounted(() => {
        cartStore.validate(true);
    });
</script>

<template>
    <div class="mx-auto max-w-4xl p-6">
        <h1 class="mb-8 text-3xl font-black uppercase tracking-tight text-slate-900">
            Ваша корзина
        </h1>

        <div v-if="cart.items.length > 0" class="grid gap-8 lg:grid-cols-3">
            <div class="space-y-4 lg:col-span-2">
                <div
                    v-for="item in cart.items"
                    :key="item.product_id"
                    class="shadow-sm transition-hover hover:shadow-md flex items-center gap-4 rounded-2xl border border-slate-100 bg-white p-4"
                >
                    <AppImage
                        :src="item.media"
                        :alt="item.name"
                        :type="'thumbnails'"
                        :class-name="'h-20 w-20 rounded-xl bg-slate-50 object-cover'"
                    />

                    <div class="flex-1">
                        <Link
                            :href="route('catalog.show', item)"
                            class="font-bold text-slate-900 hover:text-orange-600"
                        >
                            {{ item.name }}
                        </Link>
                        <p class="text-sm text-slate-500">
                            {{ formatPrice(item.price) }} ₽ / {{ item.unit }}
                        </p>
                    </div>

                    <div class="flex items-center gap-2 rounded-lg bg-slate-50 p-1">
                        <button
                            @click="cart.remove(item.product_id)"
                            class="p-1 hover:text-orange-600 disabled:opacity-30"
                        >
                            <MinusIcon class="h-4 w-4" />
                        </button>
                        <span class="w-8 text-center text-sm font-bold">{{ item.quantity }}</span>
                        <button
                            @click="cart.add(item)"
                            :disabled="item.quantity >= item.stock"
                            class="p-1 hover:text-orange-600 disabled:opacity-30"
                        >
                            <PlusIcon class="h-4 w-4" />
                        </button>
                    </div>

                    <div class="min-w-[80px] text-right">
                        <p class="font-bold text-slate-900">
                            {{ formatPrice(item.price * item.quantity) }} ₽
                        </p>
                    </div>

                    <button
                        @click="cart.destroy(item.product_id)"
                        class="text-slate-300 hover:text-red-500"
                    >
                        <TrashIcon class="h-5 w-5" />
                    </button>
                </div>
            </div>

            <div class="shadow-xl h-fit space-y-4 rounded-3xl bg-slate-900 p-6 text-white">
                <h2 class="text-xl font-bold">Итог заказа</h2>
                <div class="space-y-2 border-b border-slate-700 pb-4 text-sm opacity-80">
                    <div class="flex justify-between">
                        <span>Позиций:</span>
                        <span>{{ cart.totalItems }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Доставка:</span>
                        <span
                            class="text-[10px] font-bold uppercase tracking-widest text-emerald-400"
                            >Бесплатно</span
                        >
                    </div>
                </div>
                <div class="flex justify-between py-2 text-2xl font-black">
                    <span>Всего:</span>
                    <span>{{ formatPrice(cart.totalPrice) }} ₽</span>
                </div>

                <button
                    class="shadow-lg w-full rounded-xl bg-orange-600 py-4 font-bold uppercase tracking-widest shadow-orange-900/20 transition-all hover:bg-orange-500 active:scale-95"
                >
                    Оформить заказ
                </button>
            </div>
        </div>

        <div v-else class="flex flex-col items-center justify-center py-20 text-center">
            <div class="mb-6 rounded-full bg-slate-100 p-8 text-slate-300">
                <ShoppingCartIcon class="h-16 w-16" />
            </div>
            <h2 class="mb-2 text-2xl font-bold text-slate-900">В корзине пока пусто</h2>
            <p class="mb-8 text-slate-500">Посмотрите наши свежие овощи и саженцы!</p>
            <Link
                :href="route('catalog.index')"
                class="rounded-xl bg-slate-900 px-8 py-3 font-bold text-white hover:bg-orange-600"
            >
                Перейти в каталог
            </Link>
        </div>
    </div>
</template>
