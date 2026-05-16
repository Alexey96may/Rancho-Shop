<script setup lang="ts">
    import { reactive, ref, watch } from 'vue';

    import { router } from '@inertiajs/vue3';

    import AnimalCard from '@/Components/Admin/Cards/AdminAnimalCard.vue';
    import AdminEmptyState from '@/Components/Admin/Shared/AdminEmptyState.vue';
    import AdminPageHeader from '@/Components/Admin/Shared/AdminPageHeader.vue';
    import AdminPagination from '@/Components/Admin/Shared/AdminPagination.vue';
    import AdminLoader from '@/Components/Admin/UI/AdminLoader.vue';
    import AdminSearchInput from '@/Components/Admin/UI/AdminSearchInput.vue';
    import BaseCreateButton from '@/Components/UI/BaseCreateButton.vue';
    import BaseSelect from '@/Components/UI/BaseSelect.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useFlash } from '@/composables/useFlash';
    import { AdminAnimal, Category, Paginated, SeoData } from '@/types';

    const props = defineProps<{
        animals: Paginated<AdminAnimal>;
        categories: Category[];
        filters: { search?: string; category_id?: string | number };
        seo: SeoData;
    }>();

    defineOptions({ layout: AdminLayout });

    const { notifyWithUndo } = useFlash();
    const isFiltering = ref(false);

    const search = ref(props.filters.search || '');
    const categoryFilter = ref(props.filters.category_id || '');

    const performSearch = (val: string = search.value, cat: any = categoryFilter.value) => {
        isFiltering.value = true;
        router.get(
            route('admin.animals.index'),
            { search: val, category_id: cat },
            {
                preserveState: true,
                replace: true,
                preserveScroll: true,
                onFinish: () => {
                    isFiltering.value = false;
                },
            },
        );
    };

    watch(categoryFilter, (newVal) => {
        performSearch(search.value, newVal);
    });

    const clearFilters = () => {
        search.value = '';
        categoryFilter.value = '';
        performSearch('', '');
        router.get(route('admin.animals.index'), {}, { preserveScroll: true });
    };

    const deletingIds = reactive<Set<number>>(new Set());

    const deleteAnimal = async (id: number) => {
        if (deletingIds.has(id)) return;
        deletingIds.add(id);
        const confirmed = await notifyWithUndo(`Удаление животного`, 4000);
        if (confirmed) {
            router.delete(route('admin.animals.destroy', id), {
                preserveScroll: true,
                onFinish: () => deletingIds.delete(id),
            });
        } else {
            deletingIds.delete(id);
        }
    };

    const navigateWithContext = (animalId?: number) => {
        if (!window) return '';
        const currentParams = window.location.search;

        if (animalId) {
            router.get(route('admin.animals.edit', animalId), { back: currentParams });
        } else {
            router.get(route('admin.animals.create'), { back: currentParams });
        }
    };
</script>

<template>
    <Teleport to="#admin-header-content">
        <AdminPageHeader title="Список Животных" subtitle="Управление базой животных" />
    </Teleport>

    <div class="p-8">
        <div class="space-y-10">
            <section class="flex flex-col gap-4 lg:flex-row lg:items-end" aria-label="Фильтрация">
                <AdminSearchInput
                    v-model="search"
                    @search="performSearch"
                    placeholder="Поиск по имени..."
                    label="Поиск животных"
                />

                <BaseSelect
                    v-model="categoryFilter"
                    :options="categories"
                    placeholder="Все категории"
                    variant="admin"
                    class="lg:w-64"
                />

                <BaseCreateButton label="Добавить" @click="navigateWithContext()" />
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
                            :disabled="deletingIds.has(animal.id)"
                            :class="{
                                'pointer-events-none scale-95 opacity-50': deletingIds.has(
                                    animal.id,
                                ),
                            }"
                            @edit="navigateWithContext(animal.id)"
                            @delete="deleteAnimal(animal.id)"
                        />
                    </TransitionGroup>

                    <AdminLoader v-else-if="isFiltering" key="loading" text="Синхронизация" />

                    <AdminEmptyState
                        v-else
                        key="empty"
                        title="Животные не найдены"
                        @action="clearFilters"
                        :show-action="true"
                    />
                </Transition>
            </div>

            <Transition name="fade-slide" mode="out-in">
                <AdminPagination v-if="!isFiltering" :links="animals.meta.links" />
            </Transition>
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
