<script setup lang="ts">
    import { computed } from 'vue';

    import { Link } from '@inertiajs/vue3';

    interface Props {
        theme?: 'light' | 'dark';
    }

    const props = withDefaults(defineProps<Props>(), {
        theme: 'light',
    });

    const isDark = computed(() => props.theme === 'dark');

    const sectionsItems = [
        { name: 'Весь Каталог', pathName: 'catalog.index' },
        { name: 'Наши Животные', pathName: 'animals.index' },
        { name: 'История Ранчо', pathName: 'about' },
        { name: 'Оплата и Доставка', pathName: 'delivery' },
    ];

    const productionItems = [
        { name: 'Свежее Молоко', pathName: 'catalog.index', params: 1 },
        { name: 'Домашние Сыры', pathName: 'catalog.index', params: 2 },
        { name: 'Творог', pathName: 'catalog.index', params: 5 },
        { name: 'Фермерские Яйца', pathName: 'catalog.index', params: 15 },
    ];
</script>

<template>
    <footer
        class="rounded-t-[1rem] pb-8 pt-16 transition-colors md:rounded-t-[2rem]"
        :class="
            isDark
                ? 'border-t border-sky-600 bg-slate-950 text-slate-200'
                : 'border-t border-emerald-800 bg-rancho-forest text-rancho-paper'
        "
    >
        <AppContainer>
            <div class="mb-16 grid grid-cols-1 gap-12 md:grid-cols-2 lg:grid-cols-4">
                <!-- BRAND -->
                <div class="flex flex-col gap-6">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-full transition-colors"
                            :class="
                                isDark
                                    ? 'bg-orange-500 text-slate-950'
                                    : 'bg-rancho-buttercup text-rancho-forest'
                            "
                        >
                            <span class="font-header text-xl">МД</span>
                        </div>
                        <span
                            class="font-header text-2xl tracking-wide transition-colors"
                            :class="isDark ? 'text-slate-100' : 'text-rancho-paper'"
                        >
                            Молочная Долина
                        </span>
                    </div>

                    <p
                        class="max-w-xs font-sans text-sm leading-relaxed opacity-80 transition-colors"
                        :class="isDark ? 'text-slate-400' : 'text-rancho-paper'"
                    >
                        Мы верим, что еда должна быть честной. Наше ранчо — это место, где природа и
                        современные технологии встречаются для создания идеальных продуктов.
                    </p>

                    <div class="flex gap-4">
                        <a
                            href="https://t.me/elenikaglossa"
                            target="_blank"
                            class="transition-colors"
                            :class="
                                isDark
                                    ? 'text-slate-400 hover:text-orange-400'
                                    : 'text-rancho-paper hover:text-rancho-buttercup'
                            "
                        >
                            <span class="sr-only">Telegram</span>
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.91-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z"
                                />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- SECTIONS -->
                <div>
                    <h4
                        class="mb-6 font-header text-xl transition-colors"
                        :class="isDark ? 'text-orange-400' : 'text-rancho-buttercup'"
                    >
                        Разделы
                    </h4>
                    <ul class="space-y-4 font-sans text-sm opacity-90">
                        <li v-for="section in sectionsItems" :key="section.pathName">
                            <Link
                                :href="route(section.pathName)"
                                class="inline-block transition-transform hover:translate-x-2"
                            >
                                {{ section.name }}
                            </Link>
                        </li>
                    </ul>
                </div>

                <!-- PRODUCTION -->
                <div>
                    <h4
                        class="mb-6 font-header text-xl transition-colors"
                        :class="isDark ? 'text-orange-400' : 'text-rancho-buttercup'"
                    >
                        Продукция
                    </h4>
                    <ul class="space-y-4 font-sans text-sm opacity-90">
                        <li v-for="production in productionItems" :key="production.params">
                            <Link
                                :href="route(production.pathName, { category: production.params })"
                                class="transition-colors"
                                :class="
                                    isDark ? 'hover:text-orange-400' : 'hover:text-rancho-buttercup'
                                "
                            >
                                {{ production.name }}
                            </Link>
                        </li>
                    </ul>
                </div>

                <!-- CONTACTS -->
                <div>
                    <h4
                        class="mb-6 font-header text-xl transition-colors"
                        :class="isDark ? 'text-orange-400' : 'text-rancho-buttercup'"
                    >
                        Связаться
                    </h4>
                    <ul class="space-y-4 font-sans text-sm opacity-90">
                        <li class="flex items-start gap-3">
                            <svg
                                class="h-5 w-5 shrink-0 transition-colors"
                                :class="isDark ? 'text-orange-400' : 'text-rancho-buttercup'"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                                />
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                                />
                            </svg>
                            <span>Крым, Симферопольский р-н,<br />с. Долинное</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <svg
                                class="h-5 w-5 transition-colors"
                                :class="isDark ? 'text-orange-400' : 'text-rancho-buttercup'"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"
                                />
                            </svg>
                            <a href="tel:79780000000">+7 (978) 000-00-00</a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- BOTTOM -->
            <div
                class="flex flex-col items-center justify-between gap-4 border-t pt-8 transition-colors md:flex-row"
                :class="isDark ? 'border-slate-800' : 'border-rancho-paper/10'"
            >
                <p
                    class="font-sans text-xs opacity-60 transition-colors"
                    :class="isDark ? 'text-slate-500' : 'text-rancho-paper'"
                >
                    © 2026 Молочная Долина. Сделано с любовью к природе.
                </p>
                <div
                    class="flex gap-6 font-sans text-xs opacity-60 transition-colors"
                    :class="isDark ? 'text-slate-500' : 'text-rancho-paper'"
                >
                    <a href="#" class="hover:opacity-100">Политика конфиденциальности</a>
                    <a href="#" class="hover:opacity-100">Публичная оферта</a>
                </div>
            </div>
        </AppContainer>
    </footer>
</template>
