<script setup lang="ts">
    import { reactive, watch } from 'vue';

    import { router } from '@inertiajs/vue3';

    import debounce from 'lodash/debounce';

    import AdminOrderCard from '@/Components/Admin/Cards/AdminOrderCard.vue';
    import AdminEmptyState from '@/Components/Admin/Shared/AdminEmptyState.vue';
    import AdminPageHeader from '@/Components/Admin/Shared/AdminPageHeader.vue';
    import AdminPagination from '@/Components/Admin/Shared/AdminPagination.vue';
    import AdminSearchInput from '@/Components/Admin/UI/AdminSearchInput.vue';
    import BaseSelect from '@/Components/UI/BaseSelect.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import type { AdminOrder, Paginated } from '@/types';
    import { formatMoney } from '@/utils/format';

    defineOptions({ layout: AdminLayout });

    const props = defineProps<{
        orders: Paginated<AdminOrder>;
        filters: { search?: string; status?: string };
        total_completed_revenue?: number;
        total_pending_revenue?: number;
        total_count?: number;
    }>();

    const form = reactive({
        search: props.filters.search || '',
        status: props.filters.status || '',
    });

    watch(
        () => form,
        debounce((newForm) => {
            router.get(route('admin.orders.index'), newForm, {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            });
        }, 300),
        { deep: true },
    );

    const clearFilters = () => {
        form.search = '';
        form.status = '';
    };

    const openOrder = (orderId: number) => {
        if (!window) return;

        const searchParams = window.location.search;

        router.visit(route('admin.orders.show', orderId), {
            data: { back: searchParams },
        });
    };
</script>

<template>
    <AdminPageHeader
        title-normal="Список"
        title-orange="Заказов"
        subtitle="Оперативное управление продажами"
    />

    <div class="mb-8 space-y-6">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center"
            role="search"
            aria-label="Фильтрация"
        >
            <AdminSearchInput
                v-model="form.search"
                placeholder="Поиск по имени или ID"
                label="Поиск заказов"
            />
            <BaseSelect
                v-model="form.status"
                :options="[
                    { name: 'new', label: 'Новый' },
                    { name: 'confirmed', label: 'Подтвержден' },
                    { name: 'delivering', label: 'В доставке' },
                    { name: 'completed', label: 'Завершен' },
                    { name: 'cancelled', label: 'Отменен' },
                ]"
                valueKey="name"
                labelKey="label"
                placeholder="Все статусы"
                variant="admin"
                class="lg:w-64"
            />
            <button
                v-if="form.search || form.status"
                @click="clearFilters"
                class="text-xs font-bold text-orange-500 hover:text-orange-400"
            >
                Сбросить
            </button>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="rounded-3xl border border-slate-800 bg-slate-900/50 p-5">
                <p class="text-[10px] font-black uppercase tracking-widest text-green-500">
                    Выручка (завершено)
                </p>
                <p class="mt-2 text-2xl font-black text-white">
                    {{ formatMoney(total_completed_revenue || 0) }}
                </p>
            </div>
            <div class="rounded-3xl border border-slate-800 bg-slate-900/50 p-5">
                <p class="text-[10px] font-black uppercase tracking-widest text-orange-500">
                    В обработке
                </p>
                <p class="mt-2 text-2xl font-black text-white">
                    {{ formatMoney(total_pending_revenue || 0) }}
                </p>
            </div>
            <div class="rounded-3xl border border-slate-800 bg-slate-900/50 p-5">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-500">
                    Заказов всего
                </p>
                <p class="mt-2 text-2xl font-black text-white">{{ total_count || 0 }}</p>
            </div>
        </div>
    </div>

    <main aria-label="Список заказов">
        <TransitionGroup
            v-if="orders.data.length"
            tag="div"
            name="order-list"
            class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
        >
            <AdminOrderCard
                v-for="order in orders.data"
                :key="order.id"
                :order="order"
                @click="openOrder(order.id)"
                class="cursor-pointer"
            />
        </TransitionGroup>
        <Transition v-else name="fade-slide" mode="out-in">
            <AdminEmptyState
                title="Заказы не найдены"
                @action="clearFilters"
                :show-action="!!(form.search || form.status)"
            />
        </Transition>

        <AdminPagination :links="orders.meta.links" />
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
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #1e293b;
        border-radius: 10px;
    }
</style>
