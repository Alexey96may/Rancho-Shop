<script setup lang="ts">
    import { Head, usePage } from '@inertiajs/vue3';

    import {
        AcademicCapIcon,
        ChatBubbleLeftRightIcon,
        InboxIcon,
        PresentationChartLineIcon,
        ShoppingCartIcon,
        TicketIcon,
        UsersIcon,
    } from '@heroicons/vue/24/outline';

    import AdminDashboardCard from '@/Components/Admin/Cards/DashboardCard.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { SharedData } from '@/types';

    // В контроллере DashboardController нужно будет добавить эти ключи в stats
    const props = defineProps<{
        stats: {
            products_count: number;
            orders_pending: number;
            new_comments: number;
            total_users: number;
            active_animals?: number;
            active_promocodes?: number;
        };
    }>();

    const can = usePage<SharedData>().props.can;
</script>

<template>
    <Head title="Панель управления" />

    <AdminLayout>
        <template #header>Дашборд</template>

        <section>
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <AdminDashboardCard
                    v-if="can.manageOrders"
                    title="Заказы"
                    description="Ожидают обработки"
                    :href="route('admin.orders.index')"
                    :icon="ShoppingCartIcon"
                    :count="stats.orders_pending"
                />

                <AdminDashboardCard
                    v-if="can.manageProducts"
                    title="Продукты"
                    description="Всего в базе"
                    :href="route('admin.products.index')"
                    :icon="InboxIcon"
                    :count="stats.products_count"
                />

                <AdminDashboardCard
                    title="Животные"
                    description="Наше поголовье"
                    :href="route('admin.animals.index')"
                    :icon="AcademicCapIcon"
                    :count="stats.active_animals || 0"
                />

                <AdminDashboardCard
                    title="Промокоды"
                    description="Активные акции"
                    :href="route('admin.promocodes.index')"
                    :icon="TicketIcon"
                    :count="stats.active_promocodes || 0"
                />

                <AdminDashboardCard
                    v-if="can.manageComments"
                    title="Отзывы"
                    description="Новые сообщения"
                    :href="route('admin.comments.index')"
                    :icon="ChatBubbleLeftRightIcon"
                    :count="stats.new_comments"
                />

                <AdminDashboardCard
                    v-if="can.manageUsers"
                    title="Персонал"
                    description="Доступ к админке"
                    :href="route('admin.users.index')"
                    :icon="UsersIcon"
                    :count="stats.total_users"
                />
            </div>
        </section>

        <section class="mt-12"></section>
    </AdminLayout>
</template>
