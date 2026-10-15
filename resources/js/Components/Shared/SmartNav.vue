<script setup lang="ts">
    import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';

    import { Link, usePage } from '@inertiajs/vue3';

    import { ChevronDownIcon } from '@heroicons/vue/24/outline';

    type Theme = 'light' | 'dark';

    interface NavItem {
        name: string;
        href: string;
        pattern: string;
    }

    interface Props {
        items: NavItem[];
        theme?: Theme;
        gap?: number;
        moreButtonWidth?: number;
    }

    const props = withDefaults(defineProps<Props>(), {
        theme: 'light',
        gap: 24,
        moreButtonWidth: 96,
    });

    const page = usePage();
    const isDark = computed(() => props.theme === 'dark');

    /* ===== Единый focus для всех интерактивных элементов ===== */
    const focusClass = computed(() =>
        isDark.value
            ? 'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-400/60 rounded-md'
            : 'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rancho-pine/40 rounded-md',
    );

    /* ===== Измерения ===== */
    const containerRef = ref<HTMLElement | null>(null);
    const measureRef = ref<HTMLElement | null>(null);
    const availableWidth = ref(0);
    const itemWidths = ref<number[]>([]);
    const measured = ref(false);

    let resizeObserver: ResizeObserver | null = null;

    function measureItems() {
        if (!measureRef.value) return;
        const els = measureRef.value.querySelectorAll<HTMLElement>('[data-measure]');
        itemWidths.value = Array.from(els).map((el) => el.offsetWidth);
        measured.value = true;
    }

    onMounted(async () => {
        if (typeof document !== 'undefined' && 'fonts' in document) {
            await document.fonts.ready.catch(() => {});
        }

        await nextTick();
        measureItems();

        if (containerRef.value) {
            resizeObserver = new ResizeObserver((entries) => {
                availableWidth.value = entries[0].contentRect.width;
            });
            resizeObserver.observe(containerRef.value);
            availableWidth.value = containerRef.value.clientWidth;
        }
    });

    onUnmounted(() => {
        resizeObserver?.disconnect();
    });

    watch(
        () => props.items,
        async () => {
            await nextTick();
            measureItems();
        },
        { deep: true },
    );

    /* ===== Расчёт видимых ===== */
    const visibleCount = computed(() => {
        const n = props.items.length;
        if (!measured.value || !availableWidth.value) return n;

        const available = Math.max(0, availableWidth.value - 8);
        const widths = itemWidths.value;
        const gap = props.gap;

        let total = 0;
        let count = 0;

        for (let i = 0; i < n; i++) {
            const next = total + widths[i] + (i > 0 ? gap : 0);
            if (next <= available) {
                total = next;
                count++;
            } else {
                break;
            }
        }

        if (count === n) return n;

        const more = props.moreButtonWidth;

        while (count > 0) {
            const withMore = total + gap + more;
            if (withMore <= available) break;

            count--;
            total = 0;
            for (let i = 0; i < count; i++) {
                total += widths[i] + (i > 0 ? gap : 0);
            }
        }

        return count;
    });

    const visibleItems = computed(() => props.items.slice(0, visibleCount.value));
    const overflowItems = computed(() => props.items.slice(visibleCount.value));

    /* ===== Дропдаун ===== */
    const isDropdownOpen = ref(false);

    function toggleDropdown() {
        isDropdownOpen.value = !isDropdownOpen.value;
    }

    function closeDropdown() {
        isDropdownOpen.value = false;
    }

    function onClickOutside(e: MouseEvent) {
        if (!containerRef.value) return;
        if (!containerRef.value.contains(e.target as Node)) {
            closeDropdown();
        }
    }

    onMounted(() => {
        if (typeof document === 'undefined') return;
        document.addEventListener('click', onClickOutside);
    });
    onUnmounted(() => {
        if (typeof document === 'undefined') return;
        document.removeEventListener('click', onClickOutside);
    });

    watch(() => page.url, closeDropdown);

    function isActive(item: NavItem): boolean {
        const normalize = (url: string): string => {
            let path = url.split('?')[0].split('#')[0];

            if (typeof window === 'object') {
                try {
                    path = new URL(path, window.location.origin).pathname;
                } catch {
                    // уже path
                }
            }

            return path.replace(/\/+$/, '') || '/';
        };

        const currentUrl = normalize(page.url);
        const targetUrl = normalize(item.href);

        if (currentUrl === targetUrl) return true;

        if (targetUrl !== '/' && currentUrl.startsWith(targetUrl + '/')) {
            return true;
        }

        return false;
    }

    /* ===== Классы ===== */
    function linkClasses(active: boolean): string[] {
        return [
            'group relative whitespace-nowrap px-1 py-1 font-sans text-sm font-semibold transition-colors lg:text-base',
            active
                ? isDark.value
                    ? 'text-orange-400'
                    : 'text-rancho-pine'
                : isDark.value
                  ? 'text-slate-200 hover:text-orange-400'
                  : 'text-rancho-forest hover:text-rancho-pine',
        ];
    }

    function underlineClasses(active: boolean): string[] {
        return [
            'pointer-events-none absolute -bottom-0.5 left-1 right-1 h-0.5 transition-all',
            active ? 'opacity-100' : 'opacity-0 group-hover:opacity-100',
            isDark.value ? 'bg-orange-400' : 'bg-rancho-buttercup',
        ];
    }

    /* ===== Классы для ссылок в дропдауне ===== */
    function dropdownItemClasses(active: boolean): string[] {
        return [
            'flex items-center justify-between rounded-xl px-4 py-2.5 text-sm font-semibold transition-colors',
            active
                ? isDark.value
                    ? 'bg-orange-600/10 text-orange-400'
                    : 'bg-rancho-pine/10 text-rancho-pine'
                : isDark.value
                  ? 'text-slate-200 hover:bg-slate-900'
                  : 'text-rancho-forest hover:bg-rancho-olive/5',
        ];
    }
</script>

<template>
    <nav
        ref="containerRef"
        class="relative flex w-full min-w-0 items-center justify-center"
        :style="{ gap: `${gap}px` }"
        aria-label="Главное меню"
    >
        <!-- Скрытый слой для измерения -->
        <div
            ref="measureRef"
            class="pointer-events-none absolute -top-[9999px] left-0 flex opacity-0"
            :style="{ gap: `${gap}px` }"
            aria-hidden="true"
        >
            <span
                v-for="item in items"
                :key="`measure-${item.href}`"
                data-measure
                class="whitespace-nowrap px-1 font-sans text-sm font-semibold lg:text-base"
            >
                {{ item.name }}
            </span>
        </div>

        <!-- Видимые ссылки -->
        <Link
            v-for="item in visibleItems"
            :key="item.href"
            :href="item.href"
            :class="[...linkClasses(isActive(item)), focusClass]"
            :aria-current="isActive(item) ? 'page' : undefined"
        >
            {{ item.name }}
            <span :class="underlineClasses(isActive(item))" aria-hidden="true" />
        </Link>

        <!-- Дропдаун «Ещё» -->
        <div v-if="overflowItems.length" class="relative">
            <button
                type="button"
                class="group flex items-center gap-1 whitespace-nowrap px-1 py-1 font-sans text-sm font-semibold transition-colors lg:text-base"
                :class="[
                    overflowItems.some((item) => isActive(item))
                        ? isDark
                            ? 'text-orange-400'
                            : 'text-rancho-pine'
                        : isDark
                          ? 'text-slate-200 hover:text-orange-400'
                          : 'text-rancho-forest hover:text-rancho-pine',
                    focusClass,
                ]"
                :aria-expanded="isDropdownOpen"
                aria-haspopup="menu"
                @click.stop="toggleDropdown"
            >
                Ещё
                <ChevronDownIcon
                    class="h-4 w-4 transition-transform"
                    :class="{ 'rotate-180': isDropdownOpen }"
                    aria-hidden="true"
                />
            </button>

            <Transition name="dropdown">
                <div
                    v-if="isDropdownOpen"
                    class="absolute right-0 top-full z-50 mt-3 min-w-56 overflow-hidden rounded-2xl border p-1.5 shadow-rancho-sm backdrop-blur-lg"
                    :class="
                        isDark
                            ? 'border-slate-800 bg-slate-950/95'
                            : 'border-rancho-olive/10 bg-rancho-paper/95'
                    "
                    role="menu"
                >
                    <Link
                        v-for="item in overflowItems"
                        :key="item.href"
                        :href="item.href"
                        :class="[...dropdownItemClasses(isActive(item)), focusClass]"
                        role="menuitem"
                    >
                        <span>{{ item.name }}</span>
                        <span
                            v-if="isActive(item)"
                            class="h-1.5 w-1.5 rounded-full"
                            :class="isDark ? 'bg-orange-400' : 'bg-rancho-pine'"
                            aria-hidden="true"
                        />
                    </Link>
                </div>
            </Transition>
        </div>
    </nav>
</template>

<style scoped>
    .dropdown-enter-active,
    .dropdown-leave-active {
        transition:
            opacity 0.15s ease,
            transform 0.15s ease;
    }
    .dropdown-enter-from,
    .dropdown-leave-to {
        opacity: 0;
        transform: translateY(-6px);
    }
</style>
