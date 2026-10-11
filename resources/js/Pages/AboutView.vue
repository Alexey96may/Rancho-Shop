<script setup lang="ts">
    import { PropType, computed } from 'vue';

    import { Link } from '@inertiajs/vue3';

    import CommentsSection from '@/Components/Sections/CommentsSection.vue';
    import MainLayout from '@/Layouts/MainLayout.vue';
    import type { Comment, Page, Paginated, ResourceSingle } from '@/types';

    defineOptions({ layout: MainLayout });

    const props = defineProps({
        page: {
            type: Object as PropType<ResourceSingle<Page>>,
            required: true,
            validator: (value: Page) => {
                return Boolean(value && typeof value.id !== 'undefined' && value.title);
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
        {
            icon: '🏡',
            title: 'Экскурсии',
            desc: 'Приезжайте в гости: покажем ферму, познакомим с животными и угостим свежей продукцией.',
            route: 'catalog.index',
            cta: 'Записаться',
        },
    ];
</script>

<template>
    <AppContainer class="relative pb-14 lg:pb-8">
        <div class="min-h-screen bg-rancho-paper text-rancho-forest" aria-labelledby="farm-title">
            <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 sm:py-10 md:py-16">
                <!-- MAIN CONTENT -->
                <section
                    v-if="pageData.content"
                    class="scroll-reveal prose prose-slate max-w-none prose-headings:font-bold prose-headings:text-rancho-forest prose-p:leading-relaxed prose-p:text-rancho-forest/90 prose-a:text-rancho-pine hover:prose-a:underline"
                    aria-label="Описание фермы"
                >
                    <div v-html="pageData.content"></div>
                </section>

                <!-- HIGHLIGHTS -->
                <section v-scroll-reveal class="mt-12 sm:mt-16" aria-label="Преимущества фермы">
                    <h2 class="mb-5 text-xl font-extrabold text-rancho-forest sm:mb-6 sm:text-2xl">
                        Почему именно наше ранчо
                    </h2>
                    <div class="grid gap-4 sm:grid-cols-3 sm:gap-6">
                        <div
                            v-for="item in farmHighlights"
                            :key="item.title"
                            class="hover:shadow-lg group rounded-2xl border border-rancho-forest/10 bg-white/80 p-5 backdrop-blur-sm transition hover:border-rancho-pine/30 hover:shadow-rancho-forest/5 sm:rounded-3xl sm:p-6"
                        >
                            <div class="mb-3 text-3xl transition-transform group-hover:scale-110">
                                {{ item.icon }}
                            </div>
                            <h3 class="text-base font-bold text-rancho-forest sm:text-lg">
                                {{ item.title }}
                            </h3>
                            <p class="mt-1 text-sm text-rancho-olive">{{ item.desc }}</p>
                        </div>
                    </div>
                </section>

                <!-- SECTIONS NAVIGATION -->
                <section v-scroll-reveal class="mt-16 sm:mt-20" aria-label="Разделы фермы">
                    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <h2 class="text-xl font-extrabold text-rancho-forest sm:text-2xl">
                                Что у нас есть
                            </h2>
                            <p class="mt-2 max-w-2xl text-sm text-rancho-olive sm:text-base">
                                Загляните в каждый раздел — там подробно о животных, продукции,
                                растениях и о том, как к нам попасть.
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2 sm:gap-6">
                        <Link
                            v-for="section in sections"
                            :key="section.title"
                            :href="route(section.route)"
                            class="hover:shadow-lg group flex flex-col rounded-2xl border border-rancho-forest/10 bg-white/80 p-5 backdrop-blur-sm transition hover:border-rancho-pine/40 hover:shadow-rancho-forest/5 sm:rounded-3xl sm:p-6"
                        >
                            <div class="mb-3 flex items-center gap-3">
                                <span
                                    class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-rancho-paper text-2xl transition group-hover:scale-110"
                                    aria-hidden="true"
                                >
                                    {{ section.icon }}
                                </span>
                                <h3 class="text-lg font-bold text-rancho-forest">
                                    {{ section.title }}
                                </h3>
                            </div>

                            <p class="text-sm text-rancho-olive sm:text-base">
                                {{ section.desc }}
                            </p>

                            <span
                                class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-rancho-pine transition group-hover:gap-2.5"
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
                        <span class="mb-2 block text-xl sm:text-4xl">🌾</span>
                        <h2 class="text-xl font-black sm:text-3xl md:text-4xl">
                            Познакомьтесь с обитателями
                        </h2>
                        <p class="mt-3 text-sm text-rancho-paper/80 sm:text-base">
                            Узнайте больше о наших животных, их рационе и жизни на свежем воздухе.
                        </p>
                        <div class="mt-6 sm:mt-8">
                            <Link
                                :href="route('animals.index')"
                                class="shadow-md hover:shadow-lg inline-flex items-center gap-2 rounded-xl bg-rancho-buttercup px-6 py-3 text-sm font-bold text-rancho-forest transition hover:bg-white focus:outline-none focus-visible:ring-4 focus-visible:ring-white sm:px-8 sm:py-4 sm:text-base"
                            >
                                <span class="text-xs sm:text-lg">Перейти к жителям фермы</span>
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
        </div>
    </AppContainer>
</template>
