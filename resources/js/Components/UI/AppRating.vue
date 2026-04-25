<script setup lang="ts">
    import { computed } from 'vue';

    import { Star, StarHalf } from 'lucide-vue-next';

    import { getStarStats } from '@/utils/math';

    interface Props {
        rating: number | string | undefined | null;
        max?: number;
    }

    const { rating = 0, max = 5 } = defineProps<Props>();

    const stars = computed(() => getStarStats(Number(rating) || 0, max));
</script>

<template>
    <div
        class="rating-display flex items-center"
        role="img"
        :aria-label="`Rating: ${rating || 0} out of ${max}`"
    >
        <Star
            v-for="n in stars.full"
            :key="'full-' + n"
            :stroke-width="1"
            class="rating-star h-4 w-4 fill-orange-500/60 text-orange-500 hover:scale-110"
            aria-hidden="true"
        />

        <StarHalf
            v-if="stars.half"
            :stroke-width="1"
            class="rating-star h-4 w-4 fill-orange-500/60 text-orange-500 hover:scale-110"
            aria-hidden="true"
        />

        <Star
            v-for="n in stars.empty"
            :key="'empty-' + n"
            :stroke-width="1"
            class="rating-star h-4 w-4 text-orange-500 hover:scale-110"
            aria-hidden="true"
        />
    </div>
</template>

<style scoped>
    .rating-display {
        display: flex;
        gap: 0.2rem;
    }

    .rating-star {
        transition: transform 0.25s;
    }
</style>
