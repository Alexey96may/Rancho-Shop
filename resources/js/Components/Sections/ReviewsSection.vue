<script setup lang="ts">
    import { Link } from '@inertiajs/vue3';

    import ReviewCard from '@/Components/Cards/ReviewCard.vue';
    import type { Comment } from '@/types/Comment';

    defineProps<{
        comments: Comment[];
    }>();
</script>

<template>
    <AppContainer tag="section" class="bg-rancho-paper/10 py-24" aria-labelledby="reviews-heading">
        <div class="mb-16 flex flex-col items-center text-center">
            <h2 id="reviews-heading" class="text-3xl font-bold text-rancho-forest lg:text-4xl">
                Что говорят наши гости
            </h2>
            <p class="mt-4 max-w-xl text-rancho-olive/60">
                Мы ценим каждое мнение. Ваши отзывы помогают нам становиться лучше, а животным —
                получать больше лакомств.
            </p>
            <div class="mt-6 h-1.5 w-24 rounded-full bg-rancho-buttercup" aria-hidden="true"></div>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3" role="list">
            <ReviewCard v-for="comment in comments" :key="comment.id" :comment="comment" />
        </div>

        <div
            class="mt-16 flex flex-col items-center justify-center gap-4 border-t border-rancho-paper/50 pt-12"
        >
            <p class="text-sm font-medium text-rancho-olive/80">Поделитесь своими впечатлениями</p>

            <Link
                :href="route('reviews.index')"
                class="group relative inline-flex items-center gap-2 overflow-hidden rounded-full bg-rancho-forest px-8 py-4 font-bold text-white transition-all hover:bg-rancho-buttercup hover:text-rancho-forest focus:outline-none focus:ring-4 focus:ring-rancho-buttercup/30 active:scale-95"
            >
                <span>Все отзывы</span>
                <span class="transition-transform group-hover:translate-x-1" aria-hidden="true"
                    >→</span
                >
            </Link>
        </div>
    </AppContainer>
</template>
