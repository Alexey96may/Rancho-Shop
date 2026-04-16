<script setup lang="ts">
    import { computed } from 'vue';
    import type { Component } from 'vue';

    import { usePage } from '@inertiajs/vue3';

    import {
        ClipboardList,
        FileText,
        Folder,
        HelpCircle,
        MessageSquare,
        Package,
        PawPrint,
        Settings,
        Sparkles,
        Tags,
        Ticket,
        Truck,
        User,
    } from 'lucide-vue-next';

    import DashboardCard from '@/Components/Admin/Cards/DashboardCard.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import type { SharedData } from '@/types';

    type CardConfig = {
        href: string;
        icon: Component;
        description: string;
        label: string;
    };

    type CanMap = Record<string, boolean>;

    const page = usePage<SharedData>();

    // 👇 ВАЖНО: НЕ computed, НЕ .value
    const can: CanMap = (page.props.can ?? {}) as CanMap;

    const config: Record<string, CardConfig> = {
        manageProducts: {
            href: '/admin/products',
            icon: Package,
            description: 'Управление товарами',
            label: 'Продукты',
        },
        manageOrders: {
            href: '/admin/orders',
            icon: ClipboardList,
            description: 'Заказы',
            label: 'Заказы',
        },
        manageComments: {
            href: '/admin/comments',
            icon: MessageSquare,
            description: 'Комментарии',
            label: 'Комментарии',
        },
        manageDelivery: {
            href: '/admin/delivery',
            icon: Truck,
            description: 'Доставка',
            label: 'Доставка',
        },
        manageAnimals: {
            href: '/admin/animals',
            icon: PawPrint,
            description: 'Животные',
            label: 'Животные',
        },
        manageUsers: {
            href: '/admin/users',
            icon: User,
            description: 'Пользователи',
            label: 'Пользователи',
        },
        manageCategories: {
            href: '/admin/categories',
            icon: Folder,
            description: 'Категории',
            label: 'Категории',
        },
        manageCatalog: {
            href: '/admin/catalog',
            icon: Tags,
            description: 'Номенклатура',
            label: 'Номенклатура',
        },
        managePages: {
            href: '/admin/pages',
            icon: FileText,
            description: 'Страницы',
            label: 'Страницы',
        },
        managePromocodes: {
            href: '/admin/promocodes',
            icon: Ticket,
            description: 'Промокоды',
            label: 'Промокоды',
        },
        manageFaq: {
            href: '/admin/faq',
            icon: HelpCircle,
            description: 'FAQ',
            label: 'FAQ',
        },
        manageFeatures: {
            href: '/admin/features',
            icon: Sparkles,
            description: 'Фичи',
            label: 'Фичи',
        },
        manageSettings: {
            href: '/admin/settings',
            icon: Settings,
            description: 'Настройки',
            label: 'Настройки',
        },
    };

    const cards = computed(() => {
        return Object.entries(config)
            .map(([key, conf]) => ({
                key,
                ...conf,
                visible: Boolean(can[key]),
            }))
            .filter((card) => card.visible);
    });
</script>

<template>
    <AdminLayout>
        <section class="space-y-6">
            <header>
                <h1 class="text-2xl font-bold text-[var(--forest)]">Dashboard</h1>
                <p class="text-sm text-gray-500">Управление системой</p>
            </header>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <DashboardCard
                    v-for="card in cards"
                    :key="card.key"
                    :title="card.label"
                    :description="card.description"
                    :href="card.href"
                    :icon="card.icon"
                />
            </div>
        </section>
    </AdminLayout>
</template>
