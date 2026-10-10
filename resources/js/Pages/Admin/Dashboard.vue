<script setup lang="ts">
    import { computed } from 'vue';

    import { usePage } from '@inertiajs/vue3';

    import {
        AcademicCapIcon,
        ChartBarIcon,
        ChatBubbleLeftRightIcon,
        CubeIcon,
        InboxIcon,
        ShoppingCartIcon,
        TicketIcon,
        UsersIcon,
    } from '@heroicons/vue/24/outline';

    import AdminDashboardCard from '@/Components/Admin/Cards/DashboardCard.vue';
    import AdminPageHeader from '@/Components/Admin/Shared/AdminPageHeader.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { SharedData } from '@/types';
    import { formatMoney } from '@/utils/format';

    defineOptions({ layout: AdminLayout });

    interface StatBlock {
        total: number;
        new?: number;
        week?: number;
        active?: number;
        pending?: number;
        revenue?: number;
        in_stock?: number;
        out_of_stock?: number;
        low_stock?: number;
    }

    interface Props {
        stats: {
            users: StatBlock;
            orders: StatBlock;
            comments: StatBlock;
            products: StatBlock;
            variants: StatBlock;
            animals: StatBlock;
        };
    }

    const props = defineProps<Props>();

    const can = usePage<SharedData>().props.can;

    const variantsDescription = computed(() => {
        const v = props.stats.variants;

        if (v.out_of_stock === 0 && v.low_stock === 0) {
            return `${v.in_stock ?? 0} в наличии`;
        }

        const parts: string[] = [];
        if (v.in_stock) parts.push(`${v.in_stock} в наличии`);
        if (v.low_stock) parts.push(`${v.low_stock} заканчивается`);
        if (v.out_of_stock) parts.push(`${v.out_of_stock} нет`);

        return parts.join(' · ');
    });
</script>

<template>
    <Teleport to="#admin-header-content">
        <AdminPageHeader title="Админ-Панель" subtitle="Управление сайтом" />
    </Teleport>

    <section>
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <!-- ORDERS -->
            <AdminDashboardCard
                v-if="can.manageOrders"
                title="Заказы"
                :description="`${stats.orders.new ?? 0} новых · +${stats.orders.week ?? 0} за неделю`"
                :href="route('admin.orders.index')"
                :icon="ShoppingCartIcon"
                :count="stats.orders.total"
            />

            <!-- ANALYTICS (без count) -->
            <AdminDashboardCard
                v-if="can.manageAnalitics"
                title="Аналитика"
                description="Данные по заказам"
                :href="route('admin.analytics.index')"
                :active="route().current('admin.analytics.*')"
                :icon="ChartBarIcon"
            />

            <!-- PRODUCTS -->
            <AdminDashboardCard
                v-if="can.manageProducts"
                title="Товары"
                :description="`${stats.products.active ?? 0} активных`"
                :href="route('admin.products.index')"
                :icon="InboxIcon"
                :count="stats.products.total"
            />

            <!-- VARIANTS -->
            <AdminDashboardCard
                v-if="can.manageProducts"
                title="Варианты"
                :description="variantsDescription"
                :href="route('admin.catalog.index')"
                :icon="CubeIcon"
                :count="stats.variants.total"
            />

            <!-- ANIMALS -->
            <AdminDashboardCard
                title="Животные"
                :description="`${stats.animals.active ?? 0} активных`"
                :href="route('admin.animals.index')"
                :icon="AcademicCapIcon"
                :count="stats.animals.total"
            />

            <!-- PROMOCODES -->
            <AdminDashboardCard
                title="Промокоды"
                description="Активные акции"
                :href="route('admin.promocodes.index')"
                :icon="TicketIcon"
                :count="0"
            />

            <!-- COMMENTS -->
            <AdminDashboardCard
                v-if="can.manageComments"
                title="Отзывы"
                :description="`${stats.comments.pending ?? 0} на модерации · +${stats.comments.week ?? 0} за неделю`"
                :href="route('admin.comments.index')"
                :icon="ChatBubbleLeftRightIcon"
                :count="stats.comments.total"
            />

            <!-- USERS -->
            <AdminDashboardCard
                v-if="can.manageUsers"
                title="Пользователи"
                :description="`+${stats.users.new ?? 0} за неделю`"
                :href="route('admin.users.index')"
                :icon="UsersIcon"
                :count="stats.users.total"
            />
        </div>

        <!-- REVENUE BLOCK -->
        <div
            v-if="can.manageOrders && stats.orders.revenue"
            class="mt-6 rounded-2xl border border-slate-200 bg-white p-6"
        >
            <div class="text-xs uppercase tracking-wider text-slate-500">
                Выручка (оплаченные заказы)
            </div>
            <div class="mt-2 text-3xl font-black text-emerald-600">
                {{ formatMoney(stats.orders.revenue) }}
            </div>
        </div>
    </section>
</template>
