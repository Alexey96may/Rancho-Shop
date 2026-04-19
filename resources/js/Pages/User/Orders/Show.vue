<script setup lang="ts">
    import { Head, Link } from '@inertiajs/vue3';

    import {
        CheckCircle2Icon,
        ChevronLeftIcon,
        MapPinIcon,
        PackageIcon,
        TruckIcon,
    } from 'lucide-vue-next';

    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
    import type { Order } from '@/types';

    const props = defineProps<{
        order: { data: Order };
    }>();

    // Форматирование для мета-данных (например, время доставки)
    const metaInfo = props.order.data.delivery_meta;
</script>

<template>
    <Head :title="'Заказ №' + order.data.id" />

    <AuthenticatedLayout>
        <main class="mx-auto max-w-6xl space-y-8 px-4 py-10" role="main">
            <Link
                :href="route('user.orders.index')"
                class="group inline-flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-slate-400 transition-colors hover:text-slate-900"
            >
                <ChevronLeftIcon class="h-4 w-4 transition-transform group-hover:-translate-x-1" />
                Назад к истории
            </Link>

            <header
                class="flex flex-col items-start justify-between gap-6 md:flex-row md:items-center"
            >
                <div class="space-y-2">
                    <h1 class="text-4xl font-black uppercase tracking-tighter text-slate-900">
                        Заказ №{{ order.data.id }}
                    </h1>
                    <p class="font-medium text-slate-500">Оформлен {{ order.data.created_at }}</p>
                </div>
                <div
                    class="shadow-xl rounded-2xl bg-slate-900 px-6 py-3 text-xs font-black uppercase tracking-widest text-white"
                >
                    Статус: {{ order.data.status }}
                </div>
            </header>

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                <div class="space-y-6 lg:col-span-2">
                    <section
                        class="shadow-sm overflow-hidden rounded-[3rem] border border-slate-200 bg-white"
                    >
                        <div class="flex items-center gap-3 border-b border-slate-100 p-8">
                            <PackageIcon class="h-5 w-5 text-orange-500" />
                            <h3 class="text-sm font-black uppercase tracking-widest">
                                Состав заказа
                            </h3>
                        </div>

                        <div class="divide-y divide-slate-100">
                            <div
                                v-for="item in order.data.items"
                                :key="item.id"
                                class="group flex items-center justify-between gap-4 p-8"
                            >
                                <div class="space-y-1">
                                    <h4
                                        class="font-bold text-slate-900 transition-colors group-hover:text-orange-600"
                                    >
                                        {{ item.product_name }}
                                    </h4>
                                    <p
                                        class="text-xs font-bold uppercase tracking-tighter text-slate-400"
                                    >
                                        {{ item.quantity }} {{ item.unit.name }} ×
                                        {{ item.unit_price }} ₽
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="font-black text-slate-900">{{ item.subtotal }} ₽</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between bg-slate-50 p-8">
                            <span
                                class="text-xs font-black uppercase tracking-widest text-slate-400"
                                >Итого к оплате</span
                            >
                            <span class="text-3xl font-black text-slate-900"
                                >{{ order.data.total_price }} ₽</span
                            >
                        </div>
                    </section>
                </div>

                <div class="space-y-6">
                    <section
                        class="shadow-sm space-y-6 rounded-[3rem] border border-slate-200 bg-white p-8"
                    >
                        <div class="flex items-center gap-3">
                            <TruckIcon class="h-5 w-5 text-orange-500" />
                            <h3 class="text-sm font-black uppercase tracking-widest">Доставка</h3>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <p
                                    class="mb-1 text-[10px] font-black uppercase tracking-widest text-slate-400"
                                >
                                    Адрес
                                </p>
                                <p class="font-bold leading-tight text-slate-900">
                                    {{
                                        order.data.is_pickup
                                            ? 'Самовывоз из питомника'
                                            : order.data.delivery_address
                                    }}
                                </p>
                            </div>

                            <div v-if="metaInfo" class="space-y-2 rounded-2xl bg-slate-50 p-4">
                                <div
                                    v-for="(value, key) in metaInfo"
                                    :key="key"
                                    class="flex justify-between text-xs font-bold"
                                >
                                    <span class="uppercase tracking-tighter text-slate-400"
                                        >{{ key }}:</span
                                    >
                                    <span class="text-slate-900">{{ value }}</span>
                                </div>
                            </div>

                            <div
                                v-if="order.data.delivery_validated"
                                class="flex items-center gap-2 text-emerald-600"
                            >
                                <CheckCircle2Icon class="h-4 w-4" />
                                <span class="text-[10px] font-black uppercase tracking-widest"
                                    >Проверено менеджером</span
                                >
                            </div>
                        </div>

                        <div
                            v-if="order.data.delivery_lat"
                            class="group relative flex aspect-video items-center justify-center overflow-hidden rounded-[2rem] border-2 border-dashed border-slate-200 bg-slate-100"
                        >
                            <MapPinIcon
                                class="relative z-10 h-8 w-8 text-slate-300 transition-colors group-hover:text-orange-500"
                            />
                            <p
                                class="absolute bottom-4 text-[9px] font-black uppercase text-slate-400"
                            >
                                Карта временно недоступна
                            </p>
                        </div>
                    </section>
                </div>
            </div>
        </main>
    </AuthenticatedLayout>
</template>
