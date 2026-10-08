<script setup lang="ts">
    import { computed } from 'vue';

    import AppRating from '@/Components/UI/AppRating.vue';
    import BaseSmartTime from '@/Components/UI/BaseSmartTime.vue';
    import type { Comment } from '@/types/Comment';
    import { getAvatarColor, getInitials } from '@/utils/user';

    const props = defineProps<{
        comment: Comment;
    }>();

    const authorNameInitials = computed(() => {
        return getInitials(props.comment.author_name);
    });
</script>

<template>
    <figure
        class="shadow-sm hover:shadow-md flex flex-col gap-6 rounded-3xl border border-rancho-paper bg-white p-6 transition-all lg:p-8"
        role="listitem"
    >
        <div class="flex items-center justify-between">
            <BaseSmartTime :date="comment.created_at" />
            <AppRating v-if="comment.rating" :rating="comment.rating" :max="5" />
        </div>

        <figcaption class="flex items-center gap-3 border-t border-rancho-paper">
            <div
                class="flex h-10 w-10 items-center justify-center rounded-full text-sm font-black text-gray-300"
                :class="[getAvatarColor(comment.id)]"
                aria-hidden="true"
            >
                {{ authorNameInitials }}
            </div>

            <div class="flex flex-col">
                <cite class="font-bold not-italic text-rancho-forest">
                    {{ comment.author_name || 'Гость' }}
                </cite>
            </div>
        </figcaption>

        <blockquote class="flex-1">
            <p class="text-base leading-relaxed text-rancho-forest lg:text-lg">
                «{{ comment.content }}»
            </p>
        </blockquote>
    </figure>
</template>
