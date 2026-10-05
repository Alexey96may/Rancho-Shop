<script setup lang="ts">
    import { PropType, watch } from 'vue';

    import { router, useForm } from '@inertiajs/vue3';

    import FeatureCard from '@/Components/Admin/Cards/AdminFeatureCard.vue';
    import AdminEmptyState from '@/Components/Admin/Shared/AdminEmptyState.vue';
    import AdminPageHeader from '@/Components/Admin/Shared/AdminPageHeader.vue';
    import AdminLoader from '@/Components/Admin/UI/AdminLoader.vue';
    import AdminSearchInput from '@/Components/Admin/UI/AdminSearchInput.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useAdminFilters } from '@/composables/routing/useAdminFilters';
    import { AdminLandingBlock, ResourceCollection } from '@/types';

    defineOptions({ layout: AdminLayout });

    const props = defineProps({
        blocks: {
            type: Object as PropType<ResourceCollection<AdminLandingBlock>>,
            required: true,
            validator: (value: unknown): boolean => {
                const val = value as Record<string, unknown>;
                const hasData = Array.isArray(val?.data);

                if (!hasData) {
                    console.warn(
                        'Runtime Error: The "blocks" prop must be a ResourceCollection containing a "data" array.',
                    );
                }
                return hasData;
            },
        },
        filters: {
            type: Object as PropType<{ search: string }>,
            required: true,
            default: () => ({ search: '' }),
        },
    });

    const filterForm = useForm({
        search: props.filters.search || '',
    });

    const { isFiltering, submitFilters, clearFilters } = useAdminFilters();

    const toggleVisibility = (id: number) => {
        router.patch(
            route('admin.features.toggle', id),
            {},
            {
                preserveScroll: true,
            },
        );
    };

    watch(
        () => [filterForm.search],
        () => {
            submitFilters(filterForm, 'admin.features.index');
        },
    );
</script>

<template>
    <Teleport to="#admin-header-content">
        <AdminPageHeader title="Блоки страниц" subtitle="Управление блоками на главной" />
    </Teleport>

    <div class="animate-in fade-in slide-in-from-bottom-4 space-y-8 duration-700">
        <AdminSearchInput v-model="filterForm.search" placeholder="Поиск по названию или тегу..." />

        <Transition name="fade-slide">
            <div v-if="blocks.data.length" key="blocks">
                <TransitionGroup
                    name="list"
                    tag="div"
                    class="grid grid-cols-1 gap-6 lg:grid-cols-2"
                >
                    <FeatureCard
                        v-for="block in blocks.data"
                        :key="block.id"
                        :block="block"
                        @toggle="toggleVisibility"
                    />
                </TransitionGroup>
            </div>

            <AdminLoader v-else-if="isFiltering" key="loading" text="Синхронизация" />

            <AdminEmptyState
                v-else
                key="empty"
                title="Блоки не найдены"
                @action="clearFilters(filterForm)"
                :show-action="true"
            />
        </Transition>
    </div>
</template>

<style scoped>
    .list-move,
    .list-enter-active,
    .list-leave-active {
        transition: all 0.5s cubic-bezier(0.55, 0, 0.1, 1);
    }

    .list-enter-from,
    .list-leave-to {
        opacity: 0;
        transform: scale(0.9) translateY(30px);
    }

    .list-leave-active {
        position: absolute;
        width: 100%;
    }

    .animate-in {
        animation: fadeInScale 0.7s ease;
    }

    @keyframes fadeInScale {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .fade-slide-enter-active {
        transition: all 0.4s ease-out;
    }

    .fade-slide-enter-from {
        opacity: 0;
        transform: translateY(-10px);
    }
</style>
