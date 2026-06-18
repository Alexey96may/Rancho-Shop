<script setup lang="ts">
    import { PropType, watch } from 'vue';

    import { useForm } from '@inertiajs/vue3';

    import AdminPageCard from '@/Components/Admin/Cards/AdminPageCard.vue';
    import AdminEmptyState from '@/Components/Admin/Shared/AdminEmptyState.vue';
    import AdminPageHeader from '@/Components/Admin/Shared/AdminPageHeader.vue';
    import AdminPagination from '@/Components/Admin/Shared/AdminPagination.vue';
    import AdminLoader from '@/Components/Admin/UI/AdminLoader.vue';
    import AdminSearchInput from '@/Components/Admin/UI/AdminSearchInput.vue';
    import BaseCreateButton from '@/Components/UI/BaseCreateButton.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useAdminCrud } from '@/composables/crud/useAdminCrud';
    import { useNavigation } from '@/composables/features/useNavigation';
    import { useAdminFilters } from '@/composables/routing/useAdminFilters';
    import { useAdminNavigation } from '@/composables/routing/useAdminNavigation';
    import { AdminPage, Paginated } from '@/types';

    defineOptions({ layout: AdminLayout });

    const props = defineProps({
        pages: {
            type: Object as PropType<Paginated<AdminPage>>,
            required: true,
            validator: (value: unknown): boolean => {
                const val = value as Record<string, unknown>;
                const hasData = Array.isArray(val?.data);
                const hasMeta = val?.meta && typeof val.meta === 'object';

                if (!hasData || !hasMeta) {
                    console.warn(
                        'Runtime Error: The "pages" prop must match the Paginated structure.',
                    );
                }
                return !!(hasData && hasMeta);
            },
        },
        filters: {
            type: Object as PropType<{ search: string }>,
            required: true,
            default: () => ({ search: '' }),
        },
    });

    const filterForm = useForm({
        search: props.filters.search || '',
    });

    const { currentQuery } = useNavigation();

    const { navigateWithContext } = useAdminNavigation();
    const { deleteEntity, isDeleting } = useAdminCrud();
    const { isFiltering, submitFilters, clearFilters } = useAdminFilters();

    watch(
        () => [filterForm.search],
        () => {
            submitFilters(filterForm, 'admin.pages.index');
        },
    );
</script>

<template>
    <Teleport to="#admin-header-content">
        <AdminPageHeader title="Модерация страниц" subtitle="Управление страницами сайта (CMS)" />
    </Teleport>

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <AdminSearchInput
                v-model="filterForm.search"
                placeholder="Поиск страниц по названию..."
            />

            <BaseCreateButton
                :href="
                    route('admin.pages.create', {
                        back: currentQuery,
                    })
                "
                label="Создать"
            />
        </div>

        <div
            class="hidden grid-cols-12 gap-4 px-8 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 md:grid"
        >
            <div class="col-span-5">Заголовок / Slug</div>
            <div class="col-span-3 text-center">Тип и Шаблон</div>
            <div class="col-span-2 text-center">Статус</div>
            <div class="col-span-2 text-right">Действия</div>
        </div>

        <Transition name="fade-slide" mode="out-in">
            <div class="space-y-3" role="list" v-if="pages.data.length" key="page-list">
                <TransitionGroup name="stagger">
                    <AdminPageCard
                        v-for="(page, index) in pages.data"
                        :key="page.id"
                        :page="page"
                        :index="index"
                        :is-deleting="isDeleting(page.id)"
                        @edit="navigateWithContext('admin.pages', 'edit', page.id)"
                        @delete="
                            deleteEntity(
                                'admin.pages',
                                page.id,
                                `Удаление страницы «${page.title}»`,
                            )
                        "
                    />
                </TransitionGroup>
            </div>

            <AdminLoader v-else-if="isFiltering" key="loading" text="Синхронизация" />

            <AdminEmptyState
                v-else
                key="empty"
                title="Страницы не найдены"
                @action="clearFilters(filterForm)"
                :show-action="true"
            />
        </Transition>

        <Transition name="fade-slide" mode="out-in">
            <footer v-show="pages.data.length > 0 && !isFiltering">
                <AdminPagination :links="pages.meta.links" />
            </footer>
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
