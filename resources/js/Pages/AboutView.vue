<script setup lang="ts">
    import { type PropType, computed } from 'vue';

    import { Link } from '@inertiajs/vue3';

    import CommentsSection from '@/Components/Sections/CommentsSection.vue';
    import MainLayout from '@/Layouts/MainLayout.vue';
    import type { Comment, Page, Paginated, ResourceSingle } from '@/types';

    defineOptions({ layout: MainLayout });

    const props = defineProps({
        page: {
            type: Object as PropType<ResourceSingle<Page>>,
            required: true,
            validator: (value: ResourceSingle<Page>) => {
                return Boolean(
                    value &&
                    typeof value === 'object' &&
                    value.data &&
                    typeof value.data.id !== 'undefined' &&
                    value.data.title,
                );
            },
        },
        comments: {
            type: Object as PropType<Paginated<Comment>>,
            required: true,
            validator: (value: Paginated<Comment>) => {
                return Boolean(value && Array.isArray(value.data));
            },
        },
    });

    const pageData = computed(() => props.page.data);

    const farmHighlights = [
        {
            icon: '🌿',
            title: 'Экологичность',
            desc: 'Натуральное производство без химикатов, антибиотиков и гормонов роста.',
        },
        {
            icon: '🐄',
            title: 'Своё стадо',
            desc: 'Свободный выпас, забота о каждом животном и собственные корма.',
        },
        {
            icon: '🚜',
            title: 'Свежесть',
            desc: 'Быстрая локальная доставка — от фермы до вашего стола за считанные часы.',
        },
    ];

    const sections = [
        {
            icon: '🐐',
            title: 'Животные',
            desc: 'Козы, коровы, овцы, птица и другие обитатели фермы — с фотографиями, именами и историями.',
            route: 'animals.index',
            cta: 'Познакомиться',
        },
        {
            icon: '🧀',
            title: 'Продукция',
            desc: 'Сыры, молоко, масло, творог, мясо и яйца — всё собственного производства, без посредников.',
            route: 'catalog.index',
            cta: 'Смотреть каталог',
        },
        {
            icon: '🌾',
            title: 'Растениеводство',
            desc: 'Зерновые, овощи, травы и кормовые культуры, выращенные на собственных полях.',
            route: 'catalog.index',
            cta: 'Узнать больше',
        },
    ];
</script>

<template>
    <AppContainer class="min-h-screen bg-rancho-paper">
        <div class="py-12">
            <!-- MAIN CONTENT -->
            <section
                v-if="pageData.content"
                v-scroll-reveal
                class="prose-p:text-rancho-ink-700 prose-blockquote:bg-rancho-ink-50 prose-blockquote:text-rancho-ink-700 prose-code:bg-rancho-ink-100 prose prose-stone max-w-none prose-headings:font-black prose-headings:text-rancho-forest prose-p:leading-relaxed prose-a:text-rancho-pine prose-a:no-underline hover:prose-a:underline prose-blockquote:border-l-4 prose-blockquote:border-rancho-buttercup prose-blockquote:py-1 prose-blockquote:pl-4 prose-blockquote:not-italic prose-strong:text-rancho-forest prose-code:rounded prose-code:px-1.5 prose-code:py-0.5 prose-code:text-rancho-forest prose-code:before:content-none prose-code:after:content-none"
                aria-label="Описание фермы"
            >
                <div v-html="pageData.content"></div>
            </section>

            <!-- HIGHLIGHTS -->
            <section v-scroll-reveal aria-label="Преимущества фермы">
                <h2 class="mb-5 text-xl font-black text-rancho-forest sm:mb-6 sm:text-2xl">
                    Почему именно наше ранчо
                </h2>

                <div class="grid gap-4 sm:grid-cols-3 sm:gap-6">
                    <div
                        v-for="item in farmHighlights"
                        :key="item.title"
                        class="border-rancho-ink-200 shadow-sm hover:border-rancho-ink-300 hover:shadow-md group rounded-2xl border bg-white p-5 transition-all hover:-translate-y-0.5 sm:rounded-3xl sm:p-6"
                    >
                        <div
                            class="mb-3 text-3xl transition-transform duration-300 group-hover:scale-110"
                            aria-hidden="true"
                        >
                            {{ item.icon }}
                        </div>
                        <h3 class="text-base font-bold text-rancho-forest sm:text-lg">
                            {{ item.title }}
                        </h3>
                        <p class="text-rancho-ink-600 mt-1.5 text-sm leading-relaxed">
                            {{ item.desc }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- SECTIONS NAVIGATION -->
            <section v-scroll-reveal class="mt-16 sm:mt-20" aria-label="Разделы фермы">
                <div class="mb-6">
                    <h2 class="text-xl font-black text-rancho-forest sm:text-2xl">
                        Что у нас есть
                    </h2>
                    <p
                        class="text-rancho-ink-600 mt-2 max-w-2xl text-sm leading-relaxed sm:text-base"
                    >
                        Загляните в каждый раздел — там подробно о животных, продукции, растениях и
                        о том, как к нам попасть.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-6">
                    <Link
                        v-for="section in sections"
                        :key="section.title"
                        :href="route(section.route)"
                        class="border-rancho-ink-200 shadow-sm hover:border-rancho-ink-300 hover:shadow-md group flex flex-col rounded-2xl border bg-white p-5 transition-all hover:-translate-y-0.5 sm:rounded-3xl sm:p-6"
                    >
                        <div class="mb-3 flex items-center gap-3">
                            <span
                                class="bg-rancho-ink-50 inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl text-2xl transition-transform duration-300 group-hover:scale-110"
                                aria-hidden="true"
                            >
                                {{ section.icon }}
                            </span>
                            <h3 class="text-lg font-bold text-rancho-forest">
                                {{ section.title }}
                            </h3>
                        </div>

                        <p class="text-rancho-ink-600 text-sm leading-relaxed sm:text-base">
                            {{ section.desc }}
                        </p>

                        <span
                            class="mt-4 inline-flex items-center gap-1.5 text-sm font-bold text-rancho-pine transition-all group-hover:gap-2.5 group-hover:text-rancho-forest"
                        >
                            {{ section.cta }}
                            <span aria-hidden="true">→</span>
                        </span>
                    </Link>
                </div>
            </section>

            <!-- CALL TO ACTION -->
            <section
                v-scroll-reveal
                class="shadow-xl mt-14 overflow-hidden rounded-2xl bg-rancho-forest p-6 text-center text-white sm:mt-20 sm:rounded-3xl sm:p-8 md:p-12"
                aria-label="Призыв к действию"
            >
                <div class="mx-auto max-w-2xl">
                    <span class="mb-3 block text-3xl sm:text-4xl" aria-hidden="true">🌾</span>

                    <h2 class="text-xl font-black sm:text-3xl md:text-4xl">
                        Познакомьтесь с обитателями
                    </h2>

                    <p class="mt-3 text-sm leading-relaxed text-rancho-paper/80 sm:text-base">
                        Узнайте больше о наших животных, их рационе и жизни на свежем воздухе.
                    </p>

                    <div class="mt-6 sm:mt-8">
                        <Link
                            :href="route('animals.index')"
                            class="text-rancho-ink-950 shadow-md hover:shadow-lg inline-flex items-center gap-2 rounded-xl bg-rancho-buttercup px-5 py-3 text-sm font-bold transition-all hover:-translate-y-0.5 hover:bg-rancho-buttercup/90 focus:outline-none focus-visible:ring-4 focus-visible:ring-rancho-buttercup/40 active:scale-95 sm:px-7 sm:py-3.5 sm:text-base"
                        >
                            <span>Перейти к жителям фермы</span>
                            <span aria-hidden="true">→</span>
                        </Link>
                    </div>
                </div>
            </section>

            <!-- REVIEWS / COMMENTS -->
            <div v-if="pageData.is_commentable" v-scroll-reveal>
                <CommentsSection :comments="comments" :only-show="true" />
            </div>
        </div>
    </AppContainer>
</template>
