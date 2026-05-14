<script setup lang="ts">
    import { computed, ref } from 'vue';

    import { Link } from '@inertiajs/vue3';

    import {
        ArrowTopRightOnSquareIcon,
        HandThumbDownIcon,
        HandThumbUpIcon,
        TrashIcon,
    } from '@heroicons/vue/24/solid';

    import AppRating from '@/Components/UI/AppRating.vue';
    import { AdminComment } from '@/types';
    import { formatDateTime, formatRelativeTime } from '@/utils/format';
    import { getInitials } from '@/utils/user';

    const props = defineProps<{
        comment: AdminComment;
        isDeleting: boolean;
    }>();

    const emit = defineEmits<{
        (e: 'update-status', id: number, status: 'approved' | 'hidden'): void;
        (e: 'delete', id: number): void;
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

    const showExactDate = ref(false);
    const toggleDate = () => {
        showExactDate.value = !showExactDate.value;
    };

    const routeNames: Record<string, string> = {
        product: 'catalog.show',
        animal: 'animals.show',
        page: 'pages.show',
    };
</script>

<template>
    <article
        :aria-labelledby="`comment-author-${comment.id}`"
        class="group relative flex flex-col gap-4 rounded-3xl border p-6 transition-all duration-300"
        :class="[
            comment.status === 'approved'
                ? 'shadow-lg border-slate-800 bg-slate-900 shadow-black/20'
                : '',
            comment.status === 'pending' ? 'shadow-inner border-orange-500/30 bg-orange-500/5' : '',
            comment.status === 'hidden'
                ? 'border-slate-800/50 bg-slate-900/40 opacity-75 grayscale-[0.5]'
                : '',
            isDeleting ? 'scale-[0.97] opacity-50' : '',
        ]"
    >
        <div
            v-if="comment.status !== 'approved'"
            role="status"
            aria-live="polite"
            class="shadow-lg absolute -right-2 -top-2 z-20 flex h-6 items-center rounded-full px-3 text-[10px] font-black uppercase tracking-tighter text-white"
            :class="[comment.status === 'pending' ? 'animate-pulse bg-orange-600' : 'bg-slate-700']"
        >
            {{ comment.status_label }}
        </div>

        <header class="flex items-start justify-between">
            <div class="flex items-center gap-3">
                <div
                    class="h-12 w-12 shrink-0 overflow-hidden rounded-2xl bg-slate-800 ring-2 transition-transform group-hover:scale-110"
                    :class="comment.status === 'pending' ? 'ring-orange-500/20' : 'ring-slate-800'"
                    aria-hidden="true"
                >
                    <AppImage v-if="comment.avatar" :src="comment.avatar" alt="Аватар" />

                    <div
                        v-else
                        class="flex h-full w-full items-center justify-center bg-gradient-to-br from-slate-700 to-slate-800 text-lg font-black uppercase text-slate-500"
                    >
                        {{ getInitials(comment.user_name) }}
                    </div>
                </div>
                <div>
                    <h3
                        :id="`comment-author-${comment.id}`"
                        class="text-sm font-black leading-tight text-white"
                    >
                        {{ comment.user_name }}
                    </h3>

                    <Transition name="fade-date" mode="out-in">
                        <time
                            :key="showExactDate.toString()"
                            :datetime="comment.created_at"
                            :title="
                                showExactDate
                                    ? 'Нажмите, чтобы увидеть время назад'
                                    : 'Нажмите, чтобы увидеть точную дату'
                            "
                            @click="toggleDate"
                            class="cursor-pointer select-none text-[10px] font-medium text-slate-500 transition-colors hover:text-slate-300"
                        >
                            {{
                                showExactDate
                                    ? formatDateTime(comment.created_at)
                                    : formatRelativeTime(comment.created_at)
                            }}
                        </time>
                    </Transition>
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
                :class="comment.status === 'hidden' ? 'text-slate-500' : 'text-slate-300'"
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

            <Link
                v-if="comment.commentable?.slug === 'main'"
                :href="route('home')"
                class="rounded-xl p-2 text-slate-500 transition-all hover:bg-slate-800 hover:text-white"
                :aria-label="`Перейти к ${currentType.label}: ${comment.commentable.name}`"
            >
                <ArrowTopRightOnSquareIcon class="h-4 w-4" aria-hidden="true" />
            </Link>

            <Link
                v-else-if="comment.commentable?.slug && routeNames[comment.commentable_type]"
                :href="route(routeNames[comment.commentable_type], comment.commentable.slug)"
                class="rounded-xl p-2 text-slate-500 transition-all hover:bg-slate-800 hover:text-white"
                :aria-label="`Перейти к ${currentType.label}: ${comment.commentable.name}`"
            >
                <ArrowTopRightOnSquareIcon class="h-4 w-4" aria-hidden="true" />
            </Link>
        </section>

        <footer class="mt-auto flex items-center gap-2 border-t border-slate-800 pt-4">
            <button
                v-if="comment.status !== 'approved'"
                @click="onUpdateStatus('approved')"
                :disabled="isDeleting"
                class="shadow-lg flex flex-1 items-center justify-center gap-2 rounded-2xl bg-orange-600 py-3 text-xs font-black uppercase tracking-widest text-white shadow-orange-900/40 transition-all hover:bg-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500 active:scale-95"
                aria-label="Одобрить комментарий и опубликовать его"
            >
                <HandThumbUpIcon class="h-4 w-4" aria-hidden="true" />
                <span>Одобрить</span>
            </button>

            <button
                v-if="comment.status !== 'hidden'"
                @click="onUpdateStatus('hidden')"
                :disabled="isDeleting"
                class="flex items-center justify-center gap-2 rounded-2xl py-3 text-xs font-black uppercase tracking-widest transition-all active:scale-95"
                :class="[
                    comment.status === 'approved'
                        ? 'flex-1 bg-slate-800 text-slate-400 hover:bg-red-500/10 hover:text-red-500'
                        : 'bg-slate-800 px-4 text-slate-500',
                ]"
                :aria-label="
                    comment.status === 'approved'
                        ? 'Снять комментарий с публикации'
                        : 'Скрыть комментарий'
                "
            >
                <HandThumbDownIcon class="h-4 w-4" aria-hidden="true" />
                <span v-if="comment.status === 'approved'">Снять</span>
            </button>

            <button
                @click="onDelete"
                :disabled="isDeleting"
                class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-800 text-slate-500 transition-all hover:bg-red-500/20 hover:text-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 active:scale-90"
                aria-label="Удалить отзыв безвозвратно"
            >
                <TrashIcon class="h-5 w-5" aria-hidden="true" />
            </button>
        </footer>
    </article>
</template>

<style scoped>
    .fade-date-enter-active,
    .fade-date-leave-active {
        transition:
            opacity 0.2s ease,
            transform 0.2s ease;
    }

    .fade-date-enter-from {
        opacity: 1;
        transform: translateY(2px);
    }

    .fade-date-leave-to {
        opacity: 0;
        transform: translateY(-2px);
    }
</style>
