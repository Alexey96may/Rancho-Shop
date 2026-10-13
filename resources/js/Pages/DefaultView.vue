<script setup lang="ts">
    import { type PropType, computed } from 'vue';

    import { Link, usePage } from '@inertiajs/vue3';

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

    const pageData = computed<Page>(() => props.page.data);
</script>

<template>
    <AppContainer class="min-h-screen bg-rancho-paper">
        <div class="mx-auto max-w-4xl py-10 sm:py-14 md:py-20">
            <!-- HERO -->
            <header v-scroll-reveal class="border-rancho-ink-200 mb-10 border-b pb-8 sm:mb-12">
                <h1
                    class="text-3xl font-black leading-tight tracking-tight text-rancho-forest sm:text-4xl md:text-5xl"
                >
                    {{ pageData.title }}
                </h1>

                <p
                    v-if="pageData.seo?.description"
                    class="text-rancho-ink-600 mt-4 max-w-3xl text-base leading-relaxed sm:text-lg"
                >
                    {{ pageData.seo.description }}
                </p>
            </header>

            <!-- CONTENT -->
            <section
                v-if="pageData.content"
                v-scroll-reveal
                class="prose-p:text-rancho-ink-700 prose-blockquote:bg-rancho-ink-50 prose-blockquote:text-rancho-ink-700 prose-code:bg-rancho-ink-100 prose-li:text-rancho-ink-700 prose prose-stone max-w-none prose-headings:font-black prose-headings:text-rancho-forest prose-p:leading-relaxed prose-a:text-rancho-pine prose-a:no-underline hover:prose-a:underline prose-blockquote:border-l-4 prose-blockquote:border-rancho-buttercup prose-blockquote:py-1 prose-blockquote:pl-4 prose-blockquote:not-italic prose-strong:text-rancho-forest prose-code:rounded prose-code:px-1.5 prose-code:py-0.5 prose-code:text-rancho-forest prose-code:before:content-none prose-code:after:content-none"
                aria-label="Содержимое страницы"
                v-html="pageData.content"
            />

            <!-- FALLBACK IF NO CONTENT -->
            <section
                v-else
                v-scroll-reveal
                class="border-rancho-ink-300 bg-rancho-ink-50/50 rounded-2xl border border-dashed p-8 text-center"
            >
                <p class="text-rancho-ink-600 text-sm italic">Информация скоро появится.</p>
            </section>

            <!-- COMMENTS -->
            <div v-if="pageData.is_commentable" v-scroll-reveal>
                <CommentsSection :comments="comments" :only-show="true" />
            </div>
        </div>
    </AppContainer>
</template>
