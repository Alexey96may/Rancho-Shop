<script setup lang="ts">
    import { computed, onUnmounted, ref, watch } from 'vue';

    import { router } from '@inertiajs/vue3';

    import { debounce } from 'lodash';

    import AdminCommentCard from '@/Components/Admin/Cards/AdminCommentCard.vue';
    import AdminEmptyState from '@/Components/Admin/Shared/AdminEmptyState.vue';
    import AdminPagination from '@/Components/Admin/Shared/AdminPagination.vue';
    import AdminStatCard from '@/Components/Admin/UI/AdminStatCard.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useFlash } from '@/composables/useFlash';
    import { AdminComment, Paginated } from '@/types';
    import { sanitizeNumber } from '@/utils/format';

    interface Stats {
        avg_rating: number;
        total_count: number;
        pending_count: number;
    }

    interface Statuts {
        value: string;
        label: string;
    }

    interface Filters {
        type: string;
        status: string;
    }

    defineOptions({
        layout: AdminLayout,
    });

    const props = defineProps<{
        comments: Paginated<AdminComment>;
        filters: Filters;
        stats: Stats;
        statuses: Statuts[];
    }>();

    const currentType = ref(props.filters.type || 'all');
    const currentStatus = ref(props.filters.status || 'all');
    const { notifyWithUndo, notify } = useFlash();

    const typeTabs = [
        { id: 'all', label: 'Все категории' },
        { id: 'product', label: 'Продукты' },
        { id: 'animal', label: 'Животные' },
        { id: 'page', label: 'Страницы' },
    ];

    const statusTabs = computed(() => [
        { id: 'all', label: 'Все' },
        ...props.statuses.map((s) => ({ id: s.value, label: s.label })),
    ]);

    const debouncedApplyFilters = debounce(() => {
        router.get(
            route('admin.comments.index'),
            {
                type: currentType.value === 'all' ? null : currentType.value,
                status: currentStatus.value === 'all' ? null : currentStatus.value,
            },
            { preserveState: true, replace: true },
        );
    }, 300);

    const setType = (type: string) => {
        currentType.value = type;
    };

    const setStatus = (status: string) => {
        currentStatus.value = status;
    };

    const clearFilters = () => {
        currentStatus.value = 'all';
        currentType.value = 'all';
    };

    watch([currentType, currentStatus], () => {
        debouncedApplyFilters();
    });

    const handleUpdateStatus = (id: number, status: string) => {
        router.patch(
            route('admin.comments.update', id),
            { status },
            {
                preserveScroll: true,
            },
        );
    };

    const handleDelete = async (id: number) => {
        const isDeleted = await notifyWithUndo(`Удаление комментария #${sanitizeNumber(id)}`);

        if (isDeleted) {
            router.delete(route('admin.comments.destroy', id), {
                preserveScroll: true,
                onError() {
                    notify('Ошибка удаления!', 'error');
                },
            });
        }
    };

    onUnmounted(() => {
        debouncedApplyFilters.cancel();
    });
</script>

<template>
    <Teleport to="#admin-header-content">
        <h1 class="flex items-center gap-2 text-xl font-black text-white">
            Модерация отзывов
            <span
                v-if="stats.pending_count"
                class="rounded-full bg-orange-500/20 px-2 py-0.5 text-[10px] text-xs font-bold text-orange-500"
            >
                {{ stats.pending_count }}
                {{ String(stats.pending_count).endsWith('1') ? 'новый' : 'новых' }}
            </span>
        </h1>
    </Teleport>

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <AdminStatCard label="Рейтинг сайта" :value="`${stats.avg_rating} / 5`" />

        <AdminStatCard label="Всего отзывов" :value="stats.total_count" />

        <AdminStatCard isAccent label="Ожидают проверки" :value="stats.pending_count" />
    </div>

    <div class="mb-8 flex flex-col gap-6">
        <div class="flex flex-wrap gap-2">
            <button
                v-for="tab in typeTabs"
                :key="tab.id"
                @click="setType(tab.id)"
                class="rounded-xl px-4 py-2 text-[11px] font-black uppercase tracking-widest transition-all"
                :class="
                    currentType === tab.id
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
                    currentStatus === tab.id
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

    <div v-if="comments.data.length" class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
        <TransitionGroup name="stagger">
            <AdminCommentCard
                v-for="(comment, index) in comments.data"
                :key="comment.id"
                :comment="comment"
                :style="{ '--i': index }"
                @update-status="handleUpdateStatus"
                @delete="handleDelete"
            />
        </TransitionGroup>
    </div>

    <div v-else>
        <Transition name="fade-slide">
            <AdminEmptyState
                title="Животные не найдены"
                @action="clearFilters"
                :show-action="true"
            />
        </Transition>
    </div>

    <AdminPagination :links="comments.meta.links" />
</template>

<style scoped>
    .stagger-enter-active {
        transition: all 0.5s cubic-bezier(0.25, 1, 0.5, 1);
        transition-delay: calc(var(--i) * 0.05s);
    }

    .stagger-leave-active {
        transition: all 0.3s ease;
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
