<script setup lang="ts">
    import { type PropType, computed, onUnmounted, ref, watch } from 'vue';

    import { router } from '@inertiajs/vue3';

    import { debounce } from 'lodash-es';

    import AnimalCard from '@/Components/Cards/AnimalCard.vue';
    import MainPagination from '@/Components/Shared/MainPagination.vue';
    import AnimalCardSkeleton from '@/Components/Skeletons/AnimalCardSkeleton.vue';
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
        hideSkeleton.cancel();

        showSkeleton();

        router.get(
            route('animals.index'),
            {
                search: search.value,
                category_id: categoryId.value,
                status: status.value,
            },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
                onFinish: () => {
                    showSkeleton.cancel();
                    hideSkeleton();
                },
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
        showSkeleton.cancel();
        hideSkeleton.cancel();
    });

    const resetFilters = () => {
        search.value = '';
        categoryId.value = '';
        status.value = '';
        applyFilters();
    };

    const hasActiveFilters = computed(() => {
        return Boolean(search.value.trim() || categoryId.value || status.value);
    });

    const isFiltering = ref(false);

    const showSkeleton = debounce(() => {
        isFiltering.value = true;
    }, 200);

    const hideSkeleton = debounce(() => {
        isFiltering.value = false;
    }, 150);
</script>

<template>
    <AppContainer class="min-h-screen bg-rancho-paper">
        <div class="pt-12">
            <!-- HEADER & SEARCH -->
            <header
                v-scroll-reveal
                class="mb-8 flex flex-col gap-6 sm:mb-20 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h1 class="text-2xl font-black text-rancho-forest sm:text-4xl">Наши жители</h1>
                    <p class="mt-2 text-xs text-rancho-olive sm:text-sm">
                        Найдено: {{ animals.meta.total }}
                    </p>
                </div>
            </header>

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-[260px_1fr]">
                <!-- ФИЛЬТРЫ -->
                <aside class="space-y-8 lg:sticky lg:top-24 lg:self-start">
                    <BaseInput
                        v-model="search"
                        placeholder="Найти животное..."
                        theme="light"
                        variant="search"
                        class="w-full"
                    />

                    <div v-scroll-reveal class="rounded-2xl bg-white px-4 py-6 shadow-rancho-sm">
                        <div class="mb-8 flex items-center justify-between gap-2">
                            <h2 class="text-sm font-bold text-rancho-forest">Фильтры</h2>
                            <button
                                v-if="hasActiveFilters"
                                @click="resetFilters"
                                class="text-xs font-bold text-rancho-ember hover:underline"
                            >
                                Сбросить
                            </button>
                        </div>

                        <div class="flex flex-col gap-4">
                            <BaseSelect
                                label="Категория:"
                                placeholder="Все категории"
                                v-model="categoryId"
                                :options="categories.data"
                                width-class="w-full"
                            />

                            <BaseSelect
                                label="Статус:"
                                placeholder="Все статусы"
                                v-model="status"
                                :options="statuses"
                                width-class="w-full"
                            />
                        </div>
                    </div>
                </aside>

                <!-- ЖИВОТНЫЕ -->
                <div>
                    <Transition name="fade-slide" mode="out-in">
                        <!-- СКЕЛЕТОНЫ -->
                        <div
                            v-if="isFiltering"
                            key="skeletons"
                            class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-3"
                            aria-busy="true"
                            aria-label="Загрузка животных"
                        >
                            <AnimalCardSkeleton v-for="n in 6" :key="`skeleton-${n}`" />
                        </div>

                        <!-- ПУСТОЕ СОСТОЯНИЕ -->
                        <EmptyState
                            v-else-if="!animals.data.length"
                            key="empty"
                            title="Животные не найдены"
                            description="Попробуйте изменить параметры поиска или сбросить фильтры."
                            action-label="Сбросить фильтры"
                            @action="resetFilters"
                        />

                        <!-- РЕАЛЬНЫЕ КАРТОЧКИ -->
                        <TransitionGroup
                            v-else
                            key="grid"
                            tag="section"
                            name="card-list"
                            aria-label="Карточки животных"
                            class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-3"
                        >
                            <AnimalCard
                                v-for="animal in animals.data"
                                :key="animal.id"
                                :animal="animal"
                            />
                        </TransitionGroup>
                    </Transition>
                </div>
            </div>
        </div>

        <MainPagination v-scroll-reveal :links="animals.meta.links" theme="light" />
    </AppContainer>
</template>
