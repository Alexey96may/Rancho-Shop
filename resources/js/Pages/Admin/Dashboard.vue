<script setup lang="ts">
    import { Head } from '@inertiajs/vue3';
    import { usePage } from '@inertiajs/vue3';

    import {
        ChatBubbleLeftRightIcon,
        InboxIcon,
        PresentationChartLineIcon,
        ShoppingCartIcon,
        TruckIcon,
        UsersIcon,
    } from '@heroicons/vue/24/outline';

    import AdminDashboardCard from '@/Components/Admin/Cards/DashboardCard.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { SharedData } from '@/types';

    const props = defineProps<{
        stats: {
            products_count: number;
            orders_pending: number;
            new_comments: number;
            total_users: number;
        };
    }>();

    const can = usePage<SharedData>().props.can;
</script>

<template>
    <Head title="Панель управления" />

    <AdminLayout>
        <template #header>Дашборд</template>

        <section aria-labelledby="quick-stats-heading">
            <h2 id="quick-stats-heading" class="sr-only">Быстрая статистика</h2>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                <AdminDashboardCard
                    v-if="can.manageOrders"
                    title="Заказы"
                    description="Управление продажами и статусами"
                    :href="route('admin.orders.index')"
                    :icon="ShoppingCartIcon"
                    :count="stats.orders_pending"
                />

                <AdminDashboardCard
                    v-if="can.manageProducts"
                    title="Продукты"
                    description="Склад, цены и наличие"
                    :href="route('admin.products.index')"
                    :icon="InboxIcon"
                    :count="stats.products_count"
                />

                <AdminDashboardCard
                    v-if="can.manageComments"
                    title="Отзывы"
                    description="Модерация обратной связи"
                    :href="route('admin.comments.index')"
                    :icon="ChatBubbleLeftRightIcon"
                    :count="stats.new_comments"
                />

                <AdminDashboardCard
                    v-if="can.manageUsers"
                    title="Персонал"
                    description="Права доступа и пользователи"
                    :href="route('admin.users.index')"
                    :icon="UsersIcon"
                    :count="stats.total_users"
                />
            </div>
        </section>

        <section class="mt-12" aria-labelledby="activity-heading">
            <div class="mb-6 flex items-center justify-between">
                <h2
                    id="activity-heading"
                    class="text-xl font-black uppercase tracking-widest text-white"
                >
                    Аналитика Ранчо
                </h2>
                <div class="flex gap-2">
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 px-3 py-1 text-[10px] font-black uppercase tracking-wider text-emerald-400"
                    >
                        <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-500"></span>
                        Live
                    </span>
                </div>
            </div>

            <div
                class="flex flex-col items-center justify-center rounded-3xl border border-dashed border-slate-800 bg-slate-900/30 p-12 text-center"
            >
                <PresentationChartLineIcon class="mb-4 h-12 w-12 text-slate-700" />
                <p class="font-medium text-slate-500">
                    Модуль аналитики будет доступен после <br />
                    накопления первых данных о продажах.
                </p>
            </div>
        </section>
    </AdminLayout>
</template>
