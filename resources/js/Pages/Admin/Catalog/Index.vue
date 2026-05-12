<script setup lang="ts">
    import { reactive, ref, watch } from 'vue';

    import { router } from '@inertiajs/vue3';

    import { debounce } from 'lodash';

    import AdminCatalogRow from '@/Components/Admin/Cards/AdminCatalogRow.vue';
    import AdminEmptyState from '@/Components/Admin/Shared/AdminEmptyState.vue';
    import AdminPagination from '@/Components/Admin/Shared/AdminPagination.vue';
    import AdminLoader from '@/Components/Admin/UI/AdminLoader.vue';
    import AdminSearchInput from '@/Components/Admin/UI/AdminSearchInput.vue';
    import BaseCreateButton from '@/Components/UI/BaseCreateButton.vue';
    import BaseSelect from '@/Components/UI/BaseSelect.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useFlash } from '@/composables/useFlash';
    import type { AdminProductVariantDTO, Paginated, QuickUpdatePayload } from '@/types';

    defineOptions({ layout: AdminLayout });

    const props = defineProps<{
        variants: Paginated<AdminProductVariantDTO>;
        products: { id: number; name: string }[];
        units: { id: number; name: string }[];
        sortOptions: { label: string; value: string }[];
        filters: {
            search?: string;
            product_id?: string;
            unit_id?: string;
            sort?: string;
        };
    }>();

    const filterForm = reactive({
        search: props.filters.search || '',
        product_id: props.filters.product_id ? Number(props.filters.product_id) : null,
        unit_id: props.filters.unit_id ? Number(props.filters.unit_id) : null,
        sort: props.filters.sort || 'default',
    });

    const { notifyWithUndo, notify } = useFlash();
    const processingIds = reactive<Set<number>>(new Set());
    const isFiltering = ref(false);

    const quickUpdate = (id: number, data: QuickUpdatePayload) => {
        processingIds.add(id);

        router.patch(route('admin.catalog.quick', id), data as any, {
            preserveScroll: true,
            preserveState: true,
            onError: (e) => {
                if (e.price) {
                    notify(e.price, 'error');
                } else if (e.stock) {
                    notify(e.stock, 'error');
                } else if (e.is_default) {
                    notify(e.is_default, 'error');
                }
            },
            onFinish: () => processingIds.delete(id),
        });
    };

    const deleteVariant = async (variant: AdminProductVariantDTO) => {
        if (processingIds.has(variant.id)) return;
        processingIds.add(variant.id);

        const canDelete = await notifyWithUndo(
            'Удаление варианта «' + variant.name + '» для «' + variant.product?.name + '»',
        );

        if (canDelete) {
            router.delete(route('admin.catalog.destroy', variant.id), {
                preserveScroll: true,
                onFinish: () => processingIds.delete(variant.id),
            });
        } else {
            processingIds.delete(variant.id);
        }
    };

    const updateFilters = debounce(() => {
        const params = Object.fromEntries(
            Object.entries(filterForm).filter(([_, v]) => v !== null && v !== ''),
        );

        router.get(route('admin.catalog.index'), params, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onStart: () => (isFiltering.value = true),
            onFinish: () => (isFiltering.value = false),
        });
    }, 400);

    watch(filterForm, () => {
        isFiltering.value = true;

        updateFilters();
    });

    const clearFilters = () => {
        filterForm.search = '';
        filterForm.product_id = null;
        filterForm.unit_id = null;
        filterForm.sort = 'newest';
    };

    const goToCreate = () => {
        router.get(route('admin.catalog.create'));
    };
</script>

<template>
    <div>
        <Teleport to="#admin-header-content">
            <h1 class="text-xl font-black uppercase tracking-widest text-white">Прайс-лист</h1>
        </Teleport>

        <div class="space-y-8">
            <div class="flex items-center justify-between gap-4">
                <AdminSearchInput
                    v-model="filterForm.search"
                    placeholder="Поиск по названию или ID..."
                />

                <BaseCreateButton
                    :href="
                        route('admin.catalog.create', {
                            page: variants.meta.current_page || 1,
                        })
                    "
                    label="Добавить вариант"
                    class="!px-3"
                />
            </div>
            <div class="l flex flex-wrap gap-3 lg:col-span-8">
                <BaseSelect
                    v-model="filterForm.product_id"
                    :options="products"
                    placeholder="Все товары"
                    valueKey="id"
                    labelKey="name"
                    variant="admin"
                    class="w-full lg:w-48"
                />

                <BaseSelect
                    v-model="filterForm.unit_id"
                    :options="units"
                    placeholder="Все измерения"
                    valueKey="id"
                    labelKey="name"
                    variant="admin"
                    class="w-full lg:w-32"
                />

                <BaseSelect
                    v-model="filterForm.sort"
                    :options="sortOptions"
                    valueKey="value"
                    labelKey="label"
                    variant="admin"
                    class="w-full lg:w-48"
                />
            </div>

            <div
                class="hidden grid-cols-12 gap-4 px-8 text-[9px] font-black uppercase tracking-[0.3em] text-slate-600 lg:grid"
            >
                <div class="col-span-5">Товар / Характеристики</div>
                <div class="col-span-2 text-center">Цена за ед.</div>
                <div class="col-span-2 text-center">Складской запас</div>
                <div class="col-span-2 text-center">По умолчанию</div>
                <div class="col-span-1"></div>
            </div>

            <Transition name="fade-slide" mode="out-in">
                <div v-if="variants.data.length" key="variants" class="relative min-h-[400px]">
                    <TransitionGroup tag="div" name="catalog-list" class="space-y-3">
                        <AdminCatalogRow
                            v-for="variant in variants.data"
                            :key="variant.id"
                            :variant="variant"
                            :disabled="processingIds.has(variant.id)"
                            :out-of-stock="!variant.is_in_stock"
                            :current-page="variants.meta.current_page"
                            @quick-update="quickUpdate"
                            @delete="deleteVariant"
                        />
                    </TransitionGroup>
                </div>

                <AdminLoader v-else-if="isFiltering" key="loading" text="Синхронизация" />

                <AdminEmptyState
                    v-else
                    key="empty"
                    :title="filters.search ? 'Варианты не найдены' : 'Список вариантов пуст'"
                    @action="filters.search ? clearFilters() : goToCreate()"
                    :action-text="filters.search ? 'Очистить фильтр' : 'Добавить вариант товара'"
                    :show-action="true"
                    :description="
                        filters.search
                            ? 'По запросу «' + filters.search + '» совпадений нет'
                            : 'Нет ни одного варианта товаров'
                    "
                />
            </Transition>

            <AdminPagination :links="variants.meta.links" />
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
</style>
