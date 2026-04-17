<script setup lang="ts">
    import { usePage } from '@inertiajs/vue3';

    import {
        ChatBubbleBottomCenterTextIcon,
        Cog6ToothIcon,
        DocumentTextIcon,
        HeartIcon,
        HomeIcon,
        InboxIcon,
        ShoppingCartIcon,
        UsersIcon,
    } from '@heroicons/vue/24/outline';

    import AdminSidebarLink from '@/Components/Admin/UI/AdminSidebarLink.vue';
    import { SharedData } from '@/types';

    const page = usePage<SharedData>();
    const can = page.props.can;
</script>

<template>
    <div class="flex min-h-screen bg-slate-950 font-sans text-slate-200">
        <aside
            id="admin-sidebar"
            aria-label="Боковая панель администратора"
            class="sticky top-0 flex h-screen w-72 flex-col gap-8 border-r border-slate-900 bg-slate-950 p-6"
        >
            <div class="flex items-center gap-2 px-4">
                <span
                    class="text-2xl font-black uppercase tracking-tighter text-white"
                    aria-label="Ранчо Админ"
                >
                    Ранчо <span class="text-orange-600" aria-hidden="true">Admin</span>
                </span>
            </div>

            <nav role="navigation" aria-label="Основное меню">
                <ul role="menubar" class="flex flex-col gap-1">
                    <li role="none">
                        <AdminSidebarLink
                            :href="route('admin.dashboard')"
                            :active="route().current('admin.dashboard')"
                            :icon="HomeIcon"
                            label="Дашборд"
                        />
                    </li>

                    <li
                        role="separator"
                        class="mb-2 mt-6 px-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-600"
                        id="mgmt-heading"
                    >
                        Управление
                    </li>

                    <li role="none" v-if="can.manageProducts">
                        <AdminSidebarLink
                            :href="route('admin.products.index')"
                            :active="route().current('admin.products.*')"
                            :icon="InboxIcon"
                            label="Продукты"
                        />
                    </li>

                    <li role="none" v-if="can.manageOrders">
                        <AdminSidebarLink
                            :href="route('admin.orders.index')"
                            :active="route().current('admin.orders.*')"
                            :icon="ShoppingCartIcon"
                            label="Заказы"
                        />
                    </li>

                    <li role="none" v-if="can.manageComments">
                        <AdminSidebarLink
                            :href="route('admin.comments.index')"
                            :active="route().current('admin.comments.*')"
                            :icon="ChatBubbleBottomCenterTextIcon"
                            label="Отзывы"
                        />
                    </li>

                    <li
                        role="separator"
                        class="mb-2 mt-6 px-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-600"
                        id="sys-heading"
                    >
                        Система
                    </li>

                    <li role="none" v-if="can.manageUsers">
                        <AdminSidebarLink
                            :href="route('admin.users.index')"
                            :active="route().current('admin.users.*')"
                            :icon="UsersIcon"
                            label="Персонал"
                        />
                    </li>

                    <li role="none" v-if="can.manageSettings">
                        <AdminSidebarLink
                            :href="route('admin.settings.index')"
                            :active="route().current('admin.settings.*')"
                            :icon="Cog6ToothIcon"
                            label="Настройки"
                        />
                    </li>
                </ul>
            </nav>
        </aside>

        <div class="flex flex-1 flex-col">
            <header
                role="banner"
                class="sticky top-0 z-10 flex h-20 items-center justify-between border-b border-slate-900 bg-slate-950/50 px-10 backdrop-blur-md"
            >
                <h1 id="page-title" class="text-xl font-black uppercase tracking-widest text-white">
                    <slot name="header" />
                </h1>

                <div
                    class="flex items-center gap-4"
                    role="region"
                    aria-label="Профиль пользователя"
                >
                    <div class="hidden text-right sm:block">
                        <p class="text-sm font-bold leading-none text-white">
                            {{ page.props.auth.user?.name }}
                        </p>
                        <span
                            class="text-[10px] font-bold uppercase tracking-tighter text-orange-500"
                            >{{ page.props.auth.user?.role }}</span
                        >
                    </div>
                </div>
            </header>

            <main
                id="main-content"
                class="p-10 outline-none"
                tabindex="-1"
                aria-labelledby="page-title"
            >
                <slot />
            </main>
        </div>
    </div>
</template>
