<script setup lang="ts">
    import { ref, watch } from 'vue';

    import { Head, router } from '@inertiajs/vue3';

    import ProductCard from '@/Components/Cards/ProductCard.vue';
    import MainLayout from '@/Layouts/MainLayout.vue';
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
</script>

<template>
    <Head title="Каталог продукции Ранчо" />

    <MainLayout
        ><AppContainer>
            <template #header>
                <div class="flex flex-col justify-between gap-4 md:flex-row md:items-end">
                    <div>
                        <h1 class="text-slate-900 text-3xl font-black">Наши продукты</h1>
                        <p class="text-slate-500 mt-1">
                            Свежее из Симферополя прямо к вашему столу
                        </p>
                    </div>

                    <div class="relative">
                        <input
                            v-model="search"
                            @keyup.enter="applyFilters"
                            type="text"
                            placeholder="Найти продукт..."
                            class="border-slate-200 focus:ring-orange-500 focus:border-orange-500 w-full rounded-xl py-2 pl-10 pr-4 md:w-64"
                        />
                        <span class="absolute left-3 top-2.5 opacity-30">🔍</span>
                    </div>
                </div>
            </template>

            <div class="py-12">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div
                        class="border-slate-100 mb-8 flex flex-wrap items-center gap-4 border-b pb-6"
                    >
                        <div class="flex items-center gap-2">
                            <span class="text-slate-400 text-xs font-bold uppercase"
                                >Категория:</span
                            >
                            <select
                                v-model="category"
                                class="border-slate-200 focus:ring-orange-500 rounded-lg text-sm"
                            >
                                <option value="">Все продукты</option>
                                <option
                                    v-for="cat in categories.data"
                                    :key="cat.id"
                                    :value="cat.slug"
                                >
                                    {{ cat.name }}
                                </option>
                            </select>
                        </div>

                        <div class="flex items-center gap-2">
                            <span class="text-slate-400 text-xs font-bold uppercase">Цена:</span>
                            <select
                                v-model="sort"
                                class="border-slate-200 focus:ring-orange-500 rounded-lg text-sm"
                            >
                                <option value="">По умолчанию</option>
                                <option value="cheap">Сначала дешевле</option>
                                <option value="expensive">Сначала дороже</option>
                            </select>
                        </div>

                        <button
                            v-if="category || search || sort"
                            @click="
                                category = '';
                                search = '';
                                sort = '';
                                applyFilters();
                            "
                            class="text-orange-600 text-xs font-bold hover:underline"
                        >
                            Сбросить всё
                        </button>
                    </div>

                    <div v-if="!products.data.length" class="py-20 text-center">
                        <div class="mb-4 text-6xl">🚜</div>
                        <h3 class="text-slate-900 text-xl font-bold">Ничего не найдено</h3>
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
            </div>
        </AppContainer>
    </MainLayout>
</template>
