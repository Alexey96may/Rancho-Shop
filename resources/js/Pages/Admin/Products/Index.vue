<script setup lang="ts">
    import { ref, watch } from 'vue';

    import { router, useForm } from '@inertiajs/vue3';

    import { FunnelIcon, PlusIcon, XMarkIcon } from '@heroicons/vue/24/outline';
    import { debounce } from 'lodash';

    import AdminProductCard from '@/Components/Admin/Cards/AdminProductCard.vue';
    import AdminEmptyState from '@/Components/Admin/Shared/AdminEmptyState.vue';
    import AdminPagination from '@/Components/Admin/Shared/AdminPagination.vue';
    import AdminLoader from '@/Components/Admin/UI/AdminLoader.vue';
    import AdminSearchInput from '@/Components/Admin/UI/AdminSearchInput.vue';
    import BaseSelect from '@/Components/UI/BaseSelect.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useFlash } from '@/composables/useFlash';
    import type { AdminProduct, Animal, Category, Paginated, ResourceCollection } from '@/types';

    defineOptions({ layout: AdminLayout });

    const { notifyWithUndo } = useFlash();

    const props = defineProps<{
        products: Paginated<AdminProduct>;
        categories: ResourceCollection<Category>;
        animals: ResourceCollection<Animal>;
        filters: {
            search?: string;
            category?: number | string;
            animal?: number | string;
        };
    }>();

    const isDeleteModalOpen = ref(false);

    const isFiltering = ref(false);
    const deletingIds = ref(new Set<number>());

    const filterForm = useForm({
        search: props.filters.search || '',
        category: props.filters.category || null,
        animal: props.filters.animal || null,
    });

    const submitFilters = debounce(() => {
        filterForm.get(route('admin.products.index'), {
            preserveState: true,
            replace: true,
            preserveScroll: true,

            onFinish: () => {
                isFiltering.value = false;
            },
        });
    }, 400);

    const resetFilters = () => {
        filterForm.search = '';
        filterForm.category = null;
        filterForm.animal = null;
        submitFilters();
    };

    const goToCreate = () => {
        router.get(route('admin.products.create'));
    };

    watch(
        () => [filterForm.search, filterForm.category, filterForm.animal],
        () => {
            isFiltering.value = true;
            submitFilters();
        },
    );

    const deleteProduct = async (product: AdminProduct) => {
        if (deletingIds.value.has(product.id)) return;
        deletingIds.value.add(product.id);

        const isTimeOut = await notifyWithUndo(`Удаление товара «${product.name}»`);

        if (isTimeOut) {
            router.delete(route('admin.products.destroy', product.id), {
                preserveScroll: true,
                onFinish: () => {
                    deletingIds.value.delete(product.id);
                },
            });
        } else {
            deletingIds.value.delete(product.id);
        }
    };
</script>

<template>
    <Teleport to="#admin-header-content">
        <h1 class="text-xl font-black text-white">Управление Товарами</h1>
    </Teleport>

    <section class="mb-8 space-y-4" role="search" aria-label="Фильтрация товаров">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <AdminSearchInput
                v-model="filterForm.search"
                placeholder="Поиск по названию или описанию..."
                class="md:max-w-md"
                aria-label="Введите текст для поиска"
            />

            <div class="flex items-center gap-3">
                <button
                    v-if="filterForm.isDirty || filterForm.search"
                    @click="resetFilters"
                    class="rounded-lg px-2 text-[10px] font-black uppercase tracking-widest text-slate-500 transition-colors hover:text-white focus:outline-none focus:ring-2 focus:ring-orange-500"
                >
                    Сбросить
                </button>

                <button
                    @click="router.get(route('admin.products.create'))"
                    class="shadow-lg inline-flex items-center gap-2 rounded-2xl bg-orange-600 px-6 py-3 text-[12px] font-black uppercase tracking-widest text-white shadow-orange-600/20 transition-all hover:bg-orange-500 focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-slate-900 active:scale-95"
                    title="Создать новый товар"
                >
                    <PlusIcon class="h-5 w-5" />
                    Создать товар
                </button>
            </div>
        </div>

        <div class="mt-4">
            <div class="mb-4 flex items-center gap-2 text-slate-500" aria-hidden="true">
                <FunnelIcon class="h-4 w-4" />
                <span class="text-[10px] font-black uppercase tracking-widest">Фильтры:</span>
            </div>

            <div class="flex flex-wrap items-center gap-4">
                <BaseSelect
                    v-model="filterForm.category"
                    :options="categories.data"
                    placeholder="Все категории"
                    class="w-48"
                    variant="admin"
                    aria-label="Фильтр по категориям"
                />

                <BaseSelect
                    v-model="filterForm.animal"
                    :options="animals.data"
                    placeholder="Все животные"
                    class="w-48"
                    variant="admin"
                    aria-label="Фильтр по животным"
                />
            </div>
        </div>

        <div class="sr-only" aria-live="polite">
            {{
                products.data.length
                    ? `Найдено товаров: ${products.data.length}`
                    : 'Товары не найдены'
            }}
        </div>
    </section>

    <main class="space-y-8">
        <Transition name="fade-slide" mode="out-in">
            <div v-if="products.data.length" key="products">
                <TransitionGroup
                    tag="div"
                    name="product-grid"
                    class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
                >
                    <AdminProductCard
                        v-for="product in products.data"
                        :key="product.id"
                        :disabled="deletingIds.has(product.id)"
                        :product="product"
                        @edit="(p) => router.get(route('admin.products.edit', p.id))"
                        @delete="deleteProduct(product)"
                    />
                </TransitionGroup>

                <div class="mt-8">
                    <AdminPagination :links="products.meta.links" />
                </div>
            </div>

            <AdminLoader v-else-if="isFiltering" key="loading" text="Синхронизация" />

            <AdminEmptyState
                v-else
                key="empty"
                :title="filterForm.search ? 'Товары не найдены' : 'Список товаров пуст'"
                @action="filterForm.search ? resetFilters() : goToCreate()"
                :action-text="filterForm.search ? 'Очистить фильтр' : 'Добавить товар'"
                :show-action="true"
                :description="
                    filterForm.search
                        ? 'По запросу «' + filterForm.search + '» совпадений нет'
                        : 'Нет ни одного товара'
                "
            />
        </Transition>
    </main>
</template>

<style scoped>
    /* Анимация появления и перемещения карточек */
    .product-grid-enter-active,
    .product-grid-leave-active {
        transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .product-grid-enter-from,
    .product-grid-leave-to {
        opacity: 0;
        transform: scale(0.9) translateY(20px);
    }

    /* Эта часть отвечает за плавное перемещение соседей при удалении/добавлении */
    .product-grid-move {
        transition: transform 0.4s ease;
    }

    /* Чтобы удаляемый элемент не выбивал соседей из потока во время анимации */
    .product-grid-leave-active {
        position: absolute;
        /* Нужна логика для ширины, если используется absolute в гридах. 
       В простых гридах лучше оставить без absolute или ограничить ширину. */
        visibility: hidden;
    }

    .fade-slide-enter-active {
        transition: all 0.4s ease-out;
    }

    .fade-slide-enter-from {
        opacity: 0;
        transform: translateY(-10px);
    }
</style>
