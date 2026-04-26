<script setup lang="ts">
    import { onMounted, ref } from 'vue';

    import { usePage } from '@inertiajs/vue3';

    import {
        // Для доставки
        AcademicCapIcon,
        AdjustmentsHorizontalIcon,
        ChatBubbleBottomCenterTextIcon,
        Cog6ToothIcon,
        DocumentTextIcon,
        HomeIcon,
        InboxIcon,
        // Для промокодов
        QuestionMarkCircleIcon,
        ShoppingCartIcon,
        // Для животных (или замени на подходящую)
        Square3Stack3DIcon,
        // Для FAQ
        StarIcon,
        // Для фич/преимуществ
        TagIcon,
        // Для каталога/вариантов
        TicketIcon,
        // Для категорий
        TruckIcon,
        UsersIcon,
    } from '@heroicons/vue/24/outline';

    import AdminSidebarLink from '@/Components/Admin/UI/AdminSidebarLink.vue';
    import SeoMeta from '@/Components/Shared/SeoMeta.vue';
    import Toast from '@/Components/Shared/Toast.vue';
    import { SharedData } from '@/types';

    const page = usePage<SharedData>();
    const can = page.props.can;

    const isMounted = ref(false);
    onMounted(() => {
        isMounted.value = true;
    });
</script>

<template>
    <SeoMeta :force-robots="'nofollow, noindex'" :seo="page.props?.seo" />
    <Toast />
    <div class="flex min-h-screen bg-slate-950 font-sans text-slate-200">
        <aside
            id="admin-sidebar"
            class="sticky top-0 flex h-screen w-72 flex-col gap-8 overflow-y-auto border-r border-slate-900 bg-slate-950 p-6"
        >
            <div class="flex items-center gap-2 px-4">
                <span class="text-2xl font-black uppercase tracking-tighter text-white">
                    Ранчо <span class="text-orange-600">Admin</span>
                </span>
            </div>

            <nav role="navigation">
                <ul class="flex flex-col gap-1">
                    <li>
                        <AdminSidebarLink
                            :href="route('admin.dashboard')"
                            :active="route().current('admin.dashboard')"
                            :icon="HomeIcon"
                            label="Дашборд"
                        />
                    </li>

                    <li
                        class="mb-2 mt-6 px-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-600"
                    >
                        Магазин
                    </li>
                    <li v-if="can.manageOrders">
                        <AdminSidebarLink
                            :href="route('admin.orders.index')"
                            :active="route().current('admin.orders.*')"
                            :icon="ShoppingCartIcon"
                            label="Заказы"
                        />
                    </li>
                    <li v-if="can.manageProducts">
                        <AdminSidebarLink
                            :href="route('admin.products.index')"
                            :active="route().current('admin.products.*')"
                            :icon="InboxIcon"
                            label="Товары"
                        />
                    </li>
                    <li>
                        <AdminSidebarLink
                            :href="route('admin.categories.index')"
                            :active="route().current('admin.categories.*')"
                            :icon="TagIcon"
                            label="Категории"
                        />
                    </li>
                    <li>
                        <AdminSidebarLink
                            :href="route('admin.catalog.index')"
                            :active="route().current('admin.catalog.*')"
                            :icon="Square3Stack3DIcon"
                            label="Варианты (SKU)"
                        />
                    </li>

                    <li
                        class="mb-2 mt-6 px-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-600"
                    >
                        Ферма
                    </li>
                    <li>
                        <AdminSidebarLink
                            :href="route('admin.animals.index')"
                            :active="route().current('admin.animals.*')"
                            :icon="AcademicCapIcon"
                            label="Животные"
                        />
                    </li>

                    <li
                        class="mb-2 mt-6 px-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-600"
                    >
                        Контент
                    </li>
                    <li>
                        <AdminSidebarLink
                            :href="route('admin.pages.index')"
                            :active="route().current('admin.pages.*')"
                            :icon="DocumentTextIcon"
                            label="Страницы CMS"
                        />
                    </li>
                    <li v-if="can.manageComments">
                        <AdminSidebarLink
                            :href="route('admin.comments.index')"
                            :active="route().current('admin.comments.*')"
                            :icon="ChatBubbleBottomCenterTextIcon"
                            label="Отзывы"
                        />
                    </li>
                    <li>
                        <AdminSidebarLink
                            :href="route('admin.promocodes.index')"
                            :active="route().current('admin.promocodes.*')"
                            :icon="TicketIcon"
                            label="Промокоды"
                        />
                    </li>
                    <li>
                        <AdminSidebarLink
                            :href="route('admin.faq.index')"
                            :active="route().current('admin.faq.*')"
                            :icon="QuestionMarkCircleIcon"
                            label="FAQ"
                        />
                    </li>
                    <li>
                        <AdminSidebarLink
                            :href="route('admin.features.index')"
                            :active="route().current('admin.features.*')"
                            :icon="StarIcon"
                            label="Блоки"
                        />
                    </li>

                    <li
                        class="mb-2 mt-6 px-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-600"
                    >
                        Система
                    </li>
                    <li v-if="can.manageUsers">
                        <AdminSidebarLink
                            :href="route('admin.users.index')"
                            :active="route().current('admin.users.*')"
                            :icon="UsersIcon"
                            label="Персонал"
                        />
                    </li>
                    <li>
                        <AdminSidebarLink
                            :href="route('admin.units.index')"
                            :active="route().current('admin.units.*')"
                            :icon="AdjustmentsHorizontalIcon"
                            label="Номенклатура"
                        />
                    </li>
                    <li v-if="can.manageSettings">
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
                class="sticky top-0 z-10 flex h-20 items-center justify-between border-b border-slate-900 bg-slate-950/50 px-10 backdrop-blur-md"
            >
                <div id="admin-header-content" class="flex-1"></div>

                <div class="flex items-center gap-4">
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
            <div id="main-content" class="p-10">
                <slot v-if="isMounted" />
            </div>
        </div>
    </div>
</template>
