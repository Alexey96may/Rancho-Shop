<script setup lang="ts">
    import { PropType, computed } from 'vue';

    import { Link } from '@inertiajs/vue3';

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

    const farmHighlights = [
        { icon: '🌿', title: 'Экологичность', desc: 'Натуральное производство без химикатов' },
        { icon: '🐄', title: 'Своё стадо', desc: 'Забота и свободный выпас животных' },
        { icon: '🚜', title: 'Свежесть', desc: 'Быстрая локальная доставка с фермы' },
    ];
</script>

<template>
    <AppContainer class="relative pb-14 lg:pb-8">
        <div class="min-h-screen bg-rancho-paper text-rancho-forest" aria-labelledby="farm-title">
            <div class="mx-auto max-w-5xl px-6 py-10 md:py-16">
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

                <!-- HERO SECTION -->
                <header class="mb-12 border-b border-rancho-forest/10 pb-8">
                    <h1
                        id="farm-title"
                        class="text-4xl font-black leading-tight tracking-tight md:text-5xl lg:text-6xl"
                    >
                        {{ pageData.title }}
                    </h1>

                    <p
                        v-if="pageData.seo?.description"
                        class="mt-4 max-w-3xl text-lg leading-relaxed text-rancho-olive md:text-xl"
                    >
                        {{ pageData.seo.description }}
                    </p>
                </header>

                <!-- MEDIA GALLERY (BENTO GRID) -->
                <section v-if="pageData.media?.length" class="mb-14" aria-label="Фотографии фермы">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">
                        <figure
                            v-for="(img, idx) in pageData.media"
                            :key="img.id"
                            class="shadow-sm hover:shadow-md group relative overflow-hidden rounded-3xl bg-white transition"
                            :class="{
                                'sm:col-span-2 sm:row-span-2 md:h-[420px]': idx === 0,
                                'h-52 md:h-full': idx > 0,
                            }"
                        >
                            <img
                                :src="img.url"
                                :alt="img.name || 'Ферма'"
                                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                                loading="lazy"
                            />
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-rancho-forest/40 via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                            />
                        </figure>
                    </div>
                </section>

                <!-- MAIN CONTENT -->
                <section
                    class="prose prose-slate max-w-none prose-headings:font-bold prose-headings:text-rancho-forest prose-p:leading-relaxed prose-p:text-rancho-forest/90 prose-a:text-rancho-pine hover:prose-a:underline"
                    aria-label="Описание фермы"
                >
                    <div v-html="pageData.content" />
                </section>

                <!-- HIGHLIGHTS / INFO BLOCK -->
                <section class="mt-16" aria-label="Преимущества фермы">
                    <h2 class="mb-6 text-2xl font-extrabold text-rancho-forest">
                        Почему именно наше ранчо
                    </h2>
                    <div class="grid gap-6 sm:grid-cols-3">
                        <div
                            v-for="item in farmHighlights"
                            :key="item.title"
                            class="hover:shadow-lg group rounded-3xl border border-rancho-forest/10 bg-white/80 p-6 backdrop-blur-sm transition hover:border-rancho-pine/30 hover:shadow-rancho-forest/5"
                        >
                            <div class="mb-3 text-3xl transition-transform group-hover:scale-110">
                                {{ item.icon }}
                            </div>
                            <h3 class="text-lg font-bold text-rancho-forest">{{ item.title }}</h3>
                            <p class="mt-1 text-sm text-rancho-olive">{{ item.desc }}</p>
                        </div>
                    </div>
                </section>

                <!-- CALL TO ACTION -->
                <section
                    class="shadow-xl mt-20 overflow-hidden rounded-3xl bg-rancho-forest p-8 text-center text-white md:p-12"
                    aria-label="Призыв к действию"
                >
                    <div class="mx-auto max-w-2xl">
                        <span class="mb-2 block text-4xl">🌾</span>
                        <h2 class="text-3xl font-black md:text-4xl">Познакомьтесь с обитателями</h2>
                        <p class="mt-3 text-rancho-paper/80">
                            Узнайте больше о наших животных, их рационе и жизни на свежем воздухе.
                        </p>
                        <div class="mt-8">
                            <Link
                                :href="route('animals.index')"
                                class="shadow-md hover:shadow-lg inline-flex items-center gap-2 rounded-2xl bg-rancho-buttercup px-8 py-4 font-bold text-rancho-forest transition hover:bg-white focus:outline-none focus-visible:ring-4 focus-visible:ring-white"
                            >
                                <span>Перейти к жителям фермы</span>
                                <span aria-hidden="true">→</span>
                            </Link>
                        </div>
                    </div>
                </section>

                <!-- REVIEWS / COMMENTS -->
                <div class="mx-auto mt-12 max-w-4xl px-6 pb-16">
                    <CommentsSection :comments="comments" :only-show="true" />
                </div>
            </div>
        </div>
    </AppContainer>
</template>
