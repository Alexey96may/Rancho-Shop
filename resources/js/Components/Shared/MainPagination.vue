<script setup lang="ts">
    import { computed, onMounted, onUnmounted, ref } from 'vue';

    import { Link } from '@inertiajs/vue3';

    import type { PaginationLink } from '@/types';

    type EllipsisLink = { __ellipsis: true; key: string };
    type DisplayLink = PaginationLink | EllipsisLink;

    const props = withDefaults(
        defineProps<{
            links: PaginationLink[];
            theme?: 'light' | 'dark';
        }>(),
        {
            theme: 'dark',
        },
    );

    const isDark = computed(() => props.theme === 'dark');

    /* ---------- Отслеживание ширины экрана ---------- */

    const viewportWidth = ref(typeof window !== 'undefined' ? window.innerWidth : 1280);

    function handleResize() {
        if (typeof window !== 'object') return;
        viewportWidth.value = window.innerWidth;
    }

    onMounted(() => {
        if (typeof window !== 'object') return;
        window.addEventListener('resize', handleResize, { passive: true });
    });

    onUnmounted(() => {
        if (typeof window !== 'object') return;
        window.removeEventListener('resize', handleResize);
    });

    /* ---------- Сколько номеров показывать ---------- */

    const maxPageSlots = computed(() => {
        if (viewportWidth.value < 480) return 3; // мобилка портрет
        if (viewportWidth.value < 768) return 5; // мобилка ландшафт / планшет портрет
        if (viewportWidth.value < 1024) return 7; // планшет / маленький десктоп
        return 9; // десктоп
    });

    /* ---------- Умная обрезка links ---------- */

    const visibleLinks = computed<DisplayLink[]>(() => {
        const arr = props.links;
        if (arr.length <= 3) return arr;

        const prev = arr[0];
        const next = arr[arr.length - 1];
        const pages = arr.slice(1, -1);

        const slots = maxPageSlots.value;

        // Всё влезает — не режем
        if (pages.length <= slots) {
            return [prev, ...pages, next];
        }

        const activeIdx = pages.findIndex((p) => p.active);
        const total = pages.length;

        // 2 слота забираем под первую и последнюю страницы
        const middleSlots = Math.max(1, slots - 2);
        const half = Math.floor(middleSlots / 2);

        const center = activeIdx === -1 ? 0 : activeIdx;

        let start = Math.max(1, center - half);
        let end = Math.min(total - 2, center + half);

        // Корректировка у границ (когда активная ближе к первой или последней)
        if (end - start + 1 < middleSlots) {
            if (start === 1) {
                end = Math.min(total - 2, start + middleSlots - 1);
            } else if (end === total - 2) {
                start = Math.max(1, end - middleSlots + 1);
            }
        }

        const result: DisplayLink[] = [prev, pages[0]];

        if (start > 1) {
            result.push({ __ellipsis: true, key: 'start' });
        }

        for (let i = start; i <= end; i++) {
            result.push(pages[i]);
        }

        if (end < total - 2) {
            result.push({ __ellipsis: true, key: 'end' });
        }

        // Последняя страница (если больше 1)
        if (total > 1) {
            result.push(pages[total - 1]);
        }

        result.push(next);

        return result;
    });

    function isEllipsis(link: DisplayLink): link is EllipsisLink {
        return '__ellipsis' in link;
    }
</script>

<template>
    <Transition name="fade-slide" mode="out-in">
        <nav
            v-if="visibleLinks.length > 3"
            role="navigation"
            aria-label="Навигация по страницам"
            class="mt-8 flex justify-center sm:mt-12"
        >
            <div
                class="flex max-w-full items-center gap-1 overflow-x-auto rounded-2xl border px-4 py-2 backdrop-blur-sm sm:gap-1 sm:p-1.5"
                :class="
                    isDark ? 'border-slate-800 bg-slate-900/80' : 'border-slate-200 bg-white/80'
                "
            >
                <template v-for="(link, k) in visibleLinks" :key="k">
                    <!-- Многоточие -->
                    <span
                        v-if="isEllipsis(link)"
                        class="flex h-8 min-w-[28px] select-none items-center justify-center px-1 text-sm font-black sm:h-10 sm:min-w-[40px] sm:px-2"
                        :class="isDark ? 'text-slate-600' : 'text-slate-400'"
                        aria-hidden="true"
                    >
                        …
                    </span>

                    <!-- Заглушка (disabled, например "Previous" на первой странице) -->
                    <div
                        v-else-if="link.url === null"
                        class="flex h-8 min-w-[28px] select-none items-center justify-center gap-4 text-nowrap px-2 text-[10px] font-black uppercase tracking-widest opacity-50 sm:h-10 sm:min-w-[40px] sm:px-3"
                        :class="isDark ? 'text-slate-600' : 'text-slate-400'"
                        v-html="link.label"
                        aria-hidden="true"
                    />

                    <!-- Рабочая ссылка -->
                    <Link
                        v-else
                        :href="link.url"
                        class="flex h-8 min-w-[28px] items-center justify-center text-nowrap rounded-xl px-2 text-[10px] font-black uppercase tracking-widest transition-all focus:outline-none focus:ring-2 focus:ring-orange-500 sm:h-10 sm:min-w-[40px] sm:px-4"
                        :class="[
                            link.active
                                ? 'bg-orange-600 text-white shadow-[0_0_20px_rgba(234,88,12,0.4)]'
                                : isDark
                                  ? 'text-slate-400 hover:bg-slate-800 hover:text-white'
                                  : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900',
                        ]"
                        :aria-current="link.active ? 'page' : undefined"
                        :aria-label="`Перейти на страницу ${link.label}`"
                        preserve-scroll
                        preserve-state
                        v-html="link.label"
                    />
                </template>
            </div>
        </nav>
    </Transition>
</template>

<style scoped>
    :deep(span) {
        font-family: inherit;
        font-size: inherit;
    }

    .fade-slide-enter-active {
        transition: all 0.4s ease-out;
    }
    .fade-slide-enter-from {
        opacity: 0;
        transform: translateY(-10px);
    }

    /* Скрываем нативный скроллбар на узких экранах */
    nav > div::-webkit-scrollbar {
        height: 0;
        display: none;
    }
    nav > div {
        scrollbar-width: none;
    }
</style>
