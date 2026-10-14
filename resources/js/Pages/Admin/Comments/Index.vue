<script setup lang="ts">
    import { type PropType, computed, ref, watch } from 'vue';

    import { router, useForm } from '@inertiajs/vue3';

    import {
        ChatBubbleLeftRightIcon,
        ShieldExclamationIcon,
        StarIcon,
    } from '@heroicons/vue/24/outline';

    import AdminCommentCard from '@/Components/Admin/Cards/AdminCommentCard.vue';
    import AdminEmptyState from '@/Components/Admin/Shared/AdminEmptyState.vue';
    import AdminPageHeader from '@/Components/Admin/Shared/AdminPageHeader.vue';
    import AdminLoader from '@/Components/Admin/UI/AdminLoader.vue';
    import AdminPagination from '@/Components/Shared/MainPagination.vue';
    import StatCard from '@/Components/UI/StatCard.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useAdminCrud } from '@/composables/crud/useAdminCrud';
    import { useAdminFilters } from '@/composables/routing/useAdminFilters';
    import type { AdminComment, Paginated } from '@/types';

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
        <!-- STATS -->
        <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-3 sm:gap-4">
            <StatCard
                label="Рейтинг сайта"
                :value="`${stats.avg_rating} / 5`"
                :icon="StarIcon"
                label-color="text-rancho-mint"
            />

            <StatCard
                label="Всего отзывов"
                :value="stats.total_count"
                :icon="ChatBubbleLeftRightIcon"
                label-color="text-rancho-sky"
            />

            <StatCard
                label="Ожидают проверки"
                :value="stats.pending_count"
                :icon="ShieldExclamationIcon"
                :label-color="stats.pending_count ? 'text-rancho-buttercup' : 'text-rancho-ink-500'"
            />
        </div>

        <!-- FILTERS -->
        <div class="mb-6 flex flex-col gap-4 sm:mb-8 sm:gap-6">
            <!-- TYPE TABS -->
            <div class="flex flex-wrap gap-2">
                <button
                    v-for="tab in typeTabs"
                    :key="tab.id"
                    type="button"
                    @click="setType(tab.id)"
                    class="focus-visible:ring-rancho-mint/40 rounded-xl px-4 py-2 text-[10px] font-black uppercase tracking-widest transition-all focus:outline-none focus-visible:ring-2"
                    :class="
                        filterForm.type === tab.id
                            ? 'bg-rancho-mint text-rancho-ink-950 shadow-lg shadow-rancho-mint/20'
                            : 'bg-rancho-ink-800 text-rancho-ink-400 hover:bg-rancho-ink-700 hover:text-rancho-ink-200'
                    "
                >
                    {{ tab.label }}
                </button>
            </div>

            <!-- STATUS TABS -->
            <div class="border-rancho-ink-800 flex flex-wrap gap-2 border-t pt-4">
                <button
                    v-for="tab in statusTabs"
                    :key="tab.id"
                    type="button"
                    @click="setStatus(tab.id)"
                    class="focus-visible:ring-rancho-mint/40 flex items-center gap-2 rounded-lg border px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider transition-all focus:outline-none focus-visible:ring-2"
                    :class="
                        filterForm.status === tab.id
                            ? 'border-rancho-mint/50 bg-rancho-mint text-rancho-ink-950'
                            : 'border-rancho-ink-800 text-rancho-ink-400 hover:border-rancho-ink-600 hover:text-rancho-ink-200 bg-transparent'
                    "
                >
                    <div
                        v-if="tab.id !== ''"
                        class="h-1.5 w-1.5 rounded-full"
                        :class="{
                            'bg-rancho-buttercup': tab.id === 'pending',
                            'bg-rancho-mint': tab.id === 'approved',
                            'bg-rancho-ink-500': tab.id === 'hidden',
                        }"
                    ></div>
                    {{ tab.label }}
                </button>
            </div>
        </div>

        <!-- LIST -->
        <Transition name="fade-slide" mode="out-in">
            <div
                v-if="comments.data.length"
                key="comments-list"
                class="grid grid-cols-1 gap-4 sm:gap-6 md:grid-cols-2 xl:grid-cols-3"
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

        <!-- PAGINATION -->
        <Transition name="fade-slide" mode="out-in">
            <AdminPagination v-show="!isFiltering" :links="comments.meta.links" theme="dark" />
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

    @media (prefers-reduced-motion: reduce) {
        .stagger-enter-active,
        .stagger-leave-active,
        .stagger-move,
        .fade-slide-enter-active {
            transition: none;
        }
    }
</style>
