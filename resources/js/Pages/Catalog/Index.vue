<script setup lang="ts">
    import { type PropType, computed, onUnmounted, ref, watch } from 'vue';

    import { router } from '@inertiajs/vue3';

    import { debounce } from 'lodash-es';

    import ProductCard from '@/Components/Cards/ProductCard.vue';
    import MainPagination from '@/Components/Shared/MainPagination.vue';
    import BaseInput from '@/Components/UI/BaseInput.vue';
    import BaseSelect from '@/Components/UI/BaseSelect.vue';
    import BaseSwitch from '@/Components/UI/BaseSwitch.vue';
    import EmptyState from '@/Components/UI/EmptyState.vue';
    import MainLayout from '@/Layouts/MainLayout.vue';
    import type { Category, Paginated, Product, ResourceCollection } from '@/types';

    interface CatalogFilters {
        category?: string;
        search?: string;
        sort?: string;
        in_stock?: boolean;
    }

    defineOptions({ layout: MainLayout });

    const props = defineProps({
        products: {
            type: Object as PropType<Paginated<Product>>,
            required: true,
            validator: (value: Paginated<Product>) => {
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
        filters: {
            type: Object as PropType<CatalogFilters>,
            required: false,
            default: () => ({}),
            validator: (value: CatalogFilters) => {
                if (typeof value !== 'object' || value === null) return false;
                if ('in_stock' in value && typeof value.in_stock !== 'boolean') return false;
                return true;
            },
        },
    });

    const search = ref(props.filters.search || '');
    const category = ref(props.filters.category ? Number(props.filters.category) : '');
    const sort = ref(props.filters.sort || '');
    const inStock = ref(Boolean(props.filters.in_stock) || false);

    const applyFilters = () => {
        router.get(
            route('catalog.index'),
            {
                search: search.value,
                category: category.value,
                sort: sort.value,
                in_stock: inStock.value,
            },
            {
                preserveState: true,
                replace: true,
            },
        );
    };

    watch([category, sort, inStock], () => {
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

    const sortArray = [
        { id: 'cheap', name: 'Сначала дешевле' },
        { id: 'expensive', name: 'Сначала дороже' },
    ];

    const resetFilters = () => {
        category.value = '';
        search.value = '';
        sort.value = '';
        inStock.value = false;
        applyFilters();
    };

    const hasActiveFilters = computed(() => {
        if (search.value.trim() !== '') return true;
        if (category.value) return true;

        const validSorts = sortArray.map((s) => s.id);
        if (sort.value && validSorts.includes(String(sort.value))) return true;

        if (inStock.value === true) return true;

        return false;
    });
</script>

<template>
    <div class="py-12">
        <AppContainer>
            <header
                v-scroll-reveal
                class="mb-8 flex flex-col justify-between gap-6 sm:mb-20 md:flex-row md:items-end"
            >
                <div>
                    <h1 class="text-2xl font-black text-rancho-forest sm:text-4xl">
                        Наши продукты
                    </h1>
                    <p class="mt-2 text-xs text-rancho-olive sm:text-sm">
                        Найдено: {{ products.meta.total }}
                    </p>
                </div>

                <BaseInput
                    v-model="search"
                    placeholder="Найти продукт..."
                    theme="light"
                    variant="search"
                    class="w-full md:w-72"
                />
            </header>

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-[260px_1fr]">
                <!-- ФИЛЬТРЫ -->
                <aside class="lg:sticky lg:top-24 lg:self-start">
                    <div v-scroll-reveal class="rounded-2xl bg-white px-4 py-6 shadow-rancho-sm">
                        <div class="mb-8 flex items-center justify-between gap-2">
                            <h2 class="text-sm font-bold text-rancho-forest">Фильтры</h2>
                            <button
                                v-if="hasActiveFilters"
                                @click="resetFilters"
                                class="text-rancho-ember text-xs font-bold hover:underline"
                            >
                                Сбросить
                            </button>
                        </div>

                        <div class="flex flex-col gap-4">
                            <BaseSelect
                                label="Категория:"
                                placeholder="Все продукты"
                                v-model="category"
                                :options="categories.data"
                                width-class="w-full"
                            />

                            <BaseSelect
                                label="Сортировка:"
                                placeholder="По умолчанию"
                                v-model="sort"
                                :options="sortArray"
                                width-class="w-full"
                            />

                            <BaseSwitch
                                v-model="inStock"
                                label="Только в наличии"
                                active-text=""
                                inactive-text=""
                                theme="light"
                            />
                        </div>
                    </div>
                </aside>

                <!-- ТОВАРЫ -->
                <div>
                    <Transition name="fade-slide" mode="out-in">
                        <EmptyState
                            v-if="!products.data.length"
                            key="empty"
                            title="Товары не найдены"
                            description="Попробуйте изменить параметры поиска или сбросить фильтры."
                            action-label="Сбросить фильтры"
                            @action="resetFilters"
                        />

                        <TransitionGroup
                            v-else
                            key="grid"
                            tag="section"
                            name="card-list"
                            class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-3"
                        >
                            <ProductCard
                                v-for="product in products.data"
                                :key="product.id"
                                :product="product"
                            />
                        </TransitionGroup>
                    </Transition>
                </div>
            </div>
        </AppContainer>
        <MainPagination v-scroll-reveal :links="products.meta.links" theme="light" />
    </div>
</template>
