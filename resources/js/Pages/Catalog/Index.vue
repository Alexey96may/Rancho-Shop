<script setup lang="ts">
    import { ref, watch } from 'vue';

    import { Head, router } from '@inertiajs/vue3';

    import ProductCard from '@/Components/Cards/ProductCard.vue';
    import BaseSelect from '@/Components/UI/BaseSelect.vue';
    import MainLayout from '@/Layouts/MainLayout.vue';
    import { useCartStore } from '@/stores/cart';
    import type { Category, ProductWithCategory, ResourceCollection } from '@/types';

    const props = defineProps<{
        products: ResourceCollection<ProductWithCategory>;
        categories: ResourceCollection<Category>;
        filters: { category?: string; search?: string; sort?: string };
    }>();

    // Реактивные состояния для фильтров (инициализируем из пропсов)
    const search = ref(props.filters.search || '');
    const category = ref(props.filters.category || '');
    const sort = ref(props.filters.sort || '');

    // Функция отправки фильтров
    const applyFilters = () => {
        router.get(
            route('catalog.index'),
            {
                search: search.value,
                category: category.value,
                sort: sort.value,
            },
            {
                preserveState: true,
                replace: true, // чтобы не плодить историю при каждом символе поиска
            },
        );
    };

    // Следим за изменением категории и сортировки (срабатывают сразу)
    watch([category, sort], () => applyFilters());

    // Для поиска лучше добавить задержку (debounce), но пока сделаем по кнопке или Enter

    const sortArray = [
        { id: 1, name: 'Сначала дешевле', slug: 'cheap' },
        { id: 1, name: 'Сначала дороже', slug: 'expensive' },
    ];
</script>

<template>
    <Head title="Каталог продукции Ранчо" />

    <MainLayout
        ><main>
            <AppContainer>
                <div class="flex flex-col justify-between gap-4 md:flex-row md:items-end">
                    <div>
                        <h1 class="text-3xl font-black text-slate-900">Наши продукты</h1>
                        <p class="mt-1 text-slate-500">
                            Свежее из Симферополя прямо к вашему столу
                        </p>
                    </div>

                    <div class="relative">
                        <input
                            v-model="search"
                            @keyup.enter="applyFilters"
                            type="text"
                            placeholder="Найти продукт..."
                            class="w-full rounded-xl border-slate-200 py-2 pl-10 pr-4 focus:border-orange-500 focus:ring-orange-500 md:w-64"
                        />
                        <span class="absolute left-3 top-2.5 opacity-30">🔍</span>
                    </div>
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
                        </div>

                        <button
                            v-if="category || search || sort"
                            @click="
                                category = '';
                                search = '';
                                sort = '';
                                applyFilters();
                            "
                            class="self-center text-xs font-bold text-orange-600 hover:underline"
                        >
                            Сбросить всё
                        </button>
                    </div>

                    <div v-if="!products.data.length" class="py-20 text-center">
                        <div class="mb-4 text-6xl">🚜</div>
                        <h3 class="text-xl font-bold text-slate-900">Ничего не найдено</h3>
                        <p class="text-slate-500">Попробуйте изменить параметры фильтрации.</p>
                    </div>

                    <div
                        v-else
                        class="grid grid-cols-1 gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 xl:gap-x-8"
                    >
                        <ProductCard
                            v-for="product in products.data"
                            :key="product.id"
                            :product="product"
                        />
                    </div>
                </div>
            </AppContainer>
        </main>
    </MainLayout>
</template>
