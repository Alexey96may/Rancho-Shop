<script setup lang="ts">
    import { PropType, watch } from 'vue';

    import { router, useForm } from '@inertiajs/vue3';

    import { FunnelIcon } from '@heroicons/vue/24/outline';

    import AdminProductCard from '@/Components/Admin/Cards/AdminProductCard.vue';
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
    import type { AdminProduct, Animal, Category, Paginated, ResourceCollection } from '@/types';

    defineOptions({ layout: AdminLayout });

    const props = defineProps({
        products: {
            type: Object as PropType<Paginated<AdminProduct>>,
            required: true,
            validator: (value: unknown): boolean => {
                const val = value as Record<string, unknown>;
                const hasData = Array.isArray(val?.data);
                const hasMeta = val?.meta && typeof val.meta === 'object';

                if (!hasData || !hasMeta) {
                    console.warn(
                        'Runtime Error: The "products" prop must contain valid pagination metadata and data array.',
                    );
                }
                return !!(hasData && hasMeta);
            },
        },
        categories: {
            type: Object as PropType<ResourceCollection<Category>>,
            required: true,
            validator: (value: unknown): boolean => {
                const val = value as Record<string, unknown>;
                const hasData = Array.isArray(val?.data);

                if (!hasData) {
                    console.warn(
                        'Runtime Error: The "categories" prop must be a ResourceCollection containing a "data" array.',
                    );
                }
                return hasData;
            },
        },
        animals: {
            type: Object as PropType<ResourceCollection<Animal>>,
            required: true,
            validator: (value: unknown): boolean => {
                const val = value as Record<string, unknown>;
                const hasData = Array.isArray(val?.data);

                if (!hasData) {
                    console.warn(
                        'Runtime Error: The "animals" prop must be a ResourceCollection containing a "data" array.',
                    );
                }
                return hasData;
            },
        },
        filters: {
            type: Object as PropType<{
                search?: string;
                category?: number | string;
                animal?: number | string;
            }>,
            required: true,
            default: () => ({ search: '', category: null, animal: null }),
        },
    });

    const filterForm = useForm({
        search: props.filters.search || '',
        category: props.filters.category || null,
        animal: props.filters.animal || null,
    });

    const { navigateWithContext } = useAdminNavigation();
    const { deleteEntity, restoreEntity, isDeleting, isRestoring } = useAdminCrud();
    const { isFiltering, submitFilters, clearFilters } = useAdminFilters();

    const handleRestore = (id: number, name: string) => {
        restoreEntity('admin.products', id, name);
    };

    watch(
        () => [filterForm.search, filterForm.category, filterForm.animal],
        () => {
            submitFilters(filterForm, 'admin.products.index');
        },
    );
</script>

<template>
    <Teleport to="#admin-header-content">
        <AdminPageHeader title="Товары" subtitle="Управление Продуктами" />
    </Teleport>

    <section class="mb-8 space-y-4" role="search" aria-label="Фильтрация товаров">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <AdminSearchInput
                v-model="filterForm.search"
                placeholder="Поиск по названию или описанию..."
                class="md:max-w-md"
                aria-label="Введите текст для поиска"
            />

            <BaseCreateButton
                @click="navigateWithContext('admin.products')"
                label="Создать товар"
            />
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
                    placeholder="Все товары"
                    class="w-48"
                    variant="admin"
                    aria-label="Фильтр по товарам"
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
                        :disabled="isDeleting(product.id) || isRestoring(product.id)"
                        :product="product"
                        @edit="(p) => navigateWithContext('admin.products', 'edit', p.id)"
                        @restore="handleRestore"
                        @delete="
                            deleteEntity(
                                'admin.products',
                                product.id,
                                `Удаление товара «${product.name}»`,
                            )
                        "
                    />
                </TransitionGroup>
            </div>

            <AdminLoader v-else-if="isFiltering" key="loading" text="Синхронизация" />

            <AdminEmptyState
                v-else
                key="empty"
                :title="filterForm.search ? 'Товары не найдены' : 'Список товаров пуст'"
                @action="
                    filterForm.search
                        ? clearFilters(filterForm)
                        : navigateWithContext('admin.products')
                "
                :action-text="filterForm.search ? 'Очистить фильтр' : 'Добавить товар'"
                :show-action="true"
                :description="
                    filterForm.search
                        ? 'По запросу «' + filterForm.search + '» совпадений нет'
                        : 'Нет ни одного товара'
                "
            />
        </Transition>

        <AdminPagination :links="products.meta.links" />
    </main>
</template>

<style scoped>
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
