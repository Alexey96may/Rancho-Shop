<script setup lang="ts">
    import { computed } from 'vue';

    import { Head, Link } from '@inertiajs/vue3';

    import CommentsSection from '@/Components/Sections/CommentsSection.vue';
    import MainLayout from '@/Layouts/MainLayout.vue';
    import type { Comment, Page } from '@/types';

    defineOptions({ layout: MainLayout });

    const props = defineProps<{
        page: Page;
        reviews: {
            data: Comment[];
            meta: {
                current_page: number;
                last_page: number;
            };
        };
    }>();

    const pageData = computed(() => props.page);
</script>

<template>
    <Head :title="pageData.title">
        <meta name="description" :content="pageData.seo?.description ?? pageData.title" />
    </Head>

    <main class="min-h-screen bg-rancho-paper text-rancho-forest" aria-labelledby="farm-title">
        <div class="mx-auto max-w-5xl px-6 py-12">
            <!-- BREADCRUMB -->
            <nav aria-label="Хлебные крошки" class="mb-8 text-sm text-rancho-olive">
                <ol class="flex items-center gap-2">
                    <li>
                        <Link
                            :href="route('home')"
                            class="hover:text-rancho-pine focus:outline-none focus-visible:ring-2 focus-visible:ring-rancho-buttercup"
                        >
                            Главная
                        </Link>
                    </li>

                    <li aria-hidden="true">/</li>

                    <li class="font-semibold text-rancho-forest">
                        {{ pageData.title }}
                    </li>
                </ol>
            </nav>

            <!-- HERO -->
            <header class="mb-12">
                <h1 id="farm-title" class="text-4xl font-black leading-tight md:text-5xl">
                    {{ pageData.title }}
                </h1>

                <p
                    v-if="pageData.seo?.description"
                    class="mt-4 max-w-2xl text-lg text-rancho-olive"
                >
                    {{ pageData.seo.description }}
                </p>
            </header>

            <!-- MEDIA -->
            <section v-if="pageData.media?.length" class="mb-12" aria-label="Фотографии фермы">
                <div class="grid grid-cols-2 gap-4 md:grid-cols-3">
                    <figure
                        v-for="img in pageData.media"
                        :key="img.id"
                        class="overflow-hidden rounded-2xl bg-white"
                    >
                        <img
                            :src="img.url"
                            :alt="img.name || 'Ферма'"
                            class="h-full w-full object-cover transition hover:scale-105"
                            loading="lazy"
                        />
                    </figure>
                </div>
            </section>

            <!-- CONTENT -->
            <section class="prose prose-slate max-w-none" aria-label="Описание фермы">
                <div v-html="pageData.content" />
            </section>

            <!-- INFO BLOCK -->
            <section class="mt-16 grid gap-6 md:grid-cols-2" aria-label="Информация о ферме">
                <div class="rounded-2xl border border-rancho-paper bg-white p-6">
                    <h2 class="mb-4 text-xl font-bold">О ферме</h2>

                    <ul class="space-y-2 text-rancho-olive">
                        <li>🌿 Натуральное производство</li>
                        <li>🐄 Собственные животные</li>
                        <li>🚜 Локальная доставка</li>
                    </ul>
                </div>

                <div class="rounded-2xl border border-rancho-paper bg-white p-6">
                    <h2 class="mb-4 text-xl font-bold">SEO / мета</h2>

                    <p class="text-sm text-rancho-olive">
                        {{ pageData.seo?.description ?? 'Нет описания SEO' }}
                    </p>
                </div>
            </section>

            <!-- REVIEWS -->
            <section class="mt-20" aria-labelledby="reviews-title">
                <CommentsSection
                    :comments="reviews"
                    :meta="reviews.meta"
                    :commentable-id="pageData.id"
                    commentable-type="Page"
                />
            </section>

            <!-- CTA -->
            <section class="mt-20 text-center" aria-label="Призыв к действию">
                <h2 class="mb-4 text-2xl font-black">Посмотрите наших животных</h2>

                <Link
                    :href="route('animals.index')"
                    class="inline-flex rounded-2xl bg-rancho-pine px-8 py-4 font-bold text-white hover:bg-rancho-forest focus-visible:ring-4 focus-visible:ring-rancho-buttercup"
                >
                    Перейти к животным
                </Link>
            </section>
        </div>
    </main>
</template>
