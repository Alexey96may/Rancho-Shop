<script setup lang="ts">
    import { computed } from 'vue';

    import { Heart, MessageSquare, ShoppingBag, Star } from 'lucide-vue-next';

    import type { Comment } from '@/types/Comment';

    const props = defineProps<{
        comment: Comment;
    }>();

    // Определяем иконку в зависимости от того, к чему оставлен отзыв
    const contextIcon = computed(() => {
        switch (props.comment.commentable_type) {
            case 'product':
                return ShoppingBag;
            case 'cow':
                return Heart;
            default:
                return MessageSquare;
        }
    });

    const contextLabel = computed(() => {
        if (props.comment.commentable_type === 'product') return 'О товаре';
        if (props.comment.commentable_type === 'cow') return 'О животном';
        return 'О сайте';
    });
</script>

<template>
    <figure
        class="bg-white shadow-sm hover:shadow-md flex flex-col rounded-3xl border border-rancho-paper p-6 transition-all lg:p-8"
        role="listitem"
    >
        <div class="mb-4 flex items-center justify-between">
            <div
                class="inline-flex items-center gap-1.5 rounded-full bg-rancho-paper/30 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-rancho-olive/60"
                :title="contextLabel"
            >
                <component :is="contextIcon" :size="12" aria-hidden="true" />
                <span class="sr-only">Тип отзыва:</span> {{ contextLabel }}
            </div>

            <time
                :datetime="comment.created_at"
                class="text-xs uppercase tracking-tighter text-rancho-olive/40"
            >
                {{ comment.created_at }}
            </time>
        </div>

        <div
            v-if="comment.rating"
            class="mb-4 flex items-center gap-0.5 text-rancho-buttercup"
            role="img"
            :aria-label="`Рейтинг: ${comment.rating} из 5 звезд`"
        >
            <Star
                v-for="i in 5"
                :key="i"
                :size="16"
                :fill="i <= comment.rating ? 'currentColor' : 'none'"
                :class="i <= comment.rating ? 'text-rancho-buttercup' : 'text-rancho-olive/20'"
                aria-hidden="true"
            />
        </div>

        <blockquote class="flex-1">
            <p class="text-base leading-relaxed text-rancho-forest lg:text-lg">
                «{{ comment.content }}»
            </p>
        </blockquote>

        <figcaption class="mt-6 flex items-center gap-3 border-t border-rancho-paper pt-6">
            <div
                class="text-white flex h-10 w-10 items-center justify-center rounded-full bg-rancho-forest text-sm font-bold uppercase"
                aria-hidden="true"
            >
                {{ comment.user_name?.charAt(0) }}
            </div>
            <cite class="font-bold not-italic text-rancho-forest">
                {{ comment.user_name }}
            </cite>
        </figcaption>
    </figure>
</template>
