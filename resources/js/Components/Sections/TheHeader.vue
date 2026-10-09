<script setup lang="ts">
    import { computed } from 'vue';

    import { Link, usePage } from '@inertiajs/vue3';

    import CowIcon from '@/Components/Icons/CowIcon.vue';
    import { useCartStore } from '@/stores/cart';
    import { SharedData } from '@/types';

    interface Props {
        theme?: 'light' | 'dark';
    }

    const props = withDefaults(defineProps<Props>(), {
        theme: 'light',
    });

    const isDark = computed(() => props.theme === 'dark');

    const menuItems = [
        { name: 'Каталог', pathName: 'catalog.index' },
        { name: 'Наши Животные', pathName: 'animals.index' },
        { name: 'О ферме', pathName: 'about' },
        { name: 'Доставка', pathName: 'delivery' },
    ];

    const cartStore = useCartStore();

    const page = usePage<SharedData>();
    const user = computed(() => page.props.auth?.user?.data);
</script>

<template>
    <header
        class="sticky top-0 z-50 border-b backdrop-blur-md transition-colors"
        :class="
            isDark
                ? 'border-slate-800 bg-slate-950/80'
                : 'border-rancho-olive/10 bg-rancho-paper/80'
        "
    >
        <AppContainer class="flex h-20 items-center justify-between">
            <!-- LOGO -->
            <Link :href="route('home')" class="group flex items-center gap-3">
                <CowIcon
                    class="h-8 w-8 transition-colors"
                    :class="isDark ? 'text-orange-400' : 'text-orange-600'"
                />
                <div class="flex flex-col">
                    <span
                        class="font-header text-2xl leading-none transition-colors"
                        :class="isDark ? 'text-slate-100' : 'text-rancho-forest'"
                    >
                        Молочная Долина
                    </span>
                    <span
                        class="font-sans text-[10px] uppercase tracking-[0.2em] opacity-70 transition-colors"
                        :class="isDark ? 'text-slate-400' : 'text-rancho-olive'"
                    >
                        Семейное Ранчо
                    </span>
                </div>
            </Link>

            <!-- NAV -->
            <nav class="hidden items-center gap-8 md:flex">
                <Link
                    v-for="item in menuItems"
                    :key="item.pathName"
                    :href="route(item.pathName)"
                    class="group relative font-sans font-semibold transition-colors"
                    :class="
                        isDark
                            ? 'text-slate-200 hover:text-orange-400'
                            : 'text-rancho-forest hover:text-rancho-pine'
                    "
                >
                    {{ item.name }}
                    <span
                        class="absolute -bottom-1 left-0 h-0.5 w-0 transition-all group-hover:w-full"
                        :class="isDark ? 'bg-orange-400' : 'bg-rancho-buttercup'"
                    ></span>
                </Link>
            </nav>

            <!-- RIGHT -->
            <div class="flex items-center gap-4">
                <!-- USER -->
                <Link
                    v-if="user"
                    :href="route('profile.edit')"
                    class="group flex items-center gap-2 rounded-xl border px-3 py-2 transition-all"
                    :class="
                        isDark
                            ? 'border-slate-800 bg-slate-900/60 text-slate-200 hover:border-slate-700 hover:bg-slate-800 hover:text-orange-400'
                            : 'border-rancho-olive/10 bg-rancho-olive/5 text-rancho-forest hover:bg-rancho-olive/10 hover:text-rancho-pine'
                    "
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5 transition-colors"
                        :class="
                            isDark
                                ? 'text-slate-400 group-hover:text-orange-400'
                                : 'text-rancho-olive group-hover:text-rancho-pine'
                        "
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                        />
                    </svg>
                    <span class="hidden font-sans text-xs font-bold sm:inline">
                        {{ user.name.split(' ')[0] }}
                    </span>
                </Link>

                <!-- LOGIN -->
                <Link
                    v-else
                    :href="route('login')"
                    class="flex items-center gap-1.5 p-2 font-sans text-xs font-bold uppercase tracking-wider transition-colors"
                    :class="
                        isDark
                            ? 'text-slate-400 hover:text-orange-400'
                            : 'text-rancho-olive hover:text-rancho-pine'
                    "
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"
                        />
                    </svg>
                    <span class="hidden lg:inline">Войти</span>
                </Link>

                <!-- DIVIDER -->
                <div
                    class="h-6 w-[1px] transition-colors"
                    :class="isDark ? 'bg-slate-800' : 'bg-rancho-olive/10'"
                ></div>

                <!-- CART -->
                <Link
                    :href="route('cart.index')"
                    class="shadow-md relative flex items-center gap-2 rounded-xl px-4 py-2 text-white transition-all active:scale-95"
                    :class="
                        isDark
                            ? 'bg-orange-600 hover:bg-orange-500'
                            : 'bg-rancho-pine hover:bg-rancho-forest'
                    "
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"
                        />
                    </svg>

                    <span class="hidden font-sans text-sm font-bold sm:inline">Лукошко</span>

                    <div
                        v-if="cartStore.totalCleanItems > 0"
                        class="animate-bounce-short absolute -right-2 -top-2 flex h-5 w-5 items-center justify-center rounded-full border-2 text-[10px] font-black"
                        :class="
                            isDark
                                ? 'border-slate-950 bg-orange-400 text-slate-950'
                                : 'border-rancho-paper bg-rancho-buttercup text-rancho-forest'
                        "
                    >
                        {{ cartStore.totalCleanItems }}
                    </div>
                </Link>
            </div>
        </AppContainer>
    </header>
</template>

<style scoped>
    .animate-bounce-short {
        animation: bounce-short 0.5s ease-in-out;
    }

    @keyframes bounce-short {
        0%,
        100% {
            transform: translateY(0);
        }
        50% {
            transform: translateY(-4px);
        }
    }
</style>
