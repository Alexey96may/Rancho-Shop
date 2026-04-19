<script setup lang="ts">
    import { computed, ref } from 'vue';

    import { Head, Link } from '@inertiajs/vue3';

    import { ChevronRightIcon, FilterIcon, PackageIcon } from 'lucide-vue-next';

    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
    import MainLayout from '@/Layouts/MainLayout.vue';
    import type { Order } from '@/types';

    defineOptions({ layout: MainLayout });

    const props = defineProps<{
        orders: { data: Order[] };
    }>();

    // Фильтрация на фронтенде (для мгновенной анимации)
    const activeFilter = ref('all');
    const statuses = [
        { id: 'all', label: 'Все' },
        { id: 'pending', label: 'Ожидают' },
        { id: 'processing', label: 'В работе' },
        { id: 'delivered', label: 'Выполнены' },
        { id: 'cancelled', label: 'Отменены' },
    ];

    const filteredOrders = computed(() => {
        if (activeFilter.value === 'all') return props.orders.data;
        return props.orders.data.filter((o) => o.status === activeFilter.value);
    });

    const statusMap = {
        pending: 'bg-slate-100 text-slate-600',
        processing: 'bg-blue-100 text-blue-600',
        shipped: 'bg-orange-100 text-orange-600',
        delivered: 'bg-emerald-100 text-emerald-600',
        cancelled: 'bg-rose-100 text-rose-600',
    };
</script>

<template>
    <Head title="Мои заказы" />
    <main class="mx-auto max-w-5xl space-y-8 px-4 py-10" role="main">
        <header class="flex flex-col justify-between gap-6 md:flex-row md:items-end">
            <div>
                <h1 class="text-3xl font-black uppercase tracking-tighter text-slate-900">
                    История заказов
                </h1>
                <p class="font-medium text-slate-500">Управляйте вашими покупками на Ранчо</p>
            </div>

            <nav
                class="no-scrollbar flex overflow-x-auto rounded-2xl bg-slate-100 p-1"
                role="tablist"
                aria-label="Фильтр заказов"
            >
                <button
                    v-for="status in statuses"
                    :key="status.id"
                    role="tab"
                    :aria-selected="activeFilter === status.id"
                    @click="activeFilter = status.id"
                    :class="[
                        'whitespace-nowrap rounded-xl px-4 py-2 text-[10px] font-black uppercase tracking-widest outline-none transition-all focus:ring-2 focus:ring-orange-500',
                        activeFilter === status.id
                            ? 'shadow-sm bg-white text-slate-900'
                            : 'text-slate-500 hover:text-slate-700',
                    ]"
                >
                    {{ status.label }}
                </button>
            </nav>
        </header>

        <div class="relative">
            <TransitionGroup name="list" tag="div" class="space-y-4">
                <div
                    v-for="order in filteredOrders"
                    :key="order.id"
                    class="group relative rounded-[2.5rem] border border-slate-200 bg-white p-6 transition-colors hover:border-orange-500/50"
                >
                    <div class="flex flex-col justify-between gap-6 md:flex-row md:items-center">
                        <div class="flex items-center gap-5">
                            <div
                                class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-50 text-slate-400 transition-all group-hover:bg-slate-900 group-hover:text-white"
                                aria-hidden="true"
                            >
                                <PackageIcon class="h-6 w-6" />
                            </div>
                            <div>
                                <h2 class="font-bold text-slate-900">Заказ №{{ order.id }}</h2>
                                <p
                                    class="text-xs font-black uppercase tracking-tighter text-slate-500"
                                >
                                    {{ order.created_at }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-8 md:justify-end">
                            <div class="text-right">
                                <p
                                    class="text-[10px] font-black uppercase tracking-widest text-slate-400"
                                >
                                    Сумма
                                </p>
                                <p class="font-bold text-slate-900">{{ order.total_price }} ₽</p>
                            </div>
                            <div
                                :class="[
                                    'rounded-2xl px-5 py-2 text-[10px] font-black uppercase tracking-widest',
                                    statusMap[order.status],
                                ]"
                            >
                                {{ order.status }}
                            </div>
                            <Link
                                :href="route('user.orders.show', order.id)"
                                class="rounded-2xl bg-slate-50 p-3 text-slate-400 outline-none transition-colors hover:text-orange-600 focus:ring-2 focus:ring-orange-500"
                                aria-label="Посмотреть детали заказа"
                            >
                                <ChevronRightIcon class="h-6 w-6" />
                            </Link>
                        </div>
                    </div>
                </div>
            </TransitionGroup>

            <div v-if="filteredOrders.length === 0" class="py-20 text-center">
                <p class="font-black uppercase tracking-widest text-slate-400">
                    Нет заказов с таким статусом
                </p>
            </div>
        </div>
    </main>
</template>

<style scoped>
    /* Анимация TransitionGroup */
    .list-enter-active,
    .list-leave-active {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .list-enter-from {
        opacity: 0;
        transform: translateY(20px) scale(0.95);
    }
    .list-leave-to {
        opacity: 0;
        transform: translateY(-20px) scale(0.95);
    }
    /* Чтобы элементы не прыгали при удалении/добавлении */
    .list-move {
        transition: transform 0.4s ease;
    }

    /* Скрытие скроллбара для фильтров */
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
