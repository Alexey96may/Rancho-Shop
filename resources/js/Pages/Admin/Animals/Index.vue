<script setup lang="ts">
    import { PropType, watch } from 'vue';

    import { useForm } from '@inertiajs/vue3';

    import AnimalCard from '@/Components/Admin/Cards/AdminAnimalCard.vue';
    import AdminEmptyState from '@/Components/Admin/Shared/AdminEmptyState.vue';
    import AdminPageHeader from '@/Components/Admin/Shared/AdminPageHeader.vue';
    import AdminPagination from '@/Components/Admin/Shared/AdminPagination.vue';
    import AdminLoader from '@/Components/Admin/UI/AdminLoader.vue';
    import AdminSearchInput from '@/Components/Admin/UI/AdminSearchInput.vue';
    import BaseCreateButton from '@/Components/UI/BaseCreateButton.vue';
    import BaseSelect from '@/Components/UI/BaseSelect.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useAdminCrud } from '@/composables/crud/useAdminCrud';
    import { useAdminFilters } from '@/composables/routing/useAdminFilters';
    import { useAdminNavigation } from '@/composables/routing/useAdminNavigation';
    import { AdminAnimal, Category, Paginated, SeoData } from '@/types';

    defineOptions({ layout: AdminLayout });

    const props = defineProps({
        animals: {
            type: Object as PropType<Paginated<AdminAnimal>>,
            required: true,
            validator: (value: unknown): boolean => {
                const val = value as Record<string, unknown>;

                const hasData = Array.isArray(val?.data);
                const hasMeta = val?.meta && typeof val.meta === 'object';

                if (!hasData)
                    console.warn(
                        'Runtime error: The "data" array is missing from the "animals" prop.',
                    );
                if (!hasMeta)
                    console.warn('Runtime error: The "animals" prop is missing the "meta" object.');

                return !!(hasData && hasMeta);
            },
        },
        categories: {
            type: Array as PropType<Category[]>,
            required: true,
            validator: (value: unknown): boolean => {
                if (!Array.isArray(value)) return false;

                return value.every((item: unknown) => {
                    const category = item as Record<string, unknown>;

                    const isValid = 'id' in category && 'name' in category;

                    if (!isValid) {
                        console.warn(
                            'Runtime Error: Array element "categories" does not contain an ID or name.',
                            category,
                        );
                    }
                    return isValid;
                });
            },
        },
        filters: {
            type: Object as PropType<{ search?: string; category_id?: string | number }>,
            required: true,
            default: () => ({ search: '', category_id: null }),
        },
        seo: {
            type: Object as PropType<SeoData>,
            required: true,
            validator: (value: unknown): boolean => {
                const val = value as Record<string, unknown>;
                return typeof val?.title === 'string';
            },
        },
    });

    const filterForm = useForm({
        search: props.filters.search || '',
        category_id: props.filters.category_id || null,
    });

    const { navigateWithContext } = useAdminNavigation();
    const { deleteEntity, restoreEntity, isDeleting, isRestoring } = useAdminCrud();
    const { isFiltering, submitFilters, clearFilters } = useAdminFilters();

    const handleRestore = (id: number, name: string) => {
        restoreEntity('admin.animals', id, name);
    };

    watch(
        () => [filterForm.search, filterForm.category_id],
        () => {
            submitFilters(filterForm, 'admin.animals.index');
        },
    );
</script>

<template>
    <Teleport to="#admin-header-content">
        <AdminPageHeader title="Список Животных" subtitle="Управление базой животных" />
    </Teleport>

    <div class="p-8">
        <div class="space-y-10">
            <section class="flex flex-col gap-4 lg:flex-row lg:items-end" aria-label="Фильтрация">
                <AdminSearchInput
                    v-model="filterForm.search"
                    placeholder="Поиск по имени..."
                    label="Поиск животных"
                />

                <BaseSelect
                    v-model="filterForm.category_id"
                    :options="categories"
                    placeholder="Все категории"
                    variant="admin"
                    class="lg:w-64"
                />

                <BaseCreateButton
                    label="Добавить"
                    @click="navigateWithContext('admin.animals', 'create')"
                />
            </section>

            <div class="relative min-h-[400px]">
                <Transition name="fade-slide">
                    <TransitionGroup
                        v-if="animals.data.length > 0"
                        :key="animals.data.length"
                        tag="main"
                        name="animal-grid"
                        class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3"
                        role="list"
                    >
                        <AnimalCard
                            v-for="animal in animals.data"
                            :key="animal.id"
                            :animal="animal"
                            :disabled="isDeleting(animal.id) || isRestoring(animal.id)"
                            :class="{
                                'pointer-events-none scale-95 opacity-50': isDeleting(animal.id),
                            }"
                            @edit="navigateWithContext('admin.animals', 'edit', animal.id)"
                            @restore="handleRestore"
                            @delete="
                                deleteEntity(
                                    'admin.animals',
                                    animal.id,
                                    `Удаление животного «${animal.name}»`,
                                )
                            "
                        />
                    </TransitionGroup>

                    <AdminLoader v-else-if="isFiltering" key="loading" text="Синхронизация" />

                    <AdminEmptyState
                        v-else
                        key="empty"
                        title="Животные не найдены"
                        @action="clearFilters(filterForm)"
                        :show-action="true"
                    />
                </Transition>
            </div>

            <AdminPagination :links="animals.meta.links" />
        </div>
    </div>
</template>

<style scoped>
    .animal-grid-enter-active,
    .animal-grid-leave-active {
        transition: all 0.5s ease-out;
    }
    .animal-grid-enter-from {
        opacity: 0;
        transform: translateY(30px) scale(0.9);
    }
    .animal-grid-leave-to {
        opacity: 0;
        transform: scale(0.8);
    }
    .animal-grid-move {
        transition: transform 0.5s ease;
    }
    .animal-grid-leave-active {
        position: absolute;
        width: 100%;
    }
    .fade-slide-enter-active {
        transition: all 0.4s ease-out;
    }
    .fade-slide-enter-from {
        opacity: 0;
        transform: translateY(-10px);
    }
</style>
