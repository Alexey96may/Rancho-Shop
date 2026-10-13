<script setup lang="ts">
    import { ref } from 'vue';

    import { Link } from '@inertiajs/vue3';

    import BaseTextarea from '@/Components/Admin/UI/AdminBaseTextarea.vue';
    import AppRating from '@/Components/UI/AppRating.vue';
    import BaseCancelButton from '@/Components/UI/BaseCancelButton.vue';
    import BaseCreateButton from '@/Components/UI/BaseCreateButton.vue';
    import BaseDeleteButton from '@/Components/UI/BaseDeleteButton.vue';
    import BaseSmartTime from '@/Components/UI/BaseSmartTime.vue';
    import BaseSubmitButton from '@/Components/UI/BaseSubmitButton.vue';
    import type { Comment } from '@/types';

    const props = withDefaults(
        defineProps<{
            comment: Comment;
            isDeleting?: boolean;
            isSaving?: boolean;
        }>(),
        {
            isDeleting: false,
            isSaving: false,
        },
    );

    const emit = defineEmits<{
        (
            e: 'update',
            id: number,
            data: { rating: number | null; content: string },
            callback: () => void,
        ): void;
        (e: 'delete', id: number): void;
    }>();

    const isEditing = ref(false);
    const editForm = ref({
        rating: props.comment.rating ?? 5,
        content: props.comment.content,
    });

    const startEdit = () => {
        editForm.value.rating = props.comment.rating ?? 5;
        editForm.value.content = props.comment.content;
        isEditing.value = true;
    };

    const cancelEdit = () => {
        isEditing.value = false;
    };

    const submitUpdate = () => {
        emit('update', props.comment.id, { ...editForm.value }, () => {
            isEditing.value = false;
        });
    };
</script>

<template>
    <article
        class="border-rancho-ink-800 bg-rancho-ink-950 rounded-2xl border p-5 transition-all"
        :class="[isDeleting ? 'scale-[0.99] opacity-40' : '']"
    >
        <div class="mb-3 flex flex-wrap items-start justify-between gap-4">
            <div>
                <!-- Случай А: отзыв о сайте -->
                <span
                    v-if="comment.is_site_review"
                    class="text-rancho-ink-400 block text-xs font-bold"
                >
                    О сайте
                </span>

                <!-- Случай Б: материал удалён -->
                <span
                    v-else-if="comment.is_deleted_material"
                    class="text-rancho-ink-400 block text-xs font-bold"
                >
                    К материалу:
                    <span class="text-rancho-ink-300 font-bold italic">
                        Материал удалён
                        <template v-if="comment.commentable?.name">
                            — {{ comment.commentable.name }}
                        </template>
                    </span>
                </span>

                <!-- Случай В: живой материал -->
                <span
                    v-else-if="comment.commentable"
                    class="text-rancho-ink-400 block text-xs font-bold"
                >
                    К материалу:

                    <Link
                        v-if="comment.commentable.url"
                        :href="comment.commentable.url"
                        class="font-bold text-white transition-colors hover:text-rancho-buttercup hover:underline"
                    >
                        {{ comment.commentable.name || 'Без названия' }}
                    </Link>

                    <span v-else class="text-rancho-ink-300 font-bold italic">
                        {{ comment.commentable.name || 'Без названия' }}
                    </span>
                </span>

                <BaseSmartTime :date="comment.created_at" theme="dark" />
            </div>

            <div class="flex items-center gap-2">
                <span
                    class="rounded-md px-2 py-0.5 text-[9px] font-black uppercase tracking-wider"
                    :class="{
                        'border border-rancho-pine/30 bg-rancho-pine/15 text-rancho-sky':
                            comment.status === 'approved',
                        'animate-pulse border border-rancho-buttercup/30 bg-rancho-buttercup/15 text-rancho-buttercup':
                            comment.status === 'pending',
                        'border-rancho-ink-700 bg-rancho-ink-800 text-rancho-ink-300 border':
                            comment.status === 'hidden',
                    }"
                >
                    {{
                        comment.status === 'approved'
                            ? 'Одобрен'
                            : comment.status === 'pending'
                              ? 'На модерации'
                              : 'Скрыт'
                    }}
                </span>
            </div>
        </div>

        <!-- EDIT / VIEW с анимацией -->
        <Transition name="comment-mode" mode="out-in">
            <!-- EDIT MODE -->
            <div v-if="isEditing" key="edit" class="mt-2 space-y-3">
                <form @submit.prevent="submitUpdate" method="post" class="space-y-6">
                    <AppRating v-model:rating="editForm.rating" :readonly="false" theme="dark" />

                    <BaseTextarea
                        v-model="editForm.content"
                        label="Редактировать текст"
                        placeholder="Ваш комментарий"
                        :disabled="isSaving"
                        :max-height="150"
                    />

                    <div class="flex justify-between gap-2 pt-1">
                        <BaseCancelButton @click="cancelEdit" label="Отмена" theme="dark" />
                        <BaseSubmitButton :processing="isSaving" label="Сохранить" />
                    </div>
                </form>
            </div>

            <!-- VIEW MODE -->
            <div v-else key="view">
                <div v-if="comment.rating" class="mb-2 flex">
                    <AppRating :rating="comment.rating" theme="dark" />
                </div>

                <p class="text-rancho-ink-200 text-sm italic">“{{ comment.content }}”</p>

                <div
                    class="border-rancho-ink-800 mt-4 flex items-center justify-end gap-3 border-t pt-3"
                >
                    <BaseCreateButton
                        v-if="comment.status !== 'approved'"
                        type="button"
                        variant="outline"
                        label="Изменить"
                        :disabled="isDeleting || isSaving"
                        theme="dark"
                        @click="startEdit"
                    />

                    <BaseDeleteButton
                        :disabled="isDeleting || isSaving"
                        @confirm="emit('delete', comment.id)"
                    >
                        <span v-if="isDeleting">Удаление...</span>
                        <span v-else>Удалить</span>
                    </BaseDeleteButton>
                </div>
            </div>
        </Transition>
    </article>
</template>

<style scoped>
    /* Переход между edit и view режимами */
    .comment-mode-enter-active,
    .comment-mode-leave-active {
        transition:
            opacity 0.25s ease,
            transform 0.25s ease;
    }

    .comment-mode-enter-from {
        opacity: 0;
        transform: translateY(8px);
    }

    .comment-mode-leave-to {
        opacity: 0;
        transform: translateY(-8px);
    }

    @media (prefers-reduced-motion: reduce) {
        .comment-mode-enter-active,
        .comment-mode-leave-active {
            transition: none;
        }
    }
</style>
