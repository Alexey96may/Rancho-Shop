<script setup lang="ts">
    import { PropType, computed, ref, watch } from 'vue';

    import { router, useForm } from '@inertiajs/vue3';

    import {
        ChatBubbleLeftRightIcon,
        ShieldExclamationIcon,
        StarIcon,
    } from '@heroicons/vue/24/outline';

    import AdminCommentCard from '@/Components/Admin/Cards/AdminCommentCard.vue';
    import AdminEmptyState from '@/Components/Admin/Shared/AdminEmptyState.vue';
    import AdminPageHeader from '@/Components/Admin/Shared/AdminPageHeader.vue';
    import AdminPagination from '@/Components/Admin/Shared/AdminPagination.vue';
    import AdminLoader from '@/Components/Admin/UI/AdminLoader.vue';
    import StatCard from '@/Components/UI/StatCard.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useAdminCrud } from '@/composables/crud/useAdminCrud';
    import { useAdminFilters } from '@/composables/routing/useAdminFilters';
    import { AdminComment, Paginated } from '@/types';

    defineOptions({
        layout: AdminLayout,
    });

    interface Stats {
        avg_rating: number;
        total_count: number;
        pending_count: number;
    }

    interface Statuses {
        value: string;
        label: string;
    }

    interface Filters {
        type: string;
        status: string;
    }

    const props = defineProps({
        comments: {
            type: Object as PropType<Paginated<AdminComment>>,
            required: true,
            validator: (value: unknown): boolean => {
                const val = value as Record<string, unknown>;
                const hasData = Array.isArray(val?.data);
                const hasMeta = val?.meta && typeof val.meta === 'object';

                if (!hasData || !hasMeta) {
                    console.warn(
                        'Runtime Error: The "comments" prop is missing a valid pagination structure.',
                    );
                }
                return !!(hasData && hasMeta);
            },
        },
        filters: {
            type: Object as PropType<Filters>,
            required: true,
            default: () => ({ type: '', status: '' }),
        },
        stats: {
            type: Object as PropType<Stats>,
            required: true,
            validator: (value: unknown): boolean => {
                const val = value as Record<string, unknown>;

                const hasAvg = typeof val?.avg_rating === 'number';
                const hasTotal = typeof val?.total_count === 'number';
                const hasPending = typeof val?.pending_count === 'number';

                if (!hasAvg || !hasTotal || !hasPending) {
                    console.warn(
                        'Runtime Error: The "stats" prop is missing expected numeric fields (avg_rating, total_count, pending_count).',
                    );
                }
                return !!(hasAvg && hasTotal && hasPending);
            },
        },
        statuses: {
            type: Array as PropType<Statuses[]>,
            required: true,
            validator: (value: unknown): boolean => {
                if (!Array.isArray(value)) return false;

                return value.every((item: unknown) => {
                    const status = item as Record<string, unknown>;
                    return typeof status?.value === 'string' && typeof status?.label === 'string';
                });
            },
        },
    });

    const filterForm = useForm({
        type: props.filters.type || '',
        status: props.filters.status || '',
    });

    const typeTabs = [
        { id: '', label: 'Все категории' },
        { id: 'product', label: 'Продукты' },
        { id: 'animal', label: 'Животные' },
        { id: 'page', label: 'Страницы' },
    ];

    const statusTabs = computed(() => [
        { id: '', label: 'Все' },
        ...props.statuses.map((s) => ({ id: s.value, label: s.label })),
    ]);

    const { deleteEntity, restoreEntity, isDeleting, isRestoring } = useAdminCrud();
    const { isFiltering, submitFilters, clearFilters } = useAdminFilters();

    const setType = (type: string) => {
        filterForm.type = type;
    };

    const setStatus = (status: string) => {
        filterForm.status = status;
    };

    const updateStatusIds = ref<Set<number>>(new Set());

    const handleUpdateStatus = (id: number, status: string) => {
        if (updateStatusIds.value.has(id) || isDeleting(id)) return;

        router.patch(
            route('admin.comments.update', id),
            { status },
            {
                preserveScroll: true,
                onBefore: () => {
                    updateStatusIds.value.add(id);
                },
                onFinish: () => {
                    updateStatusIds.value.delete(id);
                },
            },
        );
    };

    const handleRestore = (id: number, name: string) => {
        restoreEntity('admin.comments', id, name);
    };

    watch(
        () => [filterForm.type, filterForm.status],
        () => {
            submitFilters(filterForm, 'admin.comments.index');
        },
    );
</script>

<template>
    <Teleport to="#admin-header-content">
        <AdminPageHeader
            title="Модерация отзывов"
            :subtitle="
                String(stats.pending_count).endsWith('1')
                    ? stats.pending_count + ' новый'
                    : stats.pending_count + ' новых'
            "
        />
    </Teleport>
    <div>
        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <StatCard
                label="Рейтинг сайта"
                :value="`${stats.avg_rating} / 5`"
                :icon="StarIcon"
                labelColor="text-green-500"
            />

            <StatCard
                label="Всего отзывов"
                :value="stats.total_count"
                :icon="ChatBubbleLeftRightIcon"
            />

            <StatCard
                label="Ожидают проверки"
                :value="stats.pending_count"
                :icon="ShieldExclamationIcon"
                :labelColor="stats.pending_count ? 'text-orange-700' : 'text-slate-500'"
            />
        </div>

        <div class="mb-8 flex flex-col gap-6">
            <div class="flex flex-wrap gap-2">
                <button
                    v-for="tab in typeTabs"
                    :key="tab.id"
                    @click="setType(tab.id)"
                    class="rounded-xl px-4 py-2 text-[11px] font-black uppercase tracking-widest transition-all"
                    :class="
                        filterForm.type === tab.id
                            ? 'shadow-lg bg-orange-600 text-white shadow-orange-900/40'
                            : 'bg-slate-800 text-slate-500 hover:bg-slate-700 hover:text-slate-300'
                    "
                >
                    {{ tab.label }}
                </button>
            </div>

            <div class="flex flex-wrap gap-2 border-t border-slate-800/50 pt-4">
                <button
                    v-for="tab in statusTabs"
                    :key="tab.id"
                    @click="setStatus(tab.id)"
                    class="flex items-center gap-2 rounded-lg border px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider transition-all"
                    :class="
                        filterForm.status === tab.id
                            ? 'border-white/20 bg-white text-slate-900'
                            : 'border-slate-800 bg-transparent text-slate-500 hover:border-slate-600'
                    "
                >
                    <div
                        v-if="tab.id !== 'all'"
                        class="h-1.5 w-1.5 rounded-full"
                        :class="{
                            'bg-orange-500': tab.id === 'pending',
                            'bg-green-500': tab.id === 'approved',
                            'bg-slate-500': tab.id === 'hidden',
                        }"
                    ></div>
                    {{ tab.label }}
                </button>
            </div>
        </div>

        <Transition name="fade-slide" mode="out-in">
            <div
                v-if="comments.data.length"
                key="comments-list"
                class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3"
            >
                <TransitionGroup name="stagger">
                    <AdminCommentCard
                        v-for="(comment, index) in comments.data"
                        :key="comment.id"
                        :comment="comment"
                        :style="{ '--i': index }"
                        :is-deleting="isDeleting(comment.id) || isRestoring(comment.id)"
                        :is-processing-status="updateStatusIds.has(comment.id)"
                        @update-status="handleUpdateStatus"
                        @restore="handleRestore"
                        @delete="
                            deleteEntity(
                                'admin.comments',
                                comment.id,
                                `Удаление отзыва #${comment.id}`,
                            )
                        "
                    />
                </TransitionGroup>
            </div>

            <AdminLoader v-else-if="isFiltering" key="loading" text="Синхронизация" />

            <AdminEmptyState
                v-else
                key="empty-state"
                title="Комменты не найдены"
                @action="clearFilters(filterForm)"
                :show-action="true"
            />
        </Transition>

        <Transition name="fade-slide" mode="out-in">
            <AdminPagination v-show="!isFiltering" :links="comments.meta.links" />
        </Transition>
    </div>
</template>

<style scoped>
    .stagger-enter-active {
        transition: all 0.5s cubic-bezier(0.25, 1, 0.5, 1);
        transition-delay: calc(var(--i) * 0.05s);
    }

    .stagger-leave-active {
        transition: all 0.3s ease;
        position: absolute;
        width: 100%;
    }

    .stagger-enter-from {
        opacity: 0;
        transform: translateY(30px) scale(0.95);
    }

    .stagger-leave-to {
        opacity: 0;
        transform: scale(0.9);
    }

    .stagger-move {
        transition: transform 0.4s ease;
    }

    .fade-slide-enter-active {
        transition: all 0.4s ease-out;
    }
    .fade-slide-enter-from {
        opacity: 0;
        transform: translateY(-10px);
    }
</style>
