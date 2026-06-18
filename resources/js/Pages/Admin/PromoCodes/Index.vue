<script setup lang="ts">
    import { PropType, watch } from 'vue';

    import { router, useForm } from '@inertiajs/vue3';

    import PromoCodeCard from '@/Components/Admin/Cards/PromoCodeCard.vue';
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
    import type { AdminPromoCode, Paginated } from '@/types';

    defineOptions({ layout: AdminLayout });

    const props = defineProps({
        promoCodes: {
            type: Object as PropType<Paginated<AdminPromoCode>>,
            required: true,
            validator: (value: unknown): boolean => {
                const val = value as Record<string, unknown>;
                const hasData = Array.isArray(val?.data);
                const hasMeta = val?.meta && typeof val.meta === 'object';

                if (!hasData || !hasMeta) {
                    console.warn(
                        'Runtime Error: The "promoCodes" prop must match the Laravel Paginated structure.',
                    );
                }
                return !!(hasData && hasMeta);
            },
        },
        filters: {
            type: Object as PropType<{
                search?: string;
                type?: string;
                status?: string;
                sort?: string;
            }>,
            required: true,
            default: () => ({ search: '', type: '', status: '', sort: '' }),
        },
        typeOptions: {
            type: Array as PropType<Array<{ value: string; label: string }>>,
            required: true,
            default: () => [],
        },
        statusOptions: {
            type: Array as PropType<Array<{ value: string; label: string }>>,
            required: true,
            default: () => [],
        },
        sortOptions: {
            type: Array as PropType<Array<{ value: string; label: string }>>,
            required: true,
            default: () => [],
        },
    });

    const filterForm = useForm({
        search: props.filters.search || '',
        type: props.filters.type || '',
        status: props.filters.status || '',
        sort: props.filters.sort || '',
    });

    const { navigateWithContext } = useAdminNavigation();
    const { deleteEntity, isDeleting } = useAdminCrud();
    const { isFiltering, submitFilters, clearFilters } = useAdminFilters();

    watch(
        () => [filterForm.search, filterForm.type, filterForm.status, filterForm.sort],
        () => {
            submitFilters(filterForm, 'admin.promocodes.index');
        },
    );

    const togglePromo = (promo: AdminPromoCode) => {
        router.patch(route('admin.promocodes.toggle', promo.id));
    };
</script>

<template>
    <Teleport to="#admin-header-content">
        <AdminPageHeader title="Модерация промокодов" subtitle="Управление Промокодами магазина" />
    </Teleport>

    <div class="animate-in fade-in space-y-8 duration-500">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <BaseCreateButton
                label="Создать код"
                @click="navigateWithContext('admin.promocodes', 'create')"
            />

            <div class="grid flex-1 grid-cols-1 gap-4 sm:grid-cols-2 lg:flex lg:items-center">
                <AdminSearchInput v-model="filterForm.search" placeholder="Поиск по коду..." />

                <BaseSelect
                    v-model="filterForm.type"
                    :options="typeOptions"
                    placeholder="Все типы"
                    valueKey="value"
                    labelKey="label"
                    variant="admin"
                    class="lg:w-64"
                />

                <BaseSelect
                    v-model="filterForm.status"
                    :options="statusOptions"
                    placeholder="Все статусы"
                    valueKey="value"
                    labelKey="label"
                    variant="admin"
                    class="w-full lg:w-56"
                />

                <BaseSelect
                    v-model="filterForm.sort"
                    :options="sortOptions"
                    placeholder="По умолчанию"
                    valueKey="value"
                    labelKey="label"
                    variant="admin"
                    class="w-full lg:w-56"
                />
            </div>
        </div>

        <Transition name="fade-slide" mode="out-in">
            <div
                v-if="promoCodes.data.length"
                key="promos"
                class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3"
            >
                <TransitionGroup name="stagger">
                    <PromoCodeCard
                        v-for="promo in promoCodes.data"
                        :key="promo.id"
                        :promo="promo"
                        :disabled="isDeleting(promo.id)"
                        @edit="navigateWithContext('admin.promocodes', 'edit', promo.id)"
                        @toggle="togglePromo"
                        @delete="
                            deleteEntity(
                                'admin.promocodes',
                                promo.id,
                                `Удаление промокода «${promo.code}»`,
                            )
                        "
                /></TransitionGroup>
            </div>

            <AdminLoader v-else-if="isFiltering" key="loading" text="Синхронизация" />

            <AdminEmptyState
                v-else
                key="empty"
                :title="filterForm.search ? 'Промокоды не найдены' : 'Список промокодов пуст'"
                @action="
                    filterForm.search
                        ? clearFilters(filterForm)
                        : navigateWithContext('admin.promocodes', 'create')
                "
                :action-text="filterForm.search ? 'Очистить фильтр' : 'Добавить промокод'"
                :show-action="true"
                :description="
                    filterForm.search
                        ? 'По запросу «' + filterForm.search + '» совпадений нет'
                        : 'Нет ни одного промокода'
                "
            />
        </Transition>

        <Transition name="fade-slide" mode="out-in">
            <AdminPagination v-show="!isFiltering" :links="promoCodes.meta.links" />
        </Transition>
    </div>
</template>

<style scoped>
    .stagger-enter-active {
        transition: all 0.5s cubic-bezier(0.25, 1, 0.5, 1);
        transition-delay: calc(var(--i) * 0.05s);
    }

    .stagger-leave-active {
        transition: all 0.3s ease;
        position: absolute;
        width: 100%;
    }

    .stagger-enter-from {
        opacity: 0;
        transform: translateY(30px) scale(0.95);
    }

    .stagger-leave-to {
        opacity: 0;
        transform: scale(0.9);
    }

    .stagger-move {
        transition: transform 0.4s ease;
    }

    .fade-slide-enter-active {
        transition: all 0.4s ease-out;
    }

    .fade-slide-enter-from {
        opacity: 0;
        transform: translateY(-10px);
    }
</style>
