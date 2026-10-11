<script setup lang="ts">
    import { type PropType, computed } from 'vue';

    import { Head, Link, usePage } from '@inertiajs/vue3';

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

    const pageData = computed<Page>(() => props.page.data);
</script>

<template>
    <AppContainer class="relative pb-14 lg:pb-8">
        <div class="bg-rancho-paper text-rancho-forest" aria-labelledby="page-title">
            <div class="py-10 md:py-16">
                <!-- HERO -->
                <header v-scroll-reveal class="mb-12 border-b border-rancho-forest/10 pb-8">
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
                <!-- CONTENT -->
                <section
                    v-scroll-reveal
                    v-if="pageData.content"
                    class="prose prose-slate max-w-none prose-headings:font-bold prose-headings:text-rancho-forest prose-p:leading-relaxed prose-p:text-rancho-forest/90 prose-a:text-rancho-pine hover:prose-a:underline"
                    aria-label="Содержимое страницы"
                >
                    <div v-html="pageData.content" />
                </section>

                <!-- FALLBACK IF NO CONTENT -->
                <section
                    v-scroll-reveal
                    v-else
                    class="rounded-3xl border border-rancho-forest/10 bg-white/60 p-8 text-center text-rancho-olive"
                >
                    <p>Информация скоро появится.</p>
                </section>

                {{ String(pageData.is_commentable) }}

                <!-- COMMENTS -->
                <div v-if="pageData.is_commentable" v-scroll-reveal>
                    <CommentsSection :comments="comments" :only-show="true" />
                </div>
            </div>
        </div>
    </AppContainer>
</template>
