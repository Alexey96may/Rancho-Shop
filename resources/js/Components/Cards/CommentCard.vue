<script setup lang="ts">
    import { ref } from 'vue';

    import BaseTextarea from '@/Components/Admin/UI/AdminBaseTextarea.vue';
    import AppRating from '@/Components/UI/AppRating.vue';
    import BaseCancelButton from '@/Components/UI/BaseCancelButton.vue';
    import BaseCreateButton from '@/Components/UI/BaseCreateButton.vue';
    import BaseDeleteButton from '@/Components/UI/BaseDeleteButton.vue';
    import BaseSelect from '@/Components/UI/BaseSelect.vue';
    import BaseSmartTime from '@/Components/UI/BaseSmartTime.vue';
    import BaseSubmitButton from '@/Components/UI/BaseSubmitButton.vue';

    interface Comment {
        id: number;
        rating: number;
        content: string;
        created_at: string;
        status: 'approved' | 'pending' | 'hidden';
        commentable?: {
            name?: string;
        } | null;
    }

    const props = defineProps({
        comment: {
            type: Object as () => Comment,
            required: true,
        },
        isDeleting: {
            type: Boolean,
            default: false,
        },
        isSaving: {
            type: Boolean,
            default: false,
        },
    });

    const emit = defineEmits<{
        (
            e: 'update',
            id: number,
            data: { rating: number; content: string },
            callback: () => void,
        ): void;
        (e: 'delete', id: number): void;
    }>();

    const isEditing = ref(false);
    const editForm = ref({
        rating: props.comment.rating,
        content: props.comment.content,
    });

    const startEdit = () => {
        editForm.value.rating = props.comment.rating;
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
        class="rounded-2xl border border-slate-800 bg-slate-950 p-5 transition-all"
        :class="[isDeleting ? 'scale-[0.99] opacity-40' : '']"
    >
        <div class="mb-3 flex flex-wrap items-start justify-between gap-4">
            <div>
                <span class="block text-xs font-bold text-slate-400">
                    К материалу:
                    <span class="text-white">
                        {{ comment.commentable?.name || 'Удалено или архивный материал' }}
                    </span>
                </span>

                <BaseSmartTime :date="comment.created_at" />
            </div>

            <div class="flex items-center gap-2">
                <span
                    class="rounded-md px-2 py-0.5 text-[9px] font-black uppercase tracking-wider"
                    :class="{
                        'border border-emerald-500/20 bg-emerald-500/10 text-emerald-400':
                            comment.status === 'approved',
                        'animate-pulse border border-orange-500/20 bg-orange-500/10 text-orange-400':
                            comment.status === 'pending',
                        'bg-slate-800 text-slate-400': comment.status === 'hidden',
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

        <div v-if="isEditing" class="mt-2 space-y-3">
            <form @submit.prevent="submitUpdate" method="post">
                <BaseSelect
                    v-model="editForm.rating"
                    :options="[
                        { vKey: 1, lKey: '⭐ 1' },
                        { vKey: 2, lKey: '⭐ 2' },
                        { vKey: 3, lKey: '⭐ 3' },
                        { vKey: 4, lKey: '⭐ 4' },
                        { vKey: 5, lKey: '⭐ 5' },
                    ]"
                    value-key="vKey"
                    label-key="lKey"
                    label="Изменить оценку"
                    variant="admin"
                    class="lg:w-64"
                />

                <BaseTextarea
                    v-model="editForm.content"
                    label="Редактировать текст"
                    placeholder="Ваш комментарий"
                    :disabled="isSaving"
                    :max-height="150"
                />

                <div class="flex gap-2 pt-1">
                    <BaseSubmitButton :processing="isSaving" label="Сохранить изменения" />

                    <BaseCancelButton @click="cancelEdit" label="Отмена" />
                </div>
            </form>
        </div>

        <div v-else>
            <div class="mb-2 flex">
                <AppRating :rating="comment.rating" />
            </div>
            <p class="text-sm italic text-slate-300">“{{ comment.content }}”</p>

            <div class="mt-4 flex items-center justify-end gap-3 border-t border-slate-900/60 pt-3">
                <BaseCreateButton
                    v-if="comment.status !== 'approved'"
                    type="button"
                    label="Изменить"
                    :disabled="isDeleting || isSaving"
                    @click="startEdit"
                />

                <BaseDeleteButton
                    :disabled="isDeleting || isSaving"
                    @confirm="emit('delete', comment.id)"
                    ><span v-if="isDeleting">Удаление...</span>
                    <span v-else>Удалить</span>
                </BaseDeleteButton>
            </div>
        </div>
    </article>
</template>
