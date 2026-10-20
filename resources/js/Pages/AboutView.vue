<script setup lang="ts">
    import { type PropType, computed } from 'vue';

    import { Link, usePage } from '@inertiajs/vue3';

    import {
        ClockIcon,
        HeartIcon,
        ShoppingBagIcon,
        SparklesIcon,
        SunIcon,
    } from '@heroicons/vue/24/outline';

    import CowIcon from '@/Components/Icons/CowIcon.vue';
    import CowIconDark from '@/Components/Icons/CowIconDark.vue';
    import CommentsSection from '@/Components/Sections/CommentsSection.vue';
    import MainLayout from '@/Layouts/MainLayout.vue';
    import { useComments } from '@/composables/crud/useComments';
    import type { Comment, Page, Paginated, ResourceSingle, SharedData } from '@/types';

    defineOptions({ layout: MainLayout });

    const props = defineProps({
        page: {
            type: Object as PropType<ResourceSingle<Page>>,
            required: true,
        },
        comments: {
            type: Object as PropType<Paginated<Comment>>,
            required: true,
        },
    });

    const pageData = computed(() => props.page.data);

    /** Преимущества */
    const farmHighlights = [
        {
            icon: SparklesIcon,
            title: 'Натурально',
            desc: 'Без антибиотиков, гормонов роста и лишней химии — только то, что даёт сама природа.',
        },
        {
            icon: HeartIcon,
            title: 'Своё стадо',
            desc: 'Свободный выпас, собственные корма и забота о каждом животном.',
        },
        {
            icon: ClockIcon,
            title: 'Свежесть',
            desc: 'Доставляем по графику — молоко и сыры приезжают к вам в день производства.',
        },
    ];

    /** Разделы — только те, что есть */
    const sections = [
        {
            icon: CowIcon,
            title: 'Животные',
            desc: 'Познакомьтесь с обитателями фермы — у каждого есть имя, характер и своя история.',
            route: 'animals.index',
            cta: 'Познакомиться',
        },
        {
            icon: ShoppingBagIcon,
            title: 'Продукция',
            desc: 'Молоко, сыры, масло, творог и другие продукты собственного производства — без посредников.',
            route: 'catalog.index',
            cta: 'Смотреть каталог',
        },
        {
            icon: SunIcon,
            title: 'Растениеводство',
            desc: 'Зерновые, овощи и травы, выращенные на собственных полях.',
            route: 'catalog.index',
            cta: 'Узнать больше',
        },
    ];

    const { submitComment, errors } = useComments('page', props.page.data.id);

    const usedPage = usePage<SharedData>();
    const isAuthenticated = computed(() => !!usedPage.props.auth?.user);
</script>

<template>
    <AppContainer class="min-h-screen bg-rancho-paper">
        <div class="space-y-12 pt-12">
            <!-- MAIN CONTENT -->
            <section
                v-if="pageData.content"
                v-scroll-reveal
                class="prose prose-stone max-w-none prose-headings:font-black prose-headings:text-rancho-forest prose-p:leading-relaxed prose-p:text-rancho-ink-700 prose-a:text-rancho-pine prose-a:no-underline hover:prose-a:underline prose-blockquote:border-l-4 prose-blockquote:border-rancho-buttercup prose-blockquote:bg-rancho-ink-50 prose-blockquote:py-1 prose-blockquote:pl-4 prose-blockquote:not-italic prose-blockquote:text-rancho-ink-700 prose-strong:text-rancho-forest prose-code:rounded prose-code:bg-rancho-ink-100 prose-code:px-1.5 prose-code:py-0.5 prose-code:text-rancho-forest prose-code:before:content-none prose-code:after:content-none"
                aria-label="Описание фермы"
            >
                <div v-html="pageData.content"></div>
            </section>

            <!-- HIGHLIGHTS -->
            <section v-scroll-reveal aria-label="Преимущества фермы" class="space-y-10">
                <h2 class="mb-5 text-xl font-black text-rancho-forest sm:mb-6 sm:text-2xl">
                    Почему именно наше ранчо
                </h2>

                <div class="grid gap-4 sm:grid-cols-3 sm:gap-6">
                    <div
                        v-for="item in farmHighlights"
                        :key="item.title"
                        class="shadow-sm hover:shadow-md group rounded-2xl border border-rancho-ink-200 bg-white p-5 transition-all hover:-translate-y-0.5 hover:border-rancho-ink-300 sm:rounded-3xl sm:p-6"
                    >
                        <div
                            class="mb-3 inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-rancho-pine/10 text-rancho-pine transition-transform duration-300 group-hover:scale-110"
                            aria-hidden="true"
                        >
                            <component :is="item.icon" class="h-6 w-6" />
                        </div>

                        <h3 class="text-base font-bold text-rancho-forest sm:text-lg">
                            {{ item.title }}
                        </h3>

                        <p class="mt-1.5 text-sm leading-relaxed text-rancho-ink-600">
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
                        class="mt-2 max-w-2xl text-sm leading-relaxed text-rancho-ink-600 sm:text-base"
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
                        class="shadow-sm hover:shadow-md group flex flex-col rounded-2xl border border-rancho-ink-200 bg-white p-5 transition-all hover:-translate-y-0.5 hover:border-rancho-ink-300 sm:rounded-3xl sm:p-6"
                    >
                        <div class="mb-3 flex items-center gap-3">
                            <span
                                class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-rancho-pine/10 text-rancho-pine transition-transform duration-300 group-hover:scale-110"
                                aria-hidden="true"
                            >
                                <component :is="section.icon" class="h-6 w-6" />
                            </span>
                            <h3 class="text-lg font-bold text-rancho-forest">
                                {{ section.title }}
                            </h3>
                        </div>

                        <p class="text-sm leading-relaxed text-rancho-ink-600 sm:text-base">
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
                    <div
                        class="mb-4 inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-rancho-buttercup/20 text-rancho-buttercup"
                        aria-hidden="true"
                    >
                        <CowIconDark class="h-8 w-8" />
                    </div>

                    <h2 class="text-xl font-black sm:text-3xl md:text-4xl">Загляните в гости</h2>

                    <p class="mt-3 text-sm leading-relaxed text-rancho-paper/80 sm:text-base">
                        Познакомьтесь с нашими животными — у каждого есть имя, характер и своя
                        история.
                    </p>

                    <div class="mt-6 sm:mt-8">
                        <Link
                            :href="route('animals.index')"
                            class="shadow-md hover:shadow-lg inline-flex items-center gap-2 rounded-xl bg-rancho-buttercup px-5 py-3 text-sm font-bold text-rancho-ink-950 transition-all hover:-translate-y-0.5 hover:bg-rancho-buttercup/90 focus:outline-none focus-visible:ring-4 focus-visible:ring-rancho-buttercup/40 active:scale-95 sm:px-7 sm:py-3.5 sm:text-base"
                        >
                            <span>К нашим животным</span>
                            <span aria-hidden="true">→</span>
                        </Link>
                    </div>
                </div>
            </section>

            <!-- REVIEWS / COMMENTS -->
            <div v-if="pageData.is_commentable" v-scroll-reveal>
                <CommentsSection
                    :comments="comments"
                    @submit="submitComment"
                    :errors="errors"
                    :is-authenticated="isAuthenticated"
                />
            </div>
        </div>
    </AppContainer>
</template>
