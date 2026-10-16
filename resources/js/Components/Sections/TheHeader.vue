<script setup lang="ts">
    import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

    import { Link, usePage } from '@inertiajs/vue3';

    import { Bars3Icon, ShoppingBagIcon, UserIcon, XMarkIcon } from '@heroicons/vue/24/outline';

    import CowIcon from '@/Components/Icons/CowIcon.vue';
    import CowIconDark from '@/Components/Icons/CowIconDark.vue';
    import SmartNav from '@/Components/Shared/SmartNav.vue';
    import { useCartStore } from '@/stores/cart';
    import type { SharedData } from '@/types';
    import { getInitials } from '@/utils/user';

    interface NavItem {
        name: string;
        href: string;
        pattern: string;
    }

    interface Props {
        theme?: 'light' | 'dark';
    }

    const props = withDefaults(defineProps<Props>(), {
        theme: 'light',
    });

    const isDark = computed(() => props.theme === 'dark');

    const cartStore = useCartStore();

    const page = usePage<SharedData>();
    const user = computed(() => page.props.auth?.user?.data);

    const navigation = computed<NavItem[]>(() => (page.props as any).navigation ?? []);

    /* ===== Активность пункта ===== */
    function isActive(item: NavItem): boolean {
        const normalize = (url: string): string => {
            let path = url.split('?')[0].split('#')[0];

            try {
                path = new URL(path, window.location.origin).pathname;
            } catch {
                // уже path
            }

            return path.replace(/\/+$/, '') || '/';
        };

        const currentUrl = normalize(page.url);
        const targetUrl = normalize(item.href);

        if (currentUrl === targetUrl) return true;
        if (targetUrl !== '/' && currentUrl.startsWith(targetUrl + '/')) return true;

        return false;
    }

    /* ===== Burger menu ===== */
    const isMenuOpen = ref(false);

    function closeMenu() {
        isMenuOpen.value = false;
    }

    watch(() => page.url, closeMenu);

    watch(isMenuOpen, (open) => {
        if (typeof document !== 'undefined') {
            document.body.style.overflow = open ? 'hidden' : '';
        }
    });

    function onKeydown(e: KeyboardEvent) {
        if (e.key === 'Escape') closeMenu();
    }

    onMounted(() => {
        if (typeof window === 'undefined') return;
        window.addEventListener('keydown', onKeydown);
    });

    onUnmounted(() => {
        if (typeof window === 'undefined') return;
        window.removeEventListener('keydown', onKeydown);
        document.body.style.overflow = '';
    });

    const focusClass = computed(() =>
        isDark
            ? 'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rancho-buttercup/60'
            : 'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rancho-pine/40',
    );

    const MAX_CART_BADGE = 99;

    const cartBadgeLabel = computed(() => {
        const count = cartStore.totalCleanItems;
        if (count > MAX_CART_BADGE) return `${MAX_CART_BADGE}+`;
        return String(count);
    });

    const firstName = computed(() => {
        const name = user.value?.name?.trim() ?? '';
        if (!name) return '';
        return name.split(' ')[0];
    });

    const displayName = computed(() => {
        const fullName = user.value?.name?.trim() ?? '';
        if (!fullName) return '?';

        // 1. Full Name ≤ 9
        if (fullName.length <= 9) return fullName;

        // 2. First Name ≤ 9
        if (firstName.value.length <= 9) return firstName.value;

        // 3. First Name Initials
        return getInitials(fullName);
    });

    const CowLogo = computed(() => (isDark.value ? CowIconDark : CowIcon));
</script>

<template>
    <header
        class="sticky top-0 z-50 border-b backdrop-blur-md transition-colors"
        :class="
            isDark
                ? 'border-rancho-ink-800 bg-rancho-ink-950/80'
                : 'border-rancho-ink-200 bg-rancho-paper/80'
        "
    >
        <AppContainer class="flex items-center justify-between gap-3 py-3 sm:py-4 md:gap-6">
            <!-- LOGO -->
            <Link
                :href="route('home')"
                class="group flex min-w-0 shrink items-center gap-2 sm:gap-3"
                :class="focusClass"
            >
                <component :is="CowLogo" class="h-7 w-7 shrink-0 transition-colors sm:h-8 sm:w-8" />

                <div class="flex min-w-0 flex-col">
                    <div
                        class="flex flex-col truncate font-header text-lg leading-none transition-colors sm:flex-row sm:gap-2 sm:text-xl md:text-2xl"
                        :class="isDark ? 'text-rancho-paper' : 'text-rancho-forest'"
                    >
                        <span>Молочная</span>
                        <span>Долина</span>
                    </div>
                    <span
                        class="hidden font-sans text-[10px] uppercase tracking-[0.2em] opacity-70 transition-colors sm:block"
                        :class="isDark ? 'text-rancho-ink-400' : 'text-rancho-ink-500'"
                    >
                        Семейное Ранчо
                    </span>
                </div>
            </Link>

            <!-- NAV (умная, только md+) -->
            <div class="hidden min-w-0 flex-1 justify-center md:flex">
                <SmartNav :items="navigation" :theme="theme" />
            </div>

            <!-- RIGHT -->
            <div class="flex shrink-0 items-center gap-1.5 sm:gap-2 md:gap-4">
                <!-- USER (md+) -->
                <Link
                    v-if="user"
                    :href="route('profile.edit')"
                    class="group hidden items-center gap-2 rounded-xl border px-3 py-2 outline-none transition-all focus-visible:ring-2 focus-visible:ring-rancho-buttercup/40 md:flex"
                    :class="[
                        isDark
                            ? 'border-rancho-ink-800 bg-rancho-ink-900/60 text-rancho-ink-200 hover:border-rancho-ink-700 hover:bg-rancho-ink-800 hover:text-rancho-buttercup'
                            : 'border-rancho-ink-200 bg-rancho-ink-50 text-rancho-forest hover:border-rancho-ink-300 hover:bg-rancho-ink-100 hover:text-rancho-pine',
                        focusClass,
                    ]"
                >
                    <UserIcon
                        class="h-5 w-5 transition-colors"
                        :class="
                            isDark
                                ? 'text-rancho-ink-400 group-hover:text-rancho-buttercup'
                                : 'text-rancho-ink-500 group-hover:text-rancho-pine'
                        "
                        aria-hidden="true"
                    />
                    <span class="font-sans text-xs font-bold">
                        {{ displayName }}
                    </span>
                </Link>

                <!-- LOGIN (md+) -->
                <Link
                    v-else
                    :href="route('login')"
                    class="shadow-md relative hidden items-center gap-2 rounded-xl px-3 py-2 text-white transition-all focus:outline-none focus-visible:ring-4 focus-visible:ring-rancho-pine/30 active:scale-95 sm:px-4 md:flex"
                    :class="[
                        isDark
                            ? 'bg-rancho-buttercup text-rancho-ink-950 hover:bg-rancho-buttercup/90'
                            : 'bg-rancho-pine text-white hover:bg-rancho-forest',
                        focusClass,
                    ]"
                >
                    <UserIcon
                        class="h-5 w-5 shrink-0 stroke-2"
                        :class="isDark ? 'text-rancho-ink-950' : 'text-white'"
                        aria-hidden="true"
                    />
                </Link>

                <!-- DIVIDER (md+) -->
                <div
                    class="hidden h-6 w-px transition-colors md:block"
                    :class="isDark ? 'bg-rancho-ink-800' : 'bg-rancho-ink-200'"
                    aria-hidden="true"
                ></div>

                <!-- CART -->
                <Link
                    :href="route('cart.index')"
                    class="shadow-md relative flex items-center gap-2 rounded-xl px-3 py-2 text-white transition-all focus:outline-none focus-visible:ring-4 focus-visible:ring-rancho-pine/30 active:scale-95 sm:px-4"
                    :class="[
                        isDark
                            ? 'bg-rancho-buttercup text-rancho-ink-950 hover:bg-rancho-buttercup/90'
                            : 'bg-rancho-pine hover:bg-rancho-forest',
                        focusClass,
                    ]"
                    aria-label="Корзина"
                >
                    <ShoppingBagIcon
                        class="h-5 w-5 shrink-0 stroke-2"
                        :class="isDark ? 'text-rancho-ink-950' : 'text-white'"
                        aria-hidden="true"
                    />

                    <span
                        v-if="cartStore.totalCleanItems > 0"
                        class="animate-bounce-short absolute -right-1.5 -top-1.5 flex h-5 min-w-5 items-center justify-center rounded-full border-2 px-1 text-[10px] font-black sm:-right-2 sm:-top-2"
                        :class="
                            isDark
                                ? 'border-rancho-ink-950 bg-rancho-ink-950 text-rancho-buttercup'
                                : 'border-rancho-paper bg-rancho-ember text-white'
                        "
                        :aria-label="`Товаров в корзине: ${cartStore.totalCleanItems}`"
                    >
                        {{ cartBadgeLabel }}
                    </span>
                </Link>

                <!-- BURGER (до md) -->
                <button
                    type="button"
                    class="ml-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-rancho-buttercup/40 md:hidden"
                    :class="[
                        isDark
                            ? 'border-rancho-ink-800 bg-rancho-ink-900/60 text-rancho-ink-200 hover:bg-rancho-ink-800 hover:text-rancho-buttercup'
                            : 'border-rancho-ink-200 bg-rancho-ink-50 text-rancho-forest hover:bg-rancho-ink-100',
                        focusClass,
                    ]"
                    @click="isMenuOpen = true"
                    aria-label="Открыть меню"
                    :aria-expanded="isMenuOpen"
                    aria-controls="mobile-menu"
                >
                    <Bars3Icon class="h-5 w-5" aria-hidden="true" />
                </button>
            </div>
        </AppContainer>
    </header>

    <!-- MOBILE MENU -->
    <Teleport to="body">
        <Transition name="backdrop">
            <div
                v-if="isMenuOpen"
                class="fixed inset-0 z-[60] bg-black/60 backdrop-blur-sm md:hidden"
                @click="closeMenu"
                aria-hidden="true"
            />
        </Transition>

        <Transition name="drawer">
            <aside
                v-if="isMenuOpen"
                id="mobile-menu"
                class="shadow-2xl fixed inset-y-0 right-0 z-[70] flex h-dvh w-full max-w-sm flex-col overflow-y-auto overscroll-contain border-l transition-colors md:hidden"
                :class="
                    isDark
                        ? 'border-rancho-ink-800 bg-rancho-ink-950 text-rancho-paper'
                        : 'border-rancho-ink-200 bg-rancho-paper text-rancho-forest'
                "
                role="dialog"
                aria-modal="true"
                aria-label="Мобильное меню"
            >
                <!-- Drawer header -->
                <div
                    class="flex shrink-0 items-center justify-between gap-3 border-b px-4 py-3 sm:px-6"
                    :class="isDark ? 'border-rancho-ink-800' : 'border-rancho-ink-200'"
                >
                    <div class="flex items-center gap-2">
                        <CowIcon
                            class="h-6 w-6"
                            :class="isDark ? 'text-rancho-buttercup' : 'text-rancho-pine'"
                        />
                        <span class="font-header text-lg leading-none">Меню</span>
                    </div>

                    <button
                        type="button"
                        class="flex h-10 w-10 items-center justify-center rounded-xl transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-rancho-buttercup/40"
                        :class="
                            isDark
                                ? 'text-rancho-ink-400 hover:bg-rancho-ink-900 hover:text-white'
                                : 'text-rancho-ink-500 hover:bg-rancho-ink-100 hover:text-rancho-forest'
                        "
                        @click="closeMenu"
                        aria-label="Закрыть меню"
                    >
                        <XMarkIcon class="h-6 w-6" aria-hidden="true" />
                    </button>
                </div>

                <!-- User block -->
                <div
                    v-if="user"
                    class="border-b px-2 py-4 sm:px-6"
                    :class="isDark ? 'border-rancho-ink-800' : 'border-rancho-ink-200'"
                >
                    <Link
                        :href="route('profile.edit')"
                        class="flex items-center gap-3 rounded-2xl p-3 transition-colors"
                        :class="isDark ? 'hover:bg-rancho-ink-900' : 'hover:bg-rancho-ink-50'"
                    >
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full text-base font-black"
                            :class="
                                isDark
                                    ? 'bg-rancho-buttercup text-rancho-ink-950'
                                    : 'bg-rancho-pine text-white'
                            "
                        >
                            {{ getInitials(user.name) }}
                        </div>
                        <div class="flex min-w-0 flex-col">
                            <span class="truncate text-sm font-bold">{{ user.name }}</span>
                            <span
                                class="truncate text-xs"
                                :class="isDark ? 'text-rancho-ink-500' : 'text-rancho-ink-500'"
                            >
                                Мой профиль
                            </span>
                        </div>
                    </Link>
                </div>

                <!-- Nav links -->
                <nav class="flex flex-1 flex-col px-2 py-4 sm:px-4">
                    <Link
                        v-for="item in navigation"
                        :key="item.href"
                        :href="item.href"
                        class="flex items-center justify-between rounded-2xl px-4 py-3.5 text-base font-semibold transition-colors"
                        :class="[
                            isActive(item)
                                ? isDark
                                    ? 'bg-rancho-buttercup/10 text-rancho-buttercup'
                                    : 'bg-rancho-pine/10 text-rancho-pine'
                                : isDark
                                  ? 'text-rancho-ink-200 hover:bg-rancho-ink-900'
                                  : 'text-rancho-forest hover:bg-rancho-ink-50',
                            focusClass,
                        ]"
                    >
                        <span>{{ item.name }}</span>
                        <span
                            v-if="isActive(item)"
                            class="h-1.5 w-1.5 rounded-full"
                            :class="isDark ? 'bg-rancho-buttercup' : 'bg-rancho-pine'"
                            aria-hidden="true"
                        />
                    </Link>
                </nav>

                <!-- Footer (login) -->
                <div
                    class="shrink-0 border-t p-4 sm:p-6"
                    :class="isDark ? 'border-rancho-ink-800' : 'border-rancho-ink-200'"
                >
                    <Link
                        v-if="!user"
                        :href="route('login')"
                        class="flex w-full items-center justify-center gap-2 rounded-2xl px-4 py-3 text-xs font-bold uppercase tracking-wider outline-none transition-colors focus-visible:ring-2 focus-visible:ring-rancho-buttercup/40 sm:text-sm"
                        :class="
                            isDark
                                ? 'bg-rancho-buttercup text-rancho-ink-950 hover:bg-rancho-buttercup/90'
                                : 'bg-rancho-pine text-white hover:bg-rancho-forest'
                        "
                    >
                        <UserIcon class="h-4 w-4 sm:h-5 sm:w-5" aria-hidden="true" />
                        Войти
                    </Link>
                </div>
            </aside>
        </Transition>
    </Teleport>
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

    .backdrop-enter-active,
    .backdrop-leave-active {
        transition: opacity 0.25s ease;
    }
    .backdrop-enter-from,
    .backdrop-leave-to {
        opacity: 0;
    }

    .drawer-enter-active,
    .drawer-leave-active {
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .drawer-enter-from,
    .drawer-leave-to {
        transform: translateX(100%);
    }

    @media (prefers-reduced-motion: reduce) {
        .animate-bounce-short,
        .backdrop-enter-active,
        .backdrop-leave-active,
        .drawer-enter-active,
        .drawer-leave-active {
            animation: none;
            transition: none;
        }
    }
</style>
