<script setup lang="ts">
    import { type PropType, watch } from 'vue';

    import { router, useForm } from '@inertiajs/vue3';

    import AdminCatalogRow from '@/Components/Admin/Cards/AdminCatalogRow.vue';
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
    import { useNotificationsStore } from '@/stores/notifications';
    import type { AdminProductVariantDTO, Paginated, QuickUpdatePayload } from '@/types';

    defineOptions({ layout: AdminLayout });

    const props = defineProps({
        variants: {
            type: Object as PropType<Paginated<AdminProductVariantDTO>>,
            required: true,
            validator: (value: unknown): boolean => {
                const val = value as Record<string, unknown>;
                const hasData = Array.isArray(val?.data);
                const hasMeta = val?.meta && typeof val.meta === 'object';

                if (!hasData || !hasMeta) {
                    console.warn(
                        'Runtime Error: The "variants" prop is missing a valid pagination structure (data or meta).',
                    );
                }
                return !!(hasData && hasMeta);
            },
        },
        products: {
            type: Array as PropType<{ id: number; name: string }[]>,
            required: true,
            validator: (value: unknown): boolean => {
                if (!Array.isArray(value)) return false;
                return value.every((item: unknown) => {
                    const product = item as Record<string, unknown>;
                    return typeof product?.id === 'number' && typeof product?.name === 'string';
                });
            },
        },
        units: {
            type: Array as PropType<{ id: number; name: string }[]>,
            required: true,
            validator: (value: unknown): boolean => {
                if (!Array.isArray(value)) return false;
                return value.every((item: unknown) => {
                    const unit = item as Record<string, unknown>;
                    return typeof unit?.id === 'number' && typeof unit?.name === 'string';
                });
            },
        },
        sortOptions: {
            type: Array as PropType<{ label: string; value: string }[]>,
            required: true,
            validator: (value: unknown): boolean => {
                if (!Array.isArray(value)) return false;
                return value.every((item: unknown) => {
                    const option = item as Record<string, unknown>;
                    return typeof option?.label === 'string' && typeof option?.value === 'string';
                });
            },
        },
        filters: {
            type: Object as PropType<{
                search?: string;
                product_id?: string;
                unit_id?: string;
                sort?: string;
            }>,
            required: true,
            default: () => ({ search: '', product_id: '', unit_id: '', sort: '' }),
        },
    });

    const filterForm = useForm({
        search: props.filters.search || '',
        product_id: props.filters.product_id ? Number(props.filters.product_id) : null,
        unit_id: props.filters.unit_id ? Number(props.filters.unit_id) : null,
        sort: props.filters.sort || '',
    });

    const notify = useNotificationsStore();
    const { navigateWithContext } = useAdminNavigation();
    const { deleteEntity, isDeleting } = useAdminCrud();
    const { isFiltering, submitFilters, clearFilters } = useAdminFilters();

    const quickUpdate = (id: number, data: QuickUpdatePayload) => {
        router.patch(route('admin.catalog.quick', id), data, {
            preserveScroll: true,
            preserveState: true,
            onError: (e) => {
                if (e.price) notify.error(e.price);
                else if (e.stock) notify.error(e.stock);
                else if (e.is_default) notify.error(e.is_default);
            },
        });
    };

    watch(
        () => [filterForm.search, filterForm.product_id, filterForm.unit_id, filterForm.sort],
        () => {
            submitFilters(filterForm, 'admin.catalog.index');
        },
    );
</script>

<template>
    <div>
        <Teleport to="#admin-header-content">
            <AdminPageHeader
                title="Единицы складского учёта"
                subtitle="*Один вариант продукта будет главным всегда!"
            />
        </Teleport>

        <div class="space-y-10">
            <!-- SEARCH + ADD -->
            <div
                class="flex flex-col-reverse gap-3 sm:flex-row sm:items-end sm:justify-between sm:gap-4"
            >
                <AdminSearchInput
                    v-model="filterForm.search"
                    placeholder="Поиск по названию или ID..."
                    class="sm:max-w-md"
                />
                <BaseCreateButton
                    @click="navigateWithContext('admin.catalog', 'create')"
                    label="Добавить вариант"
                    class="self-end sm:self-auto"
                />
            </div>

            <!-- FILTERS -->
            <div class="flex flex-wrap gap-3">
                <BaseSelect
                    v-model="filterForm.product_id"
                    :options="products"
                    placeholder="Все товары"
                    label="Товар"
                    value-key="id"
                    label-key="name"
                    variant="admin"
                    class="w-full sm:w-56 lg:w-64"
                />

                <BaseSelect
                    v-model="filterForm.unit_id"
                    :options="units"
                    placeholder="Все измерения"
                    label="Измерение"
                    value-key="id"
                    label-key="name"
                    variant="admin"
                    class="w-full sm:w-44 lg:w-48"
                />

                <BaseSelect
                    v-model="filterForm.sort"
                    :options="sortOptions"
                    value-key="value"
                    label-key="label"
                    placeholder="По умолчанию"
                    label="Сортировка"
                    variant="admin"
                    class="w-full sm:w-56 lg:w-64"
                />
            </div>

            <!-- TABLE HEADER (desktop only) -->
            <div
                class="border-rancho-ink-800 text-rancho-ink-500 hidden grid-cols-12 gap-4 border-b px-6 pb-2 text-[9px] font-black uppercase tracking-[0.3em] lg:grid"
            >
                <div class="col-span-4">Товар / Характеристики</div>
                <div class="col-span-2 text-center">Цена за ед.</div>
                <div class="col-span-2 text-center">Складской запас</div>
                <div class="col-span-2 text-center">По умолчанию</div>
                <div class="col-span-1"></div>
            </div>

            <!-- LIST / LOADER / EMPTY -->
            <Transition name="fade-slide" mode="out-in">
                <div v-if="variants.data.length" key="variants" class="relative min-h-[400px]">
                    <TransitionGroup tag="div" name="catalog-list" class="space-y-3">
                        <AdminCatalogRow
                            v-for="variant in variants.data"
                            :key="variant.id"
                            :variant="variant"
                            :disabled="isDeleting(variant.id)"
                            :out-of-stock="!variant.is_in_stock"
                            :current-page="variants.meta.current_page"
                            @edit="navigateWithContext('admin.catalog', 'edit', variant.id)"
                            @quick-update="quickUpdate"
                            @delete="
                                deleteEntity(
                                    'admin.catalog',
                                    variant.id,
                                    'Удаление варианта «' +
                                        variant.name +
                                        '» для «' +
                                        variant.product?.name +
                                        '»',
                                )
                            "
                        />
                    </TransitionGroup>
                </div>

                <AdminLoader v-else-if="isFiltering" key="loading" text="Синхронизация" />

                <AdminEmptyState
                    v-else
                    key="empty"
                    :title="filters.search ? 'Варианты не найдены' : 'Список вариантов пуст'"
                    @action="
                        filters.search
                            ? clearFilters(filterForm)
                            : navigateWithContext('admin.catalog', 'create')
                    "
                    :action-text="filters.search ? 'Очистить фильтр' : 'Добавить вариант товара'"
                    :show-action="true"
                    :description="
                        filters.search
                            ? 'По запросу «' + filters.search + '» совпадений нет'
                            : 'Нет ни одного варианта товаров'
                    "
                />
            </Transition>

            <!-- PAGINATION -->
            <Transition name="fade-slide" mode="out-in">
                <AdminPagination v-if="!isFiltering" :links="variants.meta.links" theme="dark" />
            </Transition>
        </div>
    </div>
</template>

<style scoped>
    .fade-slide-enter-active {
        transition: all 0.4s ease-out;
    }

    .fade-slide-enter-from {
        opacity: 0;
        transform: translateY(-10px);
    }

    @media (prefers-reduced-motion: reduce) {
        .fade-slide-enter-active {
            transition: none;
        }
    }
</style>
