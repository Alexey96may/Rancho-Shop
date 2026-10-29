<script setup lang="ts">
    import { type PropType, computed, watch } from 'vue';

    import { useForm } from '@inertiajs/vue3';

    import {
        ChatBubbleLeftRightIcon,
        CpuChipIcon,
        TrashIcon,
        UserIcon,
    } from '@heroicons/vue/24/outline';

    import AdminEmptyState from '@/Components/Admin/Shared/AdminEmptyState.vue';
    import AdminPageHeader from '@/Components/Admin/Shared/AdminPageHeader.vue';
    import AdminPagination from '@/Components/Shared/MainPagination.vue';
    import StatCard from '@/Components/UI/StatCard.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useAdminCrud } from '@/composables/crud/useAdminCrud';
    import { useAdminFilters } from '@/composables/routing/useAdminFilters';
    import type { AiMessage, Paginated } from '@/types';

    defineOptions({ layout: AdminLayout });

    interface Stats {
        total: number;
        user_messages: number;
        assistant_messages: number;
        sessions: number;
    }

    interface Filters {
        role: string;
        session: string;
    }

    interface Statuses {
        value: string;
        label: string;
    }

    const props = defineProps({
        messages: {
            type: Object as PropType<Paginated<AiMessage>>,
            required: true,
        },
        filters: {
            type: Object as PropType<Filters>,
            required: true,
            default: () => ({ role: '', session: '' }),
        },
        stats: {
            type: Object as PropType<Stats>,
            required: true,
        },
        statuses: {
            type: Array as PropType<Statuses[]>,
            required: true,
        },
    });

    const filterForm = useForm({
        role: props.filters.role || '',
        session: props.filters.session || '',
    });

    const statusTabs = computed(() => [
        { id: '', label: 'Все' },
        ...props.statuses.map((s) => ({ id: s.value, label: s.label })),
    ]);

    const { deleteEntity, isDeleting } = useAdminCrud();
    const { isFiltering, submitFilters, clearFilters } = useAdminFilters();

    const setRole = (role: string) => {
        filterForm.role = role;
    };

    watch(
        () => [filterForm.role, filterForm.session],
        () => {
            submitFilters(filterForm, 'admin.ai-messages.index');
        },
    );

    const roleLabel = (role: string) =>
        role === 'user' ? 'Пользователь' : role === 'assistant' ? 'Ассистент' : 'Система';

    const roleColor = (role: string) =>
        role === 'user'
            ? 'text-rancho-sky'
            : role === 'assistant'
              ? 'text-rancho-mint'
              : 'text-rancho-ink-500';
</script>

<template>
    <div>
        <Teleport to="#admin-header-content">
            <AdminPageHeader
                title="Диалоги с ИИ"
                :subtitle="`${stats.sessions} сессий · ${stats.total} сообщений`"
            />
        </Teleport>
        <!-- STATS -->
        <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-3 sm:gap-4">
            <StatCard
                label="Всего сообщений"
                :value="stats.total"
                :icon="ChatBubbleLeftRightIcon"
                label-color="text-rancho-mint"
            />
            <StatCard
                label="От пользователей"
                :value="stats.user_messages"
                :icon="UserIcon"
                label-color="text-rancho-sky"
            />
            <StatCard
                label="От ассистента"
                :value="stats.assistant_messages"
                :icon="CpuChipIcon"
                label-color="text-rancho-buttercup"
            />
        </div>

        <!-- FILTERS -->
        <div class="mb-6 flex flex-col gap-4 sm:mb-8 sm:gap-6">
            <div class="flex flex-wrap gap-2">
                <button
                    v-for="tab in statusTabs"
                    :key="tab.id"
                    type="button"
                    @click="setRole(tab.id)"
                    class="flex items-center gap-2 rounded-lg border px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-rancho-mint/40"
                    :class="
                        filterForm.role === tab.id
                            ? 'border-rancho-mint/50 bg-rancho-mint text-rancho-ink-950'
                            : 'border-rancho-ink-800 bg-transparent text-rancho-ink-400 hover:border-rancho-ink-600 hover:text-rancho-ink-200'
                    "
                >
                    <div
                        v-if="tab.id !== ''"
                        class="h-1.5 w-1.5 rounded-full"
                        :class="{
                            'bg-rancho-sky': tab.id === 'user',
                            'bg-rancho-mint': tab.id === 'assistant',
                        }"
                    ></div>
                    {{ tab.label }}
                </button>
            </div>
        </div>

        <!-- LIST -->
        <Transition name="fade-slide" mode="out-in">
            <div v-if="messages.data.length" key="list" class="space-y-3">
                <div
                    v-for="(msg, index) in messages.data"
                    :key="msg.id"
                    class="group rounded-2xl border border-rancho-ink-800 bg-rancho-ink-900/50 p-4 sm:p-5"
                    :style="{ '--i': index }"
                >
                    <div
                        class="mb-2 flex flex-wrap items-center gap-3 text-[10px] uppercase tracking-widest"
                    >
                        <span class="font-black" :class="roleColor(msg.role)">
                            {{ roleLabel(msg.role) }}
                        </span>
                        <span class="text-rancho-ink-500"> #{{ msg.id }} </span>
                        <span class="text-rancho-ink-500">
                            сессия: {{ msg.session_id?.slice(0, 8) ?? '—' }}
                        </span>
                        <span v-if="msg.user_id" class="text-rancho-ink-500">
                            user: {{ msg.user_id }}
                        </span>
                        <span class="ml-auto text-rancho-ink-500">
                            {{ msg.created_at }}
                        </span>
                    </div>

                    <p class="whitespace-pre-line text-sm leading-relaxed text-rancho-ink-200">
                        {{ msg.content }}
                    </p>

                    <div
                        v-if="msg.meta?.usage || msg.meta?.model"
                        class="mt-3 flex gap-4 text-[10px] text-rancho-ink-500"
                    >
                        <span v-if="msg.meta?.model">model: {{ msg.meta.model }}</span>
                        <span v-if="msg.meta?.usage?.total_tokens">
                            tokens: {{ msg.meta.usage.total_tokens }}
                        </span>
                    </div>

                    <button
                        type="button"
                        :disabled="isDeleting(msg.id)"
                        @click="
                            deleteEntity(
                                'admin.ai-messages',
                                msg.id,
                                `Удаление сообщения #${msg.id}`,
                            )
                        "
                        class="mt-3 inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-widest text-rancho-ink-500 transition-colors hover:text-rancho-buttercup disabled:opacity-50"
                    >
                        <TrashIcon class="h-3.5 w-3.5" />
                        Удалить
                    </button>
                </div>
            </div>

            <AdminEmptyState
                v-else
                key="empty"
                title="Сообщений пока нет"
                @action="clearFilters(filterForm)"
                :show-action="true"
            />
        </Transition>

        <!-- PAGINATION -->
        <AdminPagination v-show="!isFiltering" :links="messages.meta.links" theme="dark" />
    </div>
</template>

<style scoped>
    .fade-slide-enter-active {
        transition: all 0.4s ease-out;
    }
    .fade-slide-enter-from {
        opacity: 0;
        transform: translateY(-10px);
    }

    @media (prefers-reduced-motion: reduce) {
        .fade-slide-enter-active {
            transition: none;
        }
    }
</style>
