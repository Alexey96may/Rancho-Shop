<script setup lang="ts">
    import { router } from '@inertiajs/vue3';

    import FeatureCard from '@/Components/Admin/Cards/AdminFeatureCard.vue';
    import AdminEmptyState from '@/Components/Admin/Shared/AdminEmptyState.vue';
    import AdminSearchInput from '@/Components/Admin/UI/AdminSearchInput.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { AdminLandingBlock, ResourceCollection } from '@/types';

    defineOptions({ layout: AdminLayout });

    const props = defineProps<{
        blocks: ResourceCollection<AdminLandingBlock>;
        filters: { search: string };
    }>();

    const search = (str: string): void => {
        if (str === props.filters.search) return;

        router.get(
            route('admin.features.index'),
            { search: str },
            {
                preserveScroll: true,
                preserveState: true,
                replace: true,
            },
        );
    };

    const toggleVisibility = (id: number) => {
        router.patch(
            route('admin.features.toggle', id),
            {},
            {
                preserveScroll: true,
            },
        );
    };

    const clearFilters = () => {
        search('');
    };
</script>

<template>
    <Teleport to="#admin-header-content">
        <h1 class="flex items-center gap-2 text-xl font-black text-white">
            Модерация блоков страниц
        </h1>
    </Teleport>

    <div class="animate-in fade-in slide-in-from-bottom-4 space-y-8 duration-700">
        <AdminSearchInput :model-value="props.filters.search" @search="search" />

        <Transition name="fade-slide">
            <div v-if="blocks.data.length">
                <TransitionGroup
                    name="list"
                    key="blocks"
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

            <AdminEmptyState
                v-else
                key="no-blocks"
                title="Блоки не найдены"
                @action="clearFilters"
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
