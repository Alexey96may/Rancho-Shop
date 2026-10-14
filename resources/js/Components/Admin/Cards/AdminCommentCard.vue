<script setup lang="ts">
    import { computed } from 'vue';

    import { Link, usePage } from '@inertiajs/vue3';

    import { ArrowPathIcon } from '@heroicons/vue/24/outline';

    import AdminDeleteButton from '@/Components/Admin/UI/AdminDeleteButton.vue';
    import AdminEditButton from '@/Components/Admin/UI/AdminEditButton.vue';
    import AppRating from '@/Components/UI/AppRating.vue';
    import BaseSmartTime from '@/Components/UI/BaseSmartTime.vue';
    import BaseStatusButton from '@/Components/UI/BaseStatusButton.vue';
    import type { AdminComment, SharedData } from '@/types';
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
        Product: {
            label: 'Продукт',
            color: 'text-rancho-ember bg-rancho-ember/10 border-rancho-ember/30',
        },
        Animal: {
            label: 'Животное',
            color: 'text-rancho-mint bg-rancho-mint/10 border-rancho-mint/30',
        },
        Page: {
            label: 'Страница',
            color: 'text-rancho-sky bg-rancho-sky/10 border-rancho-sky/30',
        },
    };

    const currentType = computed(
        () =>
            typeLabels[props.comment.commentable?.type ?? ''] || {
                label: 'Сайт',
                color: 'text-rancho-ink-300 bg-rancho-ink-800 border-rancho-ink-700',
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
        class="group relative flex flex-col gap-6 rounded-3xl border p-6 transition-all duration-300"
        :class="[
            comment.is_trashed
                ? 'border-rose-500/20 bg-rose-950/5 opacity-75'
                : [
                      comment.status === 'approved'
                          ? 'border-rancho-ink-800 bg-rancho-ink-900 shadow-lg shadow-black/20'
                          : '',
                      comment.status === 'pending'
                          ? 'shadow-inner border-rancho-buttercup/40 bg-rancho-buttercup/5'
                          : '',
                      comment.status === 'hidden'
                          ? 'border-rancho-ink-800/50 bg-rancho-ink-900/40 opacity-75 grayscale-[0.5]'
                          : '',
                  ],
            isDeleting ? 'scale-[0.97] opacity-50' : '',
        ]"
    >
        <!-- СТАТУС-БЕЙДЖ -->
        <div
            role="status"
            aria-live="polite"
            class="shadow-lg absolute -right-2 -top-2 z-20 flex h-6 items-center rounded-full px-3 text-[10px] font-black uppercase tracking-tighter text-white"
            :class="[
                comment.is_trashed
                    ? 'bg-rose-700 shadow-[0_0_8px_#b91c1c]'
                    : [
                          comment.status === 'pending'
                              ? 'text-rancho-ink-950 animate-pulse bg-rancho-buttercup'
                              : '',
                          comment.status === 'hidden' ? 'bg-rancho-ink-700' : '',
                          comment.status === 'approved' ? 'bg-rancho-pine' : '',
                      ],
            ]"
        >
            {{ comment.is_trashed ? 'В корзине' : comment.status_label }}
        </div>

        <!-- HEADER -->
        <header class="flex flex-col items-start justify-between gap-3 sm:flex-row">
            <div class="flex items-center gap-3">
                <div
                    class="bg-rancho-ink-800 h-12 w-12 shrink-0 overflow-hidden rounded-2xl ring-1 ring-rancho-forest transition-transform group-hover:scale-110"
                    :class="[
                        comment.is_trashed
                            ? 'ring-rose-500/20'
                            : comment.status === 'pending'
                              ? 'ring-rancho-buttercup/20'
                              : 'ring-rancho-ink-800',
                    ]"
                    aria-hidden="true"
                >
                    <AppImage v-if="comment.avatar" :src="comment.avatar" alt="Аватар" />

                    <div
                        v-else
                        class="from-rancho-ink-700 to-rancho-ink-800 text-rancho-ink-400 flex h-full w-full items-center justify-center bg-gradient-to-br text-lg font-black uppercase"
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

                    <BaseSmartTime :date="comment.created_at" theme="dark" />
                </div>
            </div>

            <AppRating :rating="comment.rating" theme="dark" />
        </header>

        <!-- CONTENT -->
        <blockquote class="relative m-0 flex-grow">
            <span
                class="absolute -left-2 -top-2 select-none text-4xl"
                :class="
                    comment.status === 'pending'
                        ? 'text-rancho-buttercup/20'
                        : 'text-rancho-ink-800'
                "
                aria-hidden="true"
            >
                “
            </span>

            <p
                class="relative z-10 text-sm italic leading-relaxed"
                :class="
                    comment.is_trashed || comment.status === 'hidden'
                        ? 'text-rancho-ink-500 decoration-rancho-ink-800 line-through'
                        : 'text-rancho-ink-200'
                "
            >
                {{ comment.content }}
            </p>
        </blockquote>

        <!-- COMMENTABLE -->
        <section
            class="mt-auto flex flex-col gap-3 rounded-2xl border border-white/5 p-3"
            :class="comment.status === 'pending' ? 'bg-rancho-buttercup/10' : 'bg-black/20'"
            aria-label="Связанный контент"
        >
            <span class="text-rancho-ink-500 text-[9px] font-black uppercase tracking-widest">
                Относится к
            </span>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <!-- БЕЙДЖ ТИПА -->
                <span
                    :class="[
                        'shrink-0 rounded-md border px-2 py-1 text-center text-[10px] font-bold uppercase tracking-wider',
                        currentType.color,
                    ]"
                >
                    {{ currentType.label }}
                </span>

                <!-- НАЗВАНИЕ / ССЫЛКА / УДАЛЁН / САЙТ -->
                <template v-if="comment.is_site_review">
                    <span class="text-rancho-ink-300 text-center text-xs font-bold italic">
                        О сайте
                    </span>
                </template>

                <template v-else-if="comment.is_deleted_material">
                    <span class="text-rancho-ink-300 text-xs font-bold italic">
                        Материал удалён
                        <template v-if="comment.commentable?.name">
                            — {{ comment.commentable.name }}
                        </template>
                    </span>
                </template>

                <template v-else-if="comment.commentable">
                    <Link
                        v-if="comment.commentable.url"
                        :href="comment.commentable.url"
                        class="text-center text-xs font-bold text-white transition-colors hover:text-rancho-buttercup hover:underline"
                    >
                        {{ comment.commentable.name || 'Без названия' }}
                    </Link>

                    <span v-else class="text-rancho-ink-300 text-xs font-bold italic">
                        {{ comment.commentable.name || 'Без названия' }}
                    </span>
                </template>
            </div>
        </section>

        <!-- FOOTER -->
        <footer class="border-rancho-ink-800 mt-auto flex items-center gap-2 border-t pt-4">
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
