<script setup lang="ts">
    import { ref, watch } from 'vue';

    import { Link, router } from '@inertiajs/vue3';

    import debounce from 'lodash/debounce';

    import PromoCodeCard from '@/Components/Admin/Cards/PromoCodeCard.vue';
    import AdminEmptyState from '@/Components/Admin/Shared/AdminEmptyState.vue';
    import AdminPageHeader from '@/Components/Admin/Shared/AdminPageHeader.vue';
    import AdminPagination from '@/Components/Admin/Shared/AdminPagination.vue';
    import AdminLoader from '@/Components/Admin/UI/AdminLoader.vue';
    import AdminSearchInput from '@/Components/Admin/UI/AdminSearchInput.vue';
    import BaseCreateButton from '@/Components/UI/BaseCreateButton.vue';
    import BaseSelect from '@/Components/UI/BaseSelect.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useFlash } from '@/composables/useFlash';
    import type { AdminPromoCode, Paginated } from '@/types';

    defineOptions({ layout: AdminLayout });

    const props = defineProps<{
        promoCodes: Paginated<AdminPromoCode>;
        filters: { search?: string; type?: string; status?: string; sort?: string };
        typeOptions: Array<{ value: string; label: string }>;
        statusOptions: Array<{ value: string; label: string }>;
        sortOptions: Array<{ value: string; label: string }>;
    }>();

    const search = ref(props.filters.search || '');
    const typeFilter = ref(props.filters.type || '');
    const statusFilter = ref(props.filters.status || '');
    const sortOrder = ref(props.filters.sort || 'latest');

    const isFiltering = ref(false);
    const deletingIds = ref(new Set<number>());

    const { notifyWithUndo } = useFlash();

    const updateFilters = debounce(() => {
        router.get(
            route('admin.promocodes.index'),
            {
                search: search.value,
                type: typeFilter.value,
                status: statusFilter.value,
                sort: sortOrder.value,
            },
            {
                preserveState: true,
                replace: true,
                onFinish: () => {
                    isFiltering.value = false;
                },
            },
        );
    }, 300);

    watch([search, typeFilter, statusFilter, sortOrder], () => {
        isFiltering.value = true;

        updateFilters();
    });

    const clearFilters = () => {
        search.value = '';
        typeFilter.value = '';
        statusFilter.value = '';
    };

    const goToCreate = () => {
        router.get(route('admin.promocodes.create'));
    };

    const togglePromo = (promo: AdminPromoCode) => {
        router.patch(route('admin.promocodes.toggle', promo.id));
    };

    const deletePromo = async (promo: AdminPromoCode) => {
        if (deletingIds.value.has(promo.id)) return;
        deletingIds.value.add(promo.id);

        const isTimeOut = await notifyWithUndo(`Удаление промокода «${promo.code}»`);

        if (isTimeOut) {
            router.delete(route('admin.promocodes.destroy', promo.id), {
                preserveScroll: true,
                onFinish: () => {
                    deletingIds.value.delete(promo.id);
                },
            });
        } else {
            deletingIds.value.delete(promo.id);
        }
    };
</script>

<template>
    <Teleport to="#admin-header-content">
        <AdminPageHeader title="Модерация промокодов" subtitle="Управление Промокодами магазина" />
    </Teleport>

    <div class="animate-in fade-in space-y-8 duration-500">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <BaseCreateButton :href="route('admin.promocodes.create')" label="Создать код" />

            <div class="grid flex-1 grid-cols-1 gap-4 sm:grid-cols-2 lg:flex lg:items-center">
                <AdminSearchInput v-model="search" placeholder="Поиск по коду..." />

                <BaseSelect
                    v-model="typeFilter"
                    :options="typeOptions"
                    placeholder="Все типы"
                    valueKey="value"
                    labelKey="label"
                    variant="admin"
                    class="lg:w-64"
                />

                <BaseSelect
                    v-model="statusFilter"
                    :options="statusOptions"
                    placeholder="Все статусы"
                    valueKey="value"
                    labelKey="label"
                    variant="admin"
                    class="w-full lg:w-56"
                />

                <BaseSelect
                    v-model="sortOrder"
                    :options="sortOptions"
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
                        :disabled="deletingIds.has(promo.id)"
                        :return-page="promoCodes.meta.current_page"
                        @toggle="togglePromo"
                        @delete="deletePromo"
                /></TransitionGroup>
            </div>

            <AdminLoader v-else-if="isFiltering" key="loading" text="Синхронизация" />

            <AdminEmptyState
                v-else
                key="empty"
                :title="search ? 'Промокоды не найдены' : 'Список промокодов пуст'"
                @action="search ? clearFilters() : goToCreate()"
                :action-text="search ? 'Очистить фильтр' : 'Добавить промокод'"
                :show-action="true"
                :description="
                    search
                        ? 'По запросу «' + search + '» совпадений нет'
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
