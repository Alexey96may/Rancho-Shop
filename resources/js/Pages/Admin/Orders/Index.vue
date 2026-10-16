<script setup lang="ts">
    import { type PropType, computed, watch } from 'vue';

    import { useForm } from '@inertiajs/vue3';

    import { ClockIcon, CurrencyDollarIcon, ShoppingBagIcon } from '@heroicons/vue/24/outline';

    import AdminOrderCard from '@/Components/Admin/Cards/AdminOrderCard.vue';
    import AdminEmptyState from '@/Components/Admin/Shared/AdminEmptyState.vue';
    import AdminPageHeader from '@/Components/Admin/Shared/AdminPageHeader.vue';
    import AdminLoader from '@/Components/Admin/UI/AdminLoader.vue';
    import AdminSearchInput from '@/Components/Admin/UI/AdminSearchInput.vue';
    import AdminPagination from '@/Components/Shared/MainPagination.vue';
    import BaseSelect from '@/Components/UI/BaseSelect.vue';
    import StatCard from '@/Components/UI/StatCard.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useAdminFilters } from '@/composables/routing/useAdminFilters';
    import { useAdminNavigation } from '@/composables/routing/useAdminNavigation';
    import type { AdminOrder, Paginated } from '@/types';
    import { formatMoney } from '@/utils/format';

    defineOptions({ layout: AdminLayout });

    const props = defineProps({
        orders: {
            type: Object as PropType<Paginated<AdminOrder>>,
            required: true,
            validator: (value: unknown): boolean => {
                const val = value as Record<string, unknown>;
                const hasData = Array.isArray(val?.data);
                const hasMeta = val?.meta && typeof val.meta === 'object';

                if (!hasData || !hasMeta) {
                    console.warn(
                        'Runtime Error: The "orders" prop must match the Paginated structure.',
                    );
                }
                return !!(hasData && hasMeta);
            },
        },
        filters: {
            type: Object as PropType<{ search?: string; status?: string }>,
            required: true,
            default: () => ({ search: '', status: '' }),
        },
        total_completed_revenue: {
            type: Number,
            required: false,
            default: 0,
        },
        total_pending_revenue: {
            type: Number,
            required: false,
            default: 0,
        },
        total_count: {
            type: Number,
            required: false,
            default: 0,
        },
    });

    const filterForm = useForm({
        search: props.filters.search || '',
        status: props.filters.status || '',
    });

    const { navigateWithContext } = useAdminNavigation();
    const { isFiltering, submitFilters, clearFilters } = useAdminFilters();

    const computedCompletedRevenue = computed(() =>
        formatMoney(props.total_completed_revenue || 0),
    );

    const computedPendingRevenue = computed(() => formatMoney(props.total_pending_revenue || 0));

    watch(
        () => [filterForm.search, filterForm.status],
        () => {
            submitFilters(filterForm, 'admin.orders.index');
        },
    );
</script>

<template>
    <Teleport to="#admin-header-content">
        <AdminPageHeader title="Список Заказов" subtitle="Управление продажами" />
    </Teleport>

    <!-- STATS -->
    <div class="mb-6 grid grid-cols-1 gap-3 sm:mb-8 sm:grid-cols-3 sm:gap-4 md:gap-6">
        <StatCard
            label="Выручка (завершено)"
            :value="computedCompletedRevenue"
            :icon="CurrencyDollarIcon"
            label-color="text-rancho-mint"
        />

        <StatCard
            label="В обработке"
            :value="computedPendingRevenue"
            :icon="ClockIcon"
            :label-color="total_pending_revenue ? 'text-rancho-buttercup' : 'text-rancho-ink-500'"
        />

        <StatCard
            label="Заказов всего"
            :icon="ShoppingBagIcon"
            :value="total_count || 0"
            label-color="text-rancho-sky"
        />
    </div>

    <!-- FILTERS -->
    <div class="mb-6 space-y-4 sm:mb-8">
        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-end sm:gap-4"
            role="search"
            aria-label="Фильтрация"
        >
            <AdminSearchInput
                v-model="filterForm.search"
                placeholder="Поиск по имени или ID"
                @input-start="filterForm.clearErrors('search')"
                label="Поиск заказов"
                class="w-full sm:max-w-md"
            />

            <BaseSelect
                v-model="filterForm.status"
                :options="[
                    { name: 'new', label: 'Новый' },
                    { name: 'confirmed', label: 'Подтверждён' },
                    { name: 'delivering', label: 'В доставке' },
                    { name: 'completed', label: 'Завершён' },
                    { name: 'cancelled', label: 'Отменён' },
                ]"
                value-key="name"
                label-key="label"
                label="Статус"
                @input-start="filterForm.clearErrors('status')"
                placeholder="Все статусы"
                variant="admin"
                class="w-full sm:w-56 lg:w-64"
            />
        </div>
    </div>

    <!-- LIST -->
    <main aria-label="Список заказов">
        <Transition name="fade-slide" mode="out-in">
            <div
                v-if="orders.data.length"
                key="orders"
                class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-6 lg:grid-cols-3 xl:grid-cols-4"
            >
                <TransitionGroup name="order-list">
                    <AdminOrderCard
                        v-for="order in orders.data"
                        :key="order.id"
                        v-memo="[order.id, order.updated_at]"
                        :order="order"
                        @click="navigateWithContext('admin.orders', 'show', order.id)"
                        class="cursor-pointer"
                    />
                </TransitionGroup>
            </div>

            <AdminLoader v-else-if="isFiltering" key="loading" text="Синхронизация" />

            <AdminEmptyState
                v-else
                key="empty"
                title="Заказы не найдены"
                @action="clearFilters(filterForm)"
                :show-action="!!(filterForm.search || filterForm.status)"
            />
        </Transition>

        <Transition name="fade-slide" mode="out-in">
            <AdminPagination v-if="!isFiltering" :links="orders.meta.links" theme="dark" />
        </Transition>
    </main>
</template>

<style scoped>
    .order-list-enter-active,
    .order-list-leave-active {
        transition: all 0.5s ease;
    }
    .order-list-enter-from,
    .order-list-leave-to {
        opacity: 0;
        transform: translateY(20px);
    }
    .order-list-move {
        transition: transform 0.5s ease;
    }
    .order-list-leave-active {
        position: absolute;
    }

    .fade-slide-enter-active,
    .fade-slide-leave-active {
        transition: all 0.4s ease-out;
    }
    .fade-slide-enter-from {
        opacity: 0;
        transform: scale(0.95);
    }
    .fade-slide-leave-to {
        opacity: 0;
        transform: scale(1.05);
    }

    @media (prefers-reduced-motion: reduce) {
        .order-list-enter-active,
        .order-list-leave-active,
        .order-list-move,
        .fade-slide-enter-active,
        .fade-slide-leave-active {
            transition: none;
        }
    }
</style>
