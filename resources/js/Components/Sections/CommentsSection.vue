<script setup lang="ts">
    import { computed, ref } from 'vue';

    import type { Comment } from '@/types';

    const props = defineProps<{
        comments: Comment[];
    }>();

    const emit = defineEmits<{
        (e: 'submit', content: string): void;
    }>();

    const content = ref('');
    const textareaId = `comment-form-${Math.random().toString(36).slice(2)}`;

    const isEmpty = computed(() => props.comments.length === 0);

    const canSubmit = computed(() => content.value.trim().length > 0);

    const submit = () => {
        if (!canSubmit.value) return;

        emit('submit', content.value.trim());
        content.value = '';
    };
</script>

<template>
    <section class="mt-24 border-t border-slate-100 pt-16" aria-labelledby="comments-title">
        <!-- HEADER -->
        <header class="mb-10 flex items-center justify-between">
            <h2 id="comments-title" class="text-2xl font-black text-rancho-forest">Отзывы</h2>

            <span
                class="rounded-lg bg-rancho-paper px-3 py-1 font-bold text-rancho-olive"
                role="status"
                aria-live="polite"
            >
                {{ comments.length }}
            </span>
        </header>

        <!-- FORM -->
        <form
            @submit.prevent="submit"
            class="mb-10 space-y-3"
            aria-label="Форма добавления комментария"
        >
            <label :for="textareaId" class="sr-only"> Ваш комментарий </label>

            <textarea
                :id="textareaId"
                v-model="content"
                rows="4"
                class="w-full rounded-2xl border border-slate-200 bg-white p-4 text-sm focus:border-rancho-pine focus:outline-none focus:ring-2 focus:ring-rancho-pine/20"
                placeholder="Оставьте ваш отзыв..."
                aria-describedby="comment-help"
                required
            />

            <p id="comment-help" class="text-xs text-slate-500">
                Нажмите Enter в форме или кнопку отправки для публикации
            </p>

            <button
                type="submit"
                class="rounded-xl bg-rancho-pine px-6 py-3 font-bold text-white transition hover:bg-rancho-forest focus:outline-none focus-visible:ring-4 focus-visible:ring-rancho-buttercup/40 disabled:cursor-not-allowed disabled:opacity-50"
                :disabled="!canSubmit"
                :aria-disabled="!canSubmit"
            >
                Отправить
            </button>
        </form>

        <!-- EMPTY STATE -->
        <div
            v-if="isEmpty"
            class="rounded-3xl bg-rancho-paper py-12 text-center"
            role="status"
            aria-live="polite"
        >
            <p class="italic text-rancho-olive">Пока нет отзывов — станьте первым 🌿</p>
        </div>

        <!-- LIST -->
        <div
            v-else
            class="grid grid-cols-1 gap-6 md:grid-cols-2"
            role="list"
            aria-label="Список комментариев"
        >
            <article
                v-for="comment in comments"
                :key="comment.id"
                class="shadow-sm rounded-2xl border border-slate-100 bg-white p-6"
                role="listitem"
                :aria-label="`Комментарий от ${comment.user_name}`"
            >
                <header class="mb-3 flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-full bg-rancho-buttercup font-bold text-white"
                        aria-hidden="true"
                    >
                        {{ comment.user_name[0] }}
                    </div>

                    <div>
                        <p class="font-bold text-rancho-forest">
                            {{ comment.user_name }}
                        </p>

                        <time class="text-xs text-rancho-olive/60" :datetime="comment.created_at">
                            {{ comment.created_at }}
                        </time>
                    </div>
                </header>

                <p class="text-sm leading-relaxed text-rancho-olive">
                    {{ comment.content }}
                </p>
            </article>
        </div>
    </section>
</template>
