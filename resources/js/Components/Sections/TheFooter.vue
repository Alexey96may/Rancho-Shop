<script setup lang="ts">
    import { computed } from 'vue';

    import { Link, usePage } from '@inertiajs/vue3';

    import { EnvelopeIcon, MapPinIcon, PhoneIcon } from '@heroicons/vue/24/outline';

    import CowIcon from '@/Components/Icons/CowIcon.vue';
    import type { SharedData } from '@/types';

    interface Props {
        theme?: 'light' | 'dark';
    }

    const props = withDefaults(defineProps<Props>(), {
        theme: 'light',
    });

    const page = usePage<SharedData>();
    const isDark = computed(() => props.theme === 'dark');

    const footer = computed(() => page.props.footer ?? {});

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

    function telHref(phone: string): string {
        return `tel:${phone.replace(/\D/g, '')}`;
    }

    /* ===== Единый focus для всех интерактивных элементов ===== */
    const focusClass = computed(
        () =>
            'rounded-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rancho-buttercup/60',
    );

    /* ===== Единый стиль ссылок футера ===== */
    const linkClass = computed(() => [
        'inline-block rounded-md px-1 py-0.5 transition-colors',
        'hover:text-rancho-buttercup hover:underline hover:decoration-2 hover:underline-offset-4',
        focusClass.value,
    ]);
</script>

<template>
    <footer
        class="rounded-t-2xl pb-8 pt-14 transition-colors sm:pt-16 md:rounded-t-[2rem]"
        :class="
            isDark
                ? 'border-t border-rancho-ink-800 bg-rancho-ink-900 text-rancho-ink-200'
                : 'border-t border-rancho-forest/20 bg-rancho-forest text-rancho-paper'
        "
    >
        <AppContainer>
            <div class="mb-16 grid grid-cols-1 gap-14 sm:grid-cols-2 sm:gap-9 lg:grid-cols-4">
                <!-- BRAND -->
                <div class="flex flex-col items-center gap-4">
                    <div class="flex items-center gap-3">
                        <CowIcon
                            class="h-8 w-8 shrink-0 rounded-full bg-rancho-buttercup p-1 text-rancho-ink-950 sm:h-12 sm:w-12"
                        />
                        <span
                            class="font-header text-xl tracking-wide text-rancho-paper sm:text-2xl"
                        >
                            {{ footer.site_name || 'Молочная Долина' }}
                        </span>
                    </div>

                    <p
                        v-if="footer.site_description"
                        class="max-w-xs text-center font-sans text-xs leading-relaxed opacity-80 sm:text-sm"
                    >
                        {{ footer.site_description }}
                    </p>

                    <div class="flex gap-3">
                        <a
                            v-if="footer.contact_telegram"
                            :href="footer.contact_telegram"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="rounded-md transition-colors"
                            :class="[
                                isDark
                                    ? 'text-rancho-ink-200 hover:text-rancho-buttercup'
                                    : 'text-rancho-paper hover:text-rancho-buttercup',
                                focusClass,
                            ]"
                            aria-label="Telegram"
                        >
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.91-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z"
                                />
                            </svg>
                        </a>

                        <a
                            v-if="footer.contact_vk"
                            :href="footer.contact_vk"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="rounded-md transition-colors"
                            :class="[
                                isDark
                                    ? 'text-rancho-ink-200 hover:text-rancho-buttercup'
                                    : 'text-rancho-paper hover:text-rancho-buttercup',
                                focusClass,
                            ]"
                            aria-label="VK"
                        >
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M13.162 18.994c.609 0 .858-.406.851-.915-.031-1.917.714-2.949 2.059-1.604 1.488 1.488 1.796 2.519 3.603 2.519h3.2c.808 0 1.126-.26 1.126-.668 0-.863-1.421-2.386-2.625-3.504-1.686-1.565-1.765-1.602-.313-3.486 1.801-2.339 4.157-5.336 2.073-5.336h-3.981c-.772 0-.828.435-1.103 1.083-.995 2.347-2.886 5.387-3.604 4.922-.751-.485-.407-2.406-.35-5.261.015-.754.011-1.271-1.141-1.539-.629-.145-1.241-.205-1.809-.205-2.273 0-3.841.953-2.95 1.119 1.571.293 1.42 3.692 1.054 5.16-.638 2.556-3.036-2.024-4.035-4.305-.241-.548-.315-.974-1.175-.974H.952c-.492 0-.787.16-.787.516 0 .602 2.96 6.72 5.786 9.77 2.756 2.975 5.48 2.708 7.211 2.708z"
                                />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- SECTIONS -->
                <div class="flex flex-col items-center">
                    <h4 class="mb-6 font-header text-lg text-rancho-buttercup sm:text-xl">
                        Разделы
                    </h4>
                    <ul class="space-y-4 font-sans text-sm opacity-90">
                        <li v-for="section in sectionsItems" :key="section.pathName">
                            <Link :href="route(section.pathName)" :class="linkClass">
                                {{ section.name }}
                            </Link>
                        </li>
                    </ul>
                </div>

                <!-- PRODUCTION -->
                <div class="flex flex-col items-center">
                    <h4 class="mb-6 font-header text-lg text-rancho-buttercup sm:text-xl">
                        Продукция
                    </h4>
                    <ul class="space-y-4 font-sans text-sm opacity-90">
                        <li v-for="production in productionItems" :key="production.params">
                            <Link
                                :href="route(production.pathName, { category: production.params })"
                                :class="linkClass"
                            >
                                {{ production.name }}
                            </Link>
                        </li>
                    </ul>
                </div>

                <!-- CONTACTS -->
                <div class="flex flex-col items-center">
                    <h4 class="mb-6 font-header text-lg text-rancho-buttercup sm:text-xl">
                        Связаться с нами
                    </h4>
                    <ul class="space-y-4 font-sans text-sm opacity-90">
                        <li v-if="footer.address_farm" class="flex items-start gap-3">
                            <MapPinIcon
                                class="h-5 w-5 shrink-0 text-rancho-buttercup"
                                aria-hidden="true"
                            />
                            <span>{{ footer.address_farm }}</span>
                        </li>

                        <li v-if="footer.contact_phone" class="flex items-center gap-3">
                            <PhoneIcon
                                class="h-5 w-5 shrink-0 text-rancho-buttercup"
                                aria-hidden="true"
                            />
                            <a :href="telHref(footer.contact_phone)" :class="linkClass">
                                {{ footer.contact_phone }}
                            </a>
                        </li>

                        <li v-if="footer.contact_email" class="flex items-center gap-3">
                            <EnvelopeIcon
                                class="h-5 w-5 shrink-0 text-rancho-buttercup"
                                aria-hidden="true"
                            />
                            <a :href="`mailto:${footer.contact_email}`" :class="linkClass">
                                {{ footer.contact_email }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- BOTTOM -->
            <div
                class="flex flex-col justify-between gap-6 border-t pt-8 sm:gap-4 md:flex-row md:items-center"
                :class="isDark ? 'border-rancho-ink-800' : 'border-rancho-paper/10'"
            >
                <p class="text-center font-sans text-xs opacity-60">
                    © {{ new Date().getFullYear() }} {{ footer.site_name || 'Молочная Долина' }}.
                    Сделано с любовью к природе.
                </p>

                <div
                    class="flex flex-wrap justify-center gap-x-6 gap-y-2 font-sans text-xs opacity-80"
                >
                    <Link
                        :href="route('pages.show', 'privacy')"
                        :class="[linkClass, 'hover:opacity-100']"
                    >
                        Политика конфиденциальности
                    </Link>

                    <Link
                        :href="route('pages.show', 'offer')"
                        :class="[linkClass, 'hover:opacity-100']"
                    >
                        Публичная оферта
                    </Link>
                </div>
            </div>
        </AppContainer>
    </footer>
</template>
