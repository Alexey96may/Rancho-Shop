<script setup lang="ts">
    import { computed } from 'vue';

    import { Link, usePage } from '@inertiajs/vue3';

    import {
        ArrowRightStartOnRectangleIcon,
        BellIcon,
        ChatBubbleLeftRightIcon,
        CpuChipIcon,
        ShoppingBagIcon,
        UserIcon,
    } from '@heroicons/vue/24/outline';

    import TheFooter from '@/Components/Sections/TheFooter.vue';
    import TheHeader from '@/Components/Sections/TheHeader.vue';
    import CookieConsent from '@/Components/Shared/CookieConsent.vue';
    import SeoMeta from '@/Components/Shared/SeoMeta.vue';
    import Toaster from '@/Components/Shared/Toaster.vue';
    import YandexMetrica from '@/Components/Shared/YandexMetrica.vue';
    import { useFlashToasts } from '@/composables/useFlashToasts';
    import type { SharedData } from '@/types';

    useFlashToasts();

    defineProps<{ title: string }>();

    const page = usePage<SharedData>();
    const user = computed(() => page.props.auth?.user?.data);

    const isStuff = computed(() => user.value?.is_stuff);

    /** Классы для пункта навигации */
    function navLinkClasses(isActive: boolean): string {
        return [
            'flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-bold transition-all outline-none focus-visible:ring-2 focus-visible:ring-rancho-buttercup/40',
            isActive
                ? 'bg-rancho-ink-800 text-white ring-1 ring-rancho-ink-700'
                : 'text-rancho-ink-400 hover:bg-rancho-ink-900 hover:text-white',
        ].join(' ');
    }

    /** Цвета ролей — в тёплой палитре */
    const roleColors: Record<string, string> = {
        emerald: 'border-rancho-pine/30 bg-rancho-pine/15 text-rancho-pine',
        orange: 'border-rancho-ember/30 bg-rancho-ember/15 text-rancho-ember',
        blue: 'border-rancho-sky/40 bg-rancho-sky/15 text-rancho-forest',
        rose: 'border-rose-500/30 bg-rose-500/15 text-rose-300',
        slate: 'border-rancho-ink-700 bg-rancho-ink-800 text-rancho-ink-300',
        buttercup: 'border-rancho-buttercup/40 bg-rancho-buttercup/15 text-rancho-buttercup',
    };
</script>

<template>
    <div class="bg-rancho-ink-950 text-white">
        <SeoMeta :force-robots="'nofollow, noindex'" />
        <Toaster theme="dark" />

        <TheHeader theme="dark" />

        <div class="min-h-screen py-12 sm:py-20">
            <AppContainer>
                <div class="mb-16 flex flex-col items-center gap-4 sm:flex-row sm:justify-between">
                    <div class="flex flex-col items-center sm:items-start">
                        <h1 class="text-xl font-black text-rancho-buttercup sm:text-2xl">
                            Личный кабинет
                        </h1>

                        <p v-if="user?.name" class="mt-1 text-center text-sm text-rancho-ink-500">
                            Добро пожаловать, {{ user.name }}
                        </p>
                    </div>

                    <span
                        v-if="user?.role"
                        class="inline-flex w-fit items-center gap-1.5 rounded-full border px-3 py-1.5 text-[10px] font-black uppercase tracking-wider"
                        :class="roleColors[user.role.color] || roleColors.slate"
                    >
                        {{ user.role.label }}
                    </span>
                </div>

                <div class="grid grid-cols-1 gap-8 md:grid-cols-4">
                    <!-- SIDEBAR / MOBILE TABS -->
                    <aside class="md:flex md:flex-col md:gap-2">
                        <!-- ADMIN LINK (только desktop) -->
                        <div v-if="isStuff" class="border-b border-rancho-ink-800 pb-8 sm:pb-6">
                            <Link
                                :href="route('admin.dashboard')"
                                class="hidden items-center gap-3 rounded-2xl border border-rancho-buttercup/30 bg-rancho-buttercup/10 px-4 py-3 text-sm font-black text-rancho-buttercup outline-none transition-all hover:bg-rancho-buttercup hover:text-rancho-ink-950 focus-visible:ring-2 focus-visible:ring-rancho-buttercup/40 md:flex"
                            >
                                <CpuChipIcon class="h-5 w-5 animate-pulse" aria-hidden="true" />
                                Панель управления
                            </Link>

                            <!-- ADMIN LINK (mobile compact) -->
                            <Link
                                :href="route('admin.dashboard')"
                                class="flex w-full items-center justify-center gap-2 rounded-2xl border border-rancho-buttercup/30 bg-rancho-buttercup/10 px-4 py-2.5 text-xs font-black text-rancho-buttercup outline-none transition-all hover:bg-rancho-buttercup hover:text-rancho-ink-950 focus-visible:ring-2 focus-visible:ring-rancho-buttercup/40 md:hidden"
                            >
                                <CpuChipIcon class="h-4 w-4 animate-pulse" aria-hidden="true" />
                                Админка
                            </Link>
                        </div>

                        <!-- MOBILE: horizontal scroll tabs -->
                        <nav
                            class="scrollbar-hide -mx-4 flex gap-2 overflow-x-auto px-4 py-4 md:mx-0 md:flex-col md:gap-1 md:overflow-visible md:px-0 md:py-0"
                            aria-label="Навигация профиля"
                        >
                            <Link
                                :href="route('profile.edit')"
                                :class="[
                                    navLinkClasses(route().current('profile.edit')),
                                    'shrink-0 whitespace-nowrap',
                                ]"
                            >
                                <UserIcon class="h-4 w-4 md:h-5 md:w-5" aria-hidden="true" />
                                Профиль
                            </Link>

                            <Link
                                :href="route('profile.comments.index')"
                                :class="[
                                    navLinkClasses(route().current('profile.comments.*')),
                                    'shrink-0 whitespace-nowrap',
                                ]"
                            >
                                <ChatBubbleLeftRightIcon
                                    class="h-4 w-4 md:h-5 md:w-5"
                                    aria-hidden="true"
                                />
                                Мои отзывы
                            </Link>

                            <Link
                                :href="route('profile.orders.index')"
                                :class="[
                                    navLinkClasses(route().current('profile.orders.*')),
                                    'shrink-0 whitespace-nowrap',
                                ]"
                            >
                                <ShoppingBagIcon class="h-4 w-4 md:h-5 md:w-5" aria-hidden="true" />
                                История заказов
                            </Link>

                            <Link
                                :href="route('profile.subscriptions.index')"
                                :class="[
                                    navLinkClasses(route().current('profile.subscriptions.*')),
                                    'shrink-0 whitespace-nowrap',
                                ]"
                            >
                                <BellIcon class="h-5 w-5" />
                                Мои подписки
                            </Link>

                            <!-- LOGOUT: только на мобилке, отделён от остальных -->
                            <Link
                                :href="route('logout')"
                                method="post"
                                as="button"
                                class="focus-visible:ring--rancho-ember/40 flex shrink-0 items-center gap-2 whitespace-nowrap rounded-2xl px-4 py-3 text-sm font-bold text-rancho-ember outline-none transition-all hover:border-rancho-ember hover:bg-rose-100 hover:text-rancho-ember focus-visible:ring-2 active:scale-95 md:hidden"
                            >
                                <ArrowRightStartOnRectangleIcon
                                    class="h-4 w-4"
                                    aria-hidden="true"
                                />
                                Выйти
                            </Link>
                        </nav>

                        <hr class="my-2 hidden border-rancho-ink-800 md:block" />

                        <!-- LOGOUT: desktop -->
                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="hidden w-full items-center gap-3 rounded-2xl px-4 py-3 text-left text-sm font-bold text-rancho-ink-500 outline-none transition-all hover:bg-rancho-ember/10 hover:text-rancho-ember focus-visible:ring-2 focus-visible:ring-rancho-ember/40 md:flex"
                        >
                            <ArrowRightStartOnRectangleIcon class="h-5 w-5" aria-hidden="true" />
                            Выйти из профиля
                        </Link>
                    </aside>

                    <!-- MAIN -->

                    <Transition name="fade" mode="out-in">
                        <main
                            :key="page.url"
                            class="rounded-2xl backdrop-blur-sm sm:border sm:border-rancho-ink-800 sm:bg-rancho-ink-900/50 sm:p-6 md:col-span-3"
                        >
                            <h2 class="mb-6 text-xl font-black text-white">{{ title }}</h2>
                            <slot />
                        </main>
                    </Transition>
                </div>
            </AppContainer>
        </div>

        <TheFooter theme="dark" />
        <YandexMetrica />
        <CookieConsent />
    </div>
</template>
