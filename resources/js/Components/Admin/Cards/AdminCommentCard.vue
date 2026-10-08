<script setup lang="ts">
    import { computed } from 'vue';

    import { usePage } from '@inertiajs/vue3';

    import { ArrowPathIcon } from '@heroicons/vue/24/outline';

    import AdminDeleteButton from '@/Components/Admin/UI/AdminDeleteButton.vue';
    import AdminEditButton from '@/Components/Admin/UI/AdminEditButton.vue';
    import AppRating from '@/Components/UI/AppRating.vue';
    import BaseSmartTime from '@/Components/UI/BaseSmartTime.vue';
    import BaseStatusButton from '@/Components/UI/BaseStatusButton.vue';
    import { AdminComment, SharedData } from '@/types';
    import { getInitials } from '@/utils/user';

    const props = defineProps<{
        comment: AdminComment;
        isDeleting: boolean;
        isProcessingStatus: boolean;
    }>();

    const emit = defineEmits<{
        (e: 'update-status', id: number, status: 'approved' | 'hidden'): void;
        (e: 'delete', id: number): void;
        (e: 'restore', id: number, name: string): void;
    }>();

    const typeLabels: Record<string, { label: string; color: string }> = {
        product: {
            label: 'Продукт',
            color: 'text-orange-400 bg-orange-400/10 border-orange-400/20',
        },
        animal: { label: 'Животное', color: 'text-green-400 bg-green-400/10 border-green-400/20' },
        page: { label: 'Страница', color: 'text-blue-400 bg-blue-400/10 border-blue-400/20' },
    };

    const currentType = computed(
        () =>
            typeLabels[props.comment.commentable_type] || {
                label: 'Сайт',
                color: 'text-slate-400 bg-slate-400/10',
            },
    );

    const onUpdateStatus = (newStatus: 'approved' | 'hidden') => {
        emit('update-status', props.comment.id, newStatus);
    };

    const onDelete = () => {
        emit('delete', props.comment.id);
    };

    const onRestore = () => {
        emit('restore', props.comment.id, `отзыв от ${props.comment.user_name}`);
    };

    const page = usePage<SharedData>();
    const can = computed(() => page.props.can ?? {});
</script>

<template>
    <article
        :aria-labelledby="`comment-author-${comment.id}`"
        class="group relative flex flex-col gap-4 rounded-3xl border p-6 transition-all duration-300"
        :class="[
            comment.is_trashed
                ? 'border-red-500/10 bg-red-950/5 opacity-75'
                : [
                      comment.status === 'approved'
                          ? 'shadow-lg border-slate-800 bg-slate-900 shadow-black/20'
                          : '',
                      comment.status === 'pending'
                          ? 'shadow-inner border-orange-500/30 bg-orange-500/5'
                          : '',
                      comment.status === 'hidden'
                          ? 'border-slate-800/50 bg-slate-900/40 opacity-75 grayscale-[0.5]'
                          : '',
                  ],
            isDeleting ? 'scale-[0.97] opacity-50' : '',
        ]"
    >
        <div
            role="status"
            aria-live="polite"
            class="shadow-lg absolute -right-2 -top-2 z-20 flex h-6 items-center rounded-full px-3 text-[10px] font-black uppercase tracking-tighter text-white"
            :class="[
                comment.is_trashed
                    ? 'bg-red-700 shadow-[0_0_8px_#b91c1c]'
                    : [
                          comment.status === 'pending' ? 'animate-pulse bg-orange-600' : '',
                          comment.status === 'hidden' ? 'bg-slate-700' : '',
                          comment.status === 'approved' ? 'bg-emerald-600' : '',
                      ],
            ]"
        >
            {{ comment.is_trashed ? 'В корзине' : comment.status_label }}
        </div>

        <header class="flex items-start justify-between">
            <div class="flex items-center gap-3">
                <div
                    class="h-12 w-12 shrink-0 overflow-hidden rounded-2xl bg-slate-800 ring-2 transition-transform group-hover:scale-110"
                    :class="[
                        comment.is_trashed
                            ? 'ring-red-500/20'
                            : comment.status === 'pending'
                              ? 'ring-orange-500/20'
                              : 'ring-slate-800',
                    ]"
                    aria-hidden="true"
                >
                    <AppImage v-if="comment.avatar" :src="comment.avatar" alt="Аватар" />

                    <div
                        v-else
                        class="flex h-full w-full items-center justify-center bg-gradient-to-br from-slate-700 to-slate-800 text-lg font-black uppercase text-slate-500"
                    >
                        {{ getInitials(comment.author_name) }}
                    </div>
                </div>
                <div>
                    <h3
                        :id="`comment-author-${comment.id}`"
                        class="text-sm font-black leading-tight text-white"
                    >
                        {{ comment.user_name }}
                    </h3>

                    <BaseSmartTime :date="comment.created_at" />
                </div>
            </div>

            <AppRating :rating="comment.rating" />
        </header>

        <blockquote class="relative m-0">
            <span
                class="absolute -left-2 -top-2 select-none text-4xl"
                :class="comment.status === 'pending' ? 'text-orange-500/20' : 'text-slate-800'"
                aria-hidden="true"
                >“</span
            >
            <p
                class="relative z-10 text-sm italic leading-relaxed"
                :class="
                    comment.is_trashed || comment.status === 'hidden'
                        ? 'text-slate-500 line-through decoration-slate-800'
                        : 'text-slate-300'
                "
            >
                {{ comment.content }}
            </p>
        </blockquote>

        <section
            class="mt-2 flex items-center justify-between rounded-2xl border border-white/5 p-3"
            :class="comment.status === 'pending' ? 'bg-orange-500/10' : 'bg-black/20'"
            aria-label="Связанный контент"
        >
            <div class="flex flex-col gap-1">
                <span class="text-[9px] font-black uppercase tracking-widest text-slate-600"
                    >Относится к</span
                >
                <div class="flex items-center gap-2">
                    <span
                        :class="[
                            'rounded-md border px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider',
                            currentType.color,
                        ]"
                    >
                        {{ currentType.label }}
                    </span>
                    <span class="text-xs font-bold text-slate-400">{{
                        comment.commentable?.name || 'Сайт'
                    }}</span>
                </div>
            </div>
        </section>

        <footer class="mt-auto flex items-center gap-2 border-t border-slate-800 pt-4">
            <template v-if="!comment.is_trashed">
                <BaseStatusButton
                    v-if="comment.status !== 'approved'"
                    type="approve"
                    :current-status="comment.status"
                    :disabled="isDeleting"
                    :loading="isProcessingStatus"
                    @click="onUpdateStatus('approved')"
                />

                <BaseStatusButton
                    v-if="comment.status !== 'hidden'"
                    type="reject"
                    :current-status="comment.status"
                    :disabled="isDeleting"
                    :loading="isProcessingStatus"
                    @click="onUpdateStatus('hidden')"
                />
            </template>

            <AdminEditButton
                v-if="comment.is_trashed && can.restore"
                @click="onRestore"
                :title="'Восстановить отзыв'"
                :disabled="isDeleting"
                :icon="ArrowPathIcon"
            />

            <AdminDeleteButton
                v-if="!comment.is_trashed || can.forceDelete"
                @click="onDelete"
                :title="comment.is_trashed ? 'Удалить окончательно' : 'Удалить в архив'"
                :disabled="isDeleting"
            />
        </footer>
    </article>
</template>
