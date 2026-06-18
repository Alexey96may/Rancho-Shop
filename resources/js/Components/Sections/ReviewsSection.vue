<script setup lang="ts">
    import ReviewCard from '@/Components/Cards/ReviewCard.vue';
    import type { Comment } from '@/types/Comment';

    defineProps<{
        comments: Comment[];
    }>();
</script>

<template>
    <AppContainer tag="section" class="bg-rancho-paper/10 py-24" aria-labelledby="reviews-heading">
        <div class="mb-16 flex flex-col items-center text-center">
            <h2 id="reviews-heading" class="text-rancho-forest text-3xl font-bold lg:text-4xl">
                Что говорят наши гости
            </h2>
            <p class="text-rancho-olive/60 mt-4 max-w-xl">
                Мы ценим каждое мнение. Ваши отзывы помогают нам становиться лучше, а животным —
                получать больше лакомств.
            </p>
            <div class="bg-rancho-buttercup mt-6 h-1.5 w-24 rounded-full" aria-hidden="true"></div>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3" role="list">
            <ReviewCard v-for="comment in comments" :key="comment.id" :comment="comment" />
        </div>

        <div
            class="border-rancho-paper/50 mt-16 flex flex-col items-center justify-center gap-4 border-t pt-12"
        >
            <p class="text-rancho-olive/80 text-sm font-medium">Поделитесь своими впечатлениями</p>
            <button
                type="button"
                class="bg-rancho-forest hover:bg-rancho-buttercup hover:text-rancho-forest focus:ring-rancho-buttercup/30 group relative inline-flex items-center gap-2 overflow-hidden rounded-full px-8 py-4 font-bold text-white transition-all focus:ring-4 active:scale-95"
            >
                <span>Написать отзыв</span>
                <span class="transition-transform group-hover:translate-x-1" aria-hidden="true"
                    >→</span
                >
            </button>
        </div>
    </AppContainer>
</template>
