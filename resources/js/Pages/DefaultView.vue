<script setup lang="ts">
    import { type PropType, computed } from 'vue';

    import { Head, Link, usePage } from '@inertiajs/vue3';

    import CommentsSection from '@/Components/Sections/CommentsSection.vue';
    import MainLayout from '@/Layouts/MainLayout.vue';
    import type { Comment, Page, Paginated, SharedData } from '@/types';

    defineOptions({ layout: MainLayout });

    const props = defineProps({
        page: {
            type: Object as PropType<Page>,
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

    const pageData = computed(() => props.page);
</script>

<template>
    <AppContainer class="relative pb-14 lg:pb-8">
        <div class="min-h-screen bg-rancho-paper text-rancho-forest" aria-labelledby="page-title">
            <div class="mx-auto max-w-4xl px-6 py-10 md:py-16">
                <!-- BREADCRUMB -->
                <nav aria-label="Хлебные крошки" class="mb-8 text-sm text-rancho-olive">
                    <ol class="flex items-center gap-2">
                        <li>
                            <Link
                                :href="route('home')"
                                class="transition hover:text-rancho-pine focus:outline-none focus-visible:ring-2 focus-visible:ring-rancho-buttercup"
                            >
                                Главная
                            </Link>
                        </li>
                        <li aria-hidden="true" class="opacity-50">/</li>
                        <li class="font-semibold text-rancho-forest">
                            {{ pageData.title }}
                        </li>
                    </ol>
                </nav>

                <!-- HERO -->
                <header class="mb-12 border-b border-rancho-forest/10 pb-8">
                    <h1
                        id="page-title"
                        class="text-4xl font-black leading-tight tracking-tight md:text-5xl"
                    >
                        {{ pageData.title }}
                    </h1>

                    <p
                        v-if="pageData.seo?.description"
                        class="mt-4 max-w-3xl text-lg leading-relaxed text-rancho-olive"
                    >
                        {{ pageData.seo.description }}
                    </p>
                </header>

                <!-- MEDIA (SINGLE HERO IMAGE IF EXISTS) -->
                <section v-if="pageData.media?.length" class="mb-12" aria-label="Иллюстрация">
                    <figure
                        class="shadow-sm hover:shadow-md overflow-hidden rounded-3xl bg-white transition"
                    >
                        <img
                            :src="pageData.media[0].url"
                            :alt="pageData.media[0].name || pageData.title"
                            class="h-auto max-h-[500px] w-full object-cover"
                            loading="lazy"
                        />
                    </figure>
                </section>

                <!-- CONTENT -->
                <section
                    v-if="pageData.content"
                    class="prose prose-slate max-w-none prose-headings:font-bold prose-headings:text-rancho-forest prose-p:leading-relaxed prose-p:text-rancho-forest/90 prose-a:text-rancho-pine hover:prose-a:underline"
                    aria-label="Содержимое страницы"
                >
                    <div v-html="pageData.content" />
                </section>

                <!-- FALLBACK IF NO CONTENT -->
                <section
                    v-else
                    class="rounded-3xl border border-rancho-forest/10 bg-white/60 p-8 text-center text-rancho-olive"
                >
                    <p>Информация скоро появится.</p>
                </section>

                <!-- COMMENTS -->
                <div class="mx-auto mt-16 max-w-3xl">
                    <CommentsSection :comments="comments" :only-show="true" />
                </div>
            </div>
        </div>
    </AppContainer>
</template>
