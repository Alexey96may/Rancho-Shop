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
    import type { SharedData } from '@/types';
    import { formatMoney } from '@/utils/format';

    defineOptions({ layout: AdminLayout });

    interface StatBlock {
        total: number;
        new?: number;
        week?: number;
        active?: number;
        used?: number;
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
            promocodes: StatBlock;
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

    <section class="space-y-6">
        <div class="grid grid-cols-1 gap-4 sm:gap-6 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <!-- ORDERS -->
            <AdminDashboardCard
                v-if="can.manageOrders"
                title="Заказы"
                :description="`${stats.orders.new ?? 0} новых · +${stats.orders.week ?? 0} за неделю`"
                :href="route('admin.orders.index')"
                :icon="ShoppingCartIcon"
                :count="stats.orders.total"
            />

            <!-- ANALYTICS -->
            <AdminDashboardCard
                v-if="can.manageAnalitics"
                title="Аналитика"
                description="Данные по заказам"
                :href="route('admin.analytics.index')"
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
                :description="`${stats.promocodes.active ?? 0} активных · +${stats.promocodes.used ?? 0} примененных`"
                :href="route('admin.promocodes.index')"
                :icon="TicketIcon"
                :count="stats.promocodes.total"
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
            class="relative overflow-hidden rounded-2xl border border-rancho-ink-800 bg-gradient-to-br from-rancho-ink-950 to-rancho-ink-900/60 p-6 sm:p-8"
        >
            <!-- Декоративный blur -->
            <div
                class="pointer-events-none absolute -right-8 -top-8 h-32 w-32 rounded-full bg-rancho-mint/10 blur-3xl"
                aria-hidden="true"
            ></div>

            <div class="relative">
                <div
                    class="text-[10px] font-black uppercase tracking-[0.2em] text-rancho-ink-400 sm:text-[11px]"
                >
                    Выручка (оплаченные заказы)
                </div>

                <div class="mt-3 text-2xl font-black text-rancho-mint sm:text-3xl md:text-4xl">
                    {{ formatMoney(stats.orders.revenue) }}
                </div>
            </div>
        </div>
    </section>
</template>
