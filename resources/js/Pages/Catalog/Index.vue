<script setup lang="ts">
    import { type PropType, onUnmounted, ref, watch } from 'vue';

    import { Head, router } from '@inertiajs/vue3';

    import debounce from 'lodash/debounce';

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
                // Проверяем, что передается объект, а значение in_stock (если есть) является boolean
                if (typeof value !== 'object' || value === null) return false;
                if ('in_stock' in value && typeof value.in_stock !== 'boolean') return false;
                return true;
            },
        },
    });

    const search = ref(props.filters.search || '');
    const category = ref(props.filters.category ? Number(props.filters.category) : '');
    const sort = ref(props.filters.sort || '');
    const inStock = ref(props.filters.in_stock || false);

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
</script>

<template>
    <main>
        <Head title="Каталог продукции Ранчо" />

        {{ category }}

        <AppContainer>
            <div class="flex flex-col justify-between gap-4 md:flex-row md:items-end">
                <div>
                    <h1 class="text-3xl font-black text-slate-900">Наши продукты</h1>
                    <p class="mt-1 text-slate-500">Свежее к вашему столу</p>
                </div>

                <BaseInput v-model="search" placeholder="Найти продукт..." />
            </div>

            <div class="py-12">
                <div class="mb-8 border-b border-slate-100 pb-6">
                    <div class="flex flex-wrap items-center gap-4 pb-6">
                        <BaseSelect
                            label="Категория:"
                            placeholder="Все продукты"
                            v-model="category"
                            :options="categories.data"
                            :width-class="'w-64'"
                        />

                        <BaseSelect
                            label="Сортировка:"
                            placeholder="По умолчанию"
                            v-model="sort"
                            :options="sortArray"
                            :width-class="'w-64'"
                        />

                        <BaseSwitch v-model="inStock" label="Только в наличии" />
                    </div>

                    <button
                        v-if="category || search || sort || inStock"
                        @click="resetFilters"
                        class="self-center text-xs font-bold text-orange-600 hover:underline"
                    >
                        Сбросить всё
                    </button>
                </div>

                <Transition name="fade-slide" mode="out-in">
                    <!-- EMPTY STATE -->
                    <EmptyState
                        v-if="!products.data.length"
                        key="empty"
                        title="Товары не найдены"
                        description="Попробуйте изменить параметры поиска или сбросить фильтры."
                        action-label="Сбросить фильтры"
                        @action="resetFilters"
                    />

                    <div v-else>
                        <TransitionGroup
                            key="grid"
                            mode="out-in"
                            tag="section"
                            name="card-list"
                            aria-label="Карточки животных"
                            class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
                        >
                            <ProductCard
                                v-for="product in products.data"
                                :key="product.id"
                                :product="product"
                            />
                        </TransitionGroup>
                    </div>
                </Transition>
            </div>
        </AppContainer>

        <MainPagination :links="products.meta.links" />
    </main>
</template>
