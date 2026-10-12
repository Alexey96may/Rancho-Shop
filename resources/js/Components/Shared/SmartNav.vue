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
        /** Отступ между пунктами в px — должен совпадать с классом gap-* */
        gap?: number;
        /** Приблизительная ширина кнопки «Ещё», px */
        moreButtonWidth?: number;
    }

    const props = withDefaults(defineProps<Props>(), {
        theme: 'light',
        gap: 24, // = gap-6
        moreButtonWidth: 96,
    });

    const page = usePage();
    const isDark = computed(() => props.theme === 'dark');

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
        // Дожидаемся шрифтов — иначе замер по fallback-шрифту
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

        const available = Math.max(0, availableWidth.value - 8); // ← буфер
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

    // Закрытие по клику вне
    function onClickOutside(e: MouseEvent) {
        if (!containerRef.value) return;
        if (!containerRef.value.contains(e.target as Node)) {
            closeDropdown();
        }
    }

    onMounted(() => document.addEventListener('click', onClickOutside));
    onUnmounted(() => document.removeEventListener('click', onClickOutside));

    // Закрытие при навигации
    watch(() => page.url, closeDropdown);

    function isActive(item: NavItem): boolean {
        const normalize = (url: string): string => {
            // Убираем query и hash
            let path = url.split('?')[0].split('#')[0];

            // Убираем домен, если он есть
            if (typeof window === 'object') {
                try {
                    path = new URL(path, window.location.origin).pathname;
                } catch {
                    // уже path
                }
            }

            // Убираем trailing slash
            return path.replace(/\/+$/, '') || '/';
        };

        const currentUrl = normalize(page.url);
        const targetUrl = normalize(item.href);

        // 1. Точное совпадение
        if (currentUrl === targetUrl) return true;

        // 2. Вложенный путь: /catalog/milk подсветит «Каталог»
        //    НО исключаем корень, чтобы '/' не подсвечивал всё
        if (targetUrl !== '/' && currentUrl.startsWith(targetUrl + '/')) {
            return true;
        }

        return false;
    }

    /* ===== Классы ссылок ===== */
    function linkClasses(active: boolean): string[] {
        return [
            'relative whitespace-nowrap font-sans text-sm font-semibold transition-colors lg:text-base',
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
            'absolute -bottom-1 left-0 h-0.5 transition-all',
            active ? 'w-full' : 'w-0 group-hover:w-full',
            isDark.value ? 'bg-orange-400' : 'bg-rancho-buttercup',
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
        <!-- Скрытый слой для измерения ширины пунктов -->
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
                class="whitespace-nowrap font-sans text-sm font-semibold lg:text-base"
            >
                {{ item.name }}
            </span>
        </div>

        <!-- Видимые ссылки -->
        <Link
            v-for="item in visibleItems"
            :key="item.href"
            :href="item.href"
            class="group"
            :class="linkClasses(isActive(item))"
            :aria-current="isActive(item) ? 'page' : undefined"
        >
            {{ item.name }}
            <span :class="underlineClasses(isActive(item))" />
        </Link>

        <!-- Дропдаун «Ещё» -->
        <div v-if="overflowItems.length" class="relative">
            <button
                type="button"
                class="group flex items-center gap-1 whitespace-nowrap font-sans text-sm font-semibold transition-colors lg:text-base"
                :class="[
                    overflowItems.some((item) => isActive(item))
                        ? isDark
                            ? 'text-orange-400'
                            : 'text-rancho-pine'
                        : isDark
                          ? 'text-slate-200 hover:text-orange-400'
                          : 'text-rancho-forest hover:text-rancho-pine',
                ]"
                :aria-expanded="isDropdownOpen"
                aria-haspopup="menu"
                @click.stop="toggleDropdown"
            >
                Ещё
                <ChevronDownIcon
                    class="h-4 w-4 transition-transform"
                    :class="{ 'rotate-180': isDropdownOpen }"
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
                        class="flex items-center justify-between rounded-xl px-4 py-2.5 text-sm font-semibold transition-colors"
                        :class="
                            isActive(item)
                                ? isDark
                                    ? 'bg-orange-600/10 text-orange-400'
                                    : 'bg-rancho-pine/10 text-rancho-pine'
                                : isDark
                                  ? 'text-slate-200 hover:bg-slate-900'
                                  : 'text-rancho-forest hover:bg-rancho-olive/5'
                        "
                        role="menuitem"
                    >
                        <span>{{ item.name }}</span>
                        <span
                            v-if="isActive(item)"
                            class="h-1.5 w-1.5 rounded-full"
                            :class="isDark ? 'bg-orange-400' : 'bg-rancho-pine'"
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
