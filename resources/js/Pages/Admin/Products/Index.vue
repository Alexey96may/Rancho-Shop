<script setup lang="ts">
    import { type PropType, watch } from 'vue';

    import { useForm } from '@inertiajs/vue3';

    import { FunnelIcon } from '@heroicons/vue/24/outline';

    import AdminProductCard from '@/Components/Admin/Cards/AdminProductCard.vue';
    import AdminEmptyState from '@/Components/Admin/Shared/AdminEmptyState.vue';
    import AdminPageHeader from '@/Components/Admin/Shared/AdminPageHeader.vue';
    import AdminLoader from '@/Components/Admin/UI/AdminLoader.vue';
    import AdminSearchInput from '@/Components/Admin/UI/AdminSearchInput.vue';
    import AdminPagination from '@/Components/Shared/MainPagination.vue';
    import BaseCreateButton from '@/Components/UI/BaseCreateButton.vue';
    import BaseSelect from '@/Components/UI/BaseSelect.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useAdminCrud } from '@/composables/crud/useAdminCrud';
    import { useAdminFilters } from '@/composables/routing/useAdminFilters';
    import { useAdminNavigation } from '@/composables/routing/useAdminNavigation';
    import type { AdminProduct, Category, Paginated, ResourceCollection } from '@/types';

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
            type: Array as PropType<Array<{ id: number; name: string }>>,
            required: true,
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

    <div class="space-y-10">
        <!-- FILTERS -->
        <section class="mb-6 space-y-6 sm:mb-8" role="search" aria-label="Фильтрация товаров">
            <!-- SEARCH + CREATE -->
            <div
                class="flex flex-col-reverse gap-4 sm:flex-row sm:items-end sm:justify-between sm:gap-4"
            >
                <AdminSearchInput
                    v-model="filterForm.search"
                    placeholder="Поиск по названию или описанию..."
                    class="w-full sm:max-w-md"
                    aria-label="Введите текст для поиска"
                />

                <BaseCreateButton
                    @click="navigateWithContext('admin.products')"
                    label="Создать товар"
                    class="self-end sm:self-auto"
                />
            </div>

            <!-- FILTERS LABEL -->
            <div class="flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-end sm:gap-4">
                <BaseSelect
                    v-model="filterForm.category"
                    :options="categories.data"
                    placeholder="Все категории"
                    label="Категория"
                    class="w-full sm:w-56 lg:w-64"
                    variant="admin"
                    aria-label="Фильтр по категориям"
                />

                <BaseSelect
                    v-model="filterForm.animal"
                    :options="animals"
                    placeholder="Все товары"
                    label="Связанное животное"
                    class="w-full sm:w-56 lg:w-64"
                    variant="admin"
                    aria-label="Фильтр по товарам"
                />
            </div>

            <!-- SR-ONLY LIVE -->
            <div class="sr-only" aria-live="polite">
                {{
                    products.data.length
                        ? `Найдено товаров: ${products.data.length}`
                        : 'Товары не найдены'
                }}
            </div>
        </section>

        <!-- LIST -->
        <main class="space-y-10">
            <Transition name="fade-slide" mode="out-in">
                <div v-if="products.data.length" key="products">
                    <TransitionGroup
                        tag="div"
                        name="product-grid"
                        class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-6 lg:grid-cols-3 xl:grid-cols-4"
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

            <AdminPagination :links="products.meta.links" key="" theme="dark" />
        </main>
    </div>
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

    .product-grid-move {
        transition: transform 0.4s ease;
    }

    .product-grid-leave-active {
        position: absolute;
        visibility: hidden;
    }

    .fade-slide-enter-active {
        transition: all 0.4s ease-out;
    }

    .fade-slide-enter-from {
        opacity: 0;
        transform: translateY(-10px);
    }

    @media (prefers-reduced-motion: reduce) {
        .product-grid-enter-active,
        .product-grid-leave-active,
        .product-grid-move,
        .fade-slide-enter-active {
            transition: none;
        }
    }
</style>
