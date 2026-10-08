<script setup lang="ts">
    import { computed } from 'vue';

    import AppRating from '@/Components/UI/AppRating.vue';
    import BaseSmartTime from '@/Components/UI/BaseSmartTime.vue';
    import type { Comment } from '@/types';
    import { getAvatarColor, getInitials } from '@/utils/user';

    const authorNameInitials = computed(() => {
        return getInitials(props.comment.author_name);
    });

    const props = defineProps<{
        comment: Comment;
    }>();
</script>

<template>
    <article
        class="shadow-sm hover:shadow-md rounded-2xl border border-slate-200/60 bg-white p-6 transition-shadow"
    >
        <header class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-3">
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-full text-sm font-black text-gray-300"
                    :class="[getAvatarColor(comment.id)]"
                    aria-hidden="true"
                >
                    {{ authorNameInitials }}
                </div>
                <div>
                    <h3 class="font-bold text-slate-800">
                        {{ comment.author_name || 'Гость' }}
                    </h3>

                    <BaseSmartTime :date="comment.created_at" />
                </div>
            </div>

            <AppRating :rating="comment.rating" :max="5" />
        </header>

        <p class="mt-4 text-sm leading-relaxed text-slate-600">
            {{ comment.content }}
        </p>
    </article>
</template>
