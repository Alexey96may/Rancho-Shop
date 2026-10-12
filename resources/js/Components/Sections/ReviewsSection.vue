<script setup lang="ts">
    import { Link } from '@inertiajs/vue3';

    import ReviewCard from '@/Components/Cards/ReviewCard.vue';
    import type { Comment } from '@/types/Comment';

    defineProps<{
        comments: Comment[];
    }>();
</script>

<template>
    <AppContainer
        tag="section"
        class="bg-rancho-paper/10 py-16 sm:py-20"
        aria-labelledby="reviews-heading"
    >
        <div class="mb-9 flex flex-col items-center text-center sm:mb-16">
            <h2 id="reviews-heading" class="text-xl font-bold text-rancho-forest sm:text-4xl">
                Что говорят наши гости
            </h2>
            <p class="mt-4 max-w-xl text-sm text-rancho-olive/60 lg:text-lg">
                Мы ценим каждое мнение. Ваши отзывы помогают нам становиться лучше, а животным —
                получать больше лакомств.
            </p>
            <div
                class="mt-6 h-1 w-24 rounded-full bg-rancho-buttercup sm:h-1.5"
                aria-hidden="true"
            ></div>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3" role="list">
            <ReviewCard v-for="comment in comments" :key="comment.id" :comment="comment" />
        </div>

        <div
            class="flex flex-col items-center justify-center gap-4 border-t border-rancho-paper/50 pt-12 sm:mt-12 sm:mt-4"
        >
            <p class="text-xs font-medium text-rancho-olive/80 sm:text-sm">
                Поделитесь своими впечатлениями
            </p>

            <Link
                :href="route('reviews.index')"
                class="group relative inline-flex items-center gap-2 overflow-hidden rounded-full bg-rancho-forest px-6 py-3 font-bold text-white transition-all hover:bg-rancho-buttercup hover:text-rancho-forest focus:outline-none focus:ring-4 focus:ring-rancho-buttercup/30 active:scale-95 sm:px-8 sm:py-4"
            >
                <span class="text-xs sm:text-sm">Все отзывы</span>
                <span
                    class="text-xs transition-transform group-hover:translate-x-1 sm:text-sm"
                    aria-hidden="true"
                    >→</span
                >
            </Link>
        </div>
    </AppContainer>
</template>
