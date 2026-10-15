<script setup lang="ts">
    import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

    import { router } from '@inertiajs/vue3';
    import { Link, usePage } from '@inertiajs/vue3';

    import {
        AdjustmentsHorizontalIcon,
        Bars3Icon,
        ChartBarIcon,
        ChatBubbleBottomCenterTextIcon,
        Cog6ToothIcon,
        DocumentTextIcon,
        EyeIcon,
        HomeIcon,
        InboxIcon,
        QuestionMarkCircleIcon,
        ShoppingCartIcon,
        Square3Stack3DIcon,
        StarIcon,
        TagIcon,
        TicketIcon,
        UsersIcon,
        XMarkIcon,
    } from '@heroicons/vue/24/outline';

    import AdminSidebarLink from '@/Components/Admin/UI/AdminSidebarLink.vue';
    import SeoMeta from '@/Components/Shared/SeoMeta.vue';
    import Toaster from '@/Components/Shared/Toaster.vue';
    import { useFlashToasts } from '@/composables/useFlashToasts';
    import type { SharedData } from '@/types';
    import { getInitials } from '@/utils/user';

    useFlashToasts();

    const page = usePage<SharedData>();
    const can = page.props.can;

    const userName = computed(() => page.props.auth.user?.data?.name ?? '');
    const userRole = computed(() => page.props.auth.user?.data?.role.label ?? '');

    const isMounted = ref(false);
    onMounted(() => {
        isMounted.value = true;
    });

    // === Mobile sidebar ===
    const isSidebarOpen = ref(false);

    function closeSidebar() {
        isSidebarOpen.value = false;
    }

    // Блокировка/разблокировка скролла body
    function syncBodyScroll(open: boolean) {
        if (typeof document === 'undefined') return;
        document.body.style.overflow = open ? 'hidden' : '';
    }

    watch(isSidebarOpen, (open) => {
        syncBodyScroll(open);
    });

    const offFinish = router.on('finish', () => {
        closeSidebar();
        syncBodyScroll(false);
    });

    onBeforeUnmount(() => {
        offFinish();
        syncBodyScroll(false);
    });

    const userInitial = computed(() => getInitials(userName.value));
</script>

<template>
    <SeoMeta :force-robots="'nofollow, noindex'" :seo="page.props?.seo" />
    <Toaster theme="dark" />

    <div class="flex min-h-screen bg-rancho-ink-950 font-sans text-rancho-ink-200">
        <!-- Backdrop -->
        <Transition name="backdrop">
            <div
                v-if="isSidebarOpen"
                class="fixed inset-0 z-40 bg-black/70 backdrop-blur-sm lg:hidden"
                @click="closeSidebar"
                aria-hidden="true"
            ></div>
        </Transition>

        <!-- SIDEBAR -->
        <aside
            id="admin-sidebar"
            class="fixed inset-y-0 left-0 z-50 flex h-dvh w-[85vw] max-w-72 shrink-0 flex-col gap-6 overflow-y-auto overscroll-contain border-r border-rancho-ink-800 bg-rancho-ink-950 p-4 transition-transform duration-300 sm:gap-8 sm:p-6 lg:sticky lg:top-0 lg:h-screen lg:translate-x-0"
            :class="isSidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <!-- Логотип -->
            <div class="flex items-center justify-between gap-4">
                <Link
                    :href="route('home')"
                    class="text-xl font-black uppercase leading-none tracking-tighter text-white transition-colors hover:text-rancho-mint sm:text-2xl"
                >
                    Ранчо
                    <span class="text-rancho-mint">Admin</span>
                </Link>

                <!-- Закрытие -->
                <button
                    type="button"
                    class="rounded-lg p-2 pr-0 text-rancho-ink-400 transition-colors hover:bg-rancho-ink-900 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-rancho-pine/40 lg:hidden"
                    @click="closeSidebar"
                    aria-label="Закрыть меню"
                >
                    <XMarkIcon class="h-5 w-5 sm:h-6 sm:w-6" aria-hidden="true" />
                </button>
            </div>

            <!-- Меню -->
            <nav role="navigation" aria-label="Админ-навигация">
                <ul class="flex flex-col gap-0.5 sm:gap-1">
                    <li>
                        <AdminSidebarLink
                            :href="route('admin.dashboard')"
                            :active="route().current('admin.dashboard')"
                            :icon="HomeIcon"
                            label="Дашборд"
                        />
                    </li>

                    <!-- МАГАЗИН -->
                    <li
                        class="mb-1 mt-5 px-3 text-[9px] font-black uppercase tracking-[0.2em] text-rancho-ink-500 sm:mb-2 sm:mt-6 sm:px-4 sm:text-[10px]"
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
                    <li>
                        <AdminSidebarLink
                            :href="route('admin.analytics.index')"
                            :active="route().current('admin.analytics.*')"
                            :icon="ChartBarIcon"
                            label="Аналитика"
                        />
                    </li>

                    <!-- ФЕРМА -->
                    <li
                        class="mb-1 mt-5 px-3 text-[9px] font-black uppercase tracking-[0.2em] text-rancho-ink-500 sm:mb-2 sm:mt-6 sm:px-4 sm:text-[10px]"
                    >
                        Ферма
                    </li>
                    <li>
                        <AdminSidebarLink
                            :href="route('admin.animals.index')"
                            :active="route().current('admin.animals.*')"
                            :icon="EyeIcon"
                            label="Животные"
                        />
                    </li>

                    <!-- КОНТЕНТ -->
                    <li
                        class="mb-1 mt-5 px-3 text-[9px] font-black uppercase tracking-[0.2em] text-rancho-ink-500 sm:mb-2 sm:mt-6 sm:px-4 sm:text-[10px]"
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

                    <!-- СИСТЕМА -->
                    <li
                        class="mb-1 mt-5 px-3 text-[9px] font-black uppercase tracking-[0.2em] text-rancho-ink-500 sm:mb-2 sm:mt-6 sm:px-4 sm:text-[10px]"
                    >
                        Система
                    </li>
                    <li v-if="can.manageUsers">
                        <AdminSidebarLink
                            :href="route('admin.users.index')"
                            :active="route().current('admin.users.*')"
                            :icon="UsersIcon"
                            label="Люди"
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

        <!-- CONTENT -->
        <div class="flex min-w-0 flex-1 flex-col">
            <header
                class="sticky top-0 z-30 flex h-14 items-center gap-2 border-b border-rancho-ink-800 bg-rancho-ink-950/80 px-3 py-8 backdrop-blur-md sm:h-16 sm:gap-3 sm:px-4 md:h-20 md:gap-4 md:px-6 lg:px-10"
            >
                <!-- Hamburger -->
                <button
                    type="button"
                    class="shrink-0 rounded-lg p-2 pl-0 text-rancho-ink-200 transition-colors hover:bg-rancho-ink-900 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-rancho-pine/40 lg:hidden"
                    @click="isSidebarOpen = true"
                    aria-label="Открыть меню"
                >
                    <Bars3Icon class="h-5 w-5 sm:h-6 sm:w-6" aria-hidden="true" />
                </button>

                <!-- Заголовок страницы (телепорт) -->
                <h1 id="admin-header-content" class="min-w-0 flex-1 truncate text-xs"></h1>

                <!-- Правый блок: юзер -->
                <div class="flex shrink-0 items-center gap-2 sm:gap-3 md:gap-4">
                    <div class="hidden text-right md:block">
                        <p class="text-xs font-bold leading-tight text-white md:text-sm">
                            <Link
                                :href="route('profile.edit')"
                                class="transition-colors hover:text-rancho-mint"
                            >
                                {{ userName }}
                            </Link>
                        </p>
                        <span
                            class="text-[9px] font-bold uppercase tracking-tighter text-rancho-mint md:text-[10px]"
                        >
                            {{ userRole }}
                        </span>
                    </div>

                    <!-- Аватарка (до md) -->
                    <Link
                        :href="route('profile.edit')"
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-rancho-ink-800 text-xs font-black text-rancho-mint transition-colors hover:bg-rancho-ink-700 sm:h-9 sm:w-9 md:hidden"
                        :aria-label="userName"
                    >
                        {{ userInitial }}
                    </Link>
                </div>
            </header>

            <div id="main-content" class="min-w-0 flex-1 px-4 py-12 sm:p-6 lg:p-8 xl:p-10">
                <slot v-if="isMounted" />
            </div>
        </div>
    </div>
</template>

<style scoped>
    .backdrop-enter-active,
    .backdrop-leave-active {
        transition: opacity 0.25s ease;
    }
    .backdrop-enter-from,
    .backdrop-leave-to {
        opacity: 0;
    }

    /* Скроллбар сайдбара */
    #admin-sidebar::-webkit-scrollbar {
        width: 4px;
    }
    #admin-sidebar::-webkit-scrollbar-thumb {
        background-color: #403a2d; /* rancho-ink-700 */
        border-radius: 9999px;
    }
    #admin-sidebar::-webkit-scrollbar-thumb:hover {
        background-color: #756d57; /* rancho-ink-500 */
    }

    @media (prefers-reduced-motion: reduce) {
        .backdrop-enter-active,
        .backdrop-leave-active {
            transition: none;
        }
    }
</style>
