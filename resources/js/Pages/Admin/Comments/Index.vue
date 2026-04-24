<script setup lang="ts">
    import { ref } from 'vue';

    import { Head, router } from '@inertiajs/vue3';

    import { ChatBubbleLeftRightIcon } from '@heroicons/vue/24/outline';

    import AdminCommentCard from '@/Components/Admin/Cards/AdminCommentCard.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { AdminComment, Paginated } from '@/types';

    const props = defineProps<{
        comments: Paginated<AdminComment>;
        filters: { type: string; status: string };
    }>();

    const currentType = ref(props.filters.type || 'all');
    const currentStatus = ref(props.filters.status || 'all');

    const typeTabs = [
        { id: 'all', label: 'Все категории' },
        { id: 'product', label: 'Продукты' },
        { id: 'animal', label: 'Животные' },
        { id: 'page', label: 'Страницы' },
    ];

    const statusTabs = [
        { id: 'all', label: 'Любой статус' },
        { id: 'published', label: 'Опубликовано' },
        { id: 'draft', label: 'Черновики' },
    ];

    const applyFilters = () => {
        router.get(
            route('admin.comments.index'),
            {
                type: currentType.value === 'all' ? null : currentType.value,
                status: currentStatus.value === 'all' ? null : currentStatus.value,
            },
            { preserveState: true, replace: true },
        );
    };

    const setType = (type: string) => {
        currentType.value = type;
        applyFilters();
    };

    const setStatus = (status: string) => {
        currentStatus.value = status;
        applyFilters();
    };

    const togglePublish = (comment: any) => {
        router.patch(
            route('admin.comments.update', comment.id),
            { is_published: !comment.is_published },
            { preserveScroll: true },
        );
    };
</script>

<template>
    <AdminLayout>
        <template #header>Модерация отзывов</template>

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
                        :class="tab.id === 'published' ? 'bg-green-500' : 'bg-orange-500'"
                    ></div>
                    {{ tab.label }}
                </button>
            </div>
        </div>

        <div
            v-if="comments.data.length"
            class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3"
        >
            <TransitionGroup name="stagger">
                <AdminCommentCard
                    v-for="(comment, index) in comments.data"
                    :key="comment.id"
                    :comment="comment"
                    :style="{ '--i': index }"
                    @toggle-publish="togglePublish"
                />
            </TransitionGroup>
        </div>

        <div v-else class="flex flex-col items-center justify-center py-20 text-slate-500">
            <ChatBubbleLeftRightIcon class="mb-4 h-12 w-12 opacity-20" />
            <p class="font-bold tracking-tight">Ничего не найдено по этим фильтрам</p>
        </div>
    </AdminLayout>
</template>

<style scoped>
    /* Анимация появления в шахматном порядке */
    .stagger-enter-active {
        transition: all 0.5s cubic-bezier(0.25, 1, 0.5, 1);
        /* Рассчитываем задержку для каждой карточки динамически */
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

    /* Чтобы карточки плавно меняли позиции при фильтрации */
    .stagger-move {
        transition: transform 0.4s ease;
    }
</style>
