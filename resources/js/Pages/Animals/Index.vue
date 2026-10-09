<script setup lang="ts">
    import { type PropType, onUnmounted, ref, watch } from 'vue';

    import { router } from '@inertiajs/vue3';

    import debounce from 'lodash/debounce';

    import AnimalCard from '@/Components/Cards/AnimalCard.vue';
    import MainPagination from '@/Components/Shared/MainPagination.vue';
    import BaseInput from '@/Components/UI/BaseInput.vue';
    import BaseSelect from '@/Components/UI/BaseSelect.vue';
    import EmptyState from '@/Components/UI/EmptyState.vue';
    import MainLayout from '@/Layouts/MainLayout.vue';
    import type { Animal, Category, Paginated, ResourceCollection } from '@/types';

    interface AnimalStatus {
        id: string;
        name: string;
    }

    interface AnimalFilters {
        search?: string;
        category_id?: string | number;
        status?: string;
    }

    defineOptions({ layout: MainLayout });

    const props = defineProps({
        animals: {
            type: Object as PropType<Paginated<Animal>>,
            required: true,
            validator: (value: Paginated<Animal>) => {
                return Boolean(value && Array.isArray(value.data));
            },
        },
        categories: {
            type: Object as PropType<ResourceCollection<Category>>,
            required: true,
            validator: (value: ResourceCollection<Category>) => {
                return Boolean(value && Array.isArray(value.data));
            },
        },
        statuses: {
            type: Array as PropType<AnimalStatus[]>,
            required: true,
            validator: (value: unknown[]) => {
                if (!Array.isArray(value)) return false;
                return value.every(
                    (item) =>
                        typeof item === 'object' && item !== null && 'id' in item && 'name' in item,
                );
            },
        },
        filters: {
            type: Object as PropType<AnimalFilters>,
            required: false,
            default: () => ({}),
            validator: (value: AnimalFilters) => {
                return typeof value === 'object' && value !== null;
            },
        },
    });

    const search = ref(props.filters.search || '');
    const categoryId = ref(props.filters.category_id || '');
    const status = ref(props.filters.status || '');

    const applyFilters = () => {
        router.get(
            route('animals.index'),
            {
                search: search.value,
                category_id: categoryId.value,
                status: status.value,
            },
            {
                preserveState: true,
                replace: true,
            },
        );
    };

    watch([categoryId, status], () => {
        applyFilters();
    });

    const debouncedSearch = debounce(() => {
        applyFilters();
    }, 400);

    watch(search, () => {
        debouncedSearch();
    });

    onUnmounted(() => {
        debouncedSearch.cancel();
    });

    const resetFilters = () => {
        search.value = '';
        categoryId.value = '';
        status.value = '';
        applyFilters();
    };
</script>

<template>
    <AppContainer class="min-h-screen bg-[#fcfaf5]">
        <div class="mx-auto max-w-7xl px-6 py-10">
            <!-- HEADER & SEARCH -->
            <header class="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-end">
                <div>
                    <h1 class="text-4xl font-black text-[#1c3f34]">Наши жители фермы</h1>
                    <p class="mt-2 text-sm text-[#597d5b]">
                        Познакомьтесь с животными, которые живут на нашей ферме
                    </p>
                </div>

                <BaseInput
                    v-model="search"
                    placeholder="Найти животное..."
                    class="w-full md:w-72"
                />
            </header>

            <!-- FILTERS BAR -->
            <section class="mb-8 border-b border-slate-200/60 pb-6" aria-label="Фильтры">
                <div class="flex flex-wrap items-center gap-4">
                    <BaseSelect
                        label="Категория:"
                        placeholder="Все категории"
                        v-model="categoryId"
                        :options="categories.data"
                        width-class="w-64"
                    />

                    <BaseSelect
                        label="Статус:"
                        placeholder="Все статусы"
                        v-model="status"
                        :options="statuses"
                        width-class="w-64"
                    />

                    <!-- Кнопка сброса с плавной анимацией появления -->
                    <Transition name="fade">
                        <button
                            v-if="search || categoryId || status"
                            @click="resetFilters"
                            class="self-end pb-2 text-xs font-bold text-orange-600 transition-colors hover:underline"
                        >
                            Сбросить всё
                        </button>
                    </Transition>
                </div>
            </section>

            <Transition name="fade-slide" mode="out-in">
                <!-- EMPTY STATE -->
                <EmptyState
                    v-if="!animals.data.length"
                    key="empty"
                    title="Животные не найдены"
                    description="Попробуйте изменить параметры поиска или сбросить фильтры."
                    action-label="Сбросить фильтры"
                    @action="resetFilters"
                />

                <!-- GRID -->
                <div v-else>
                    <TransitionGroup
                        key="grid"
                        mode="out-in"
                        tag="section"
                        name="card-list"
                        aria-label="Карточки животных"
                        class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
                    >
                        <AnimalCard
                            v-for="animal in animals.data"
                            :key="animal.id"
                            :animal="animal"
                        />
                    </TransitionGroup>
                </div>
            </Transition>
        </div>

        <MainPagination :links="animals.meta.links" />
    </AppContainer>
</template>
