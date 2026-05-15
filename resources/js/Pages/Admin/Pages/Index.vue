<script setup lang="ts">
    import { ref, watch } from 'vue';

    import { router } from '@inertiajs/vue3';

    import debounce from 'lodash/debounce';

    import AdminPageCard from '@/Components/Admin/Cards/AdminPageCard.vue';
    import AdminEmptyState from '@/Components/Admin/Shared/AdminEmptyState.vue';
    import AdminPageHeader from '@/Components/Admin/Shared/AdminPageHeader.vue';
    import AdminPagination from '@/Components/Admin/Shared/AdminPagination.vue';
    import AdminLoader from '@/Components/Admin/UI/AdminLoader.vue';
    import AdminSearchInput from '@/Components/Admin/UI/AdminSearchInput.vue';
    import BaseCreateButton from '@/Components/UI/BaseCreateButton.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useFlash } from '@/composables/useFlash';
    import { useNavigation } from '@/composables/useNavigation';
    import { AdminPage, Paginated } from '@/types';

    defineOptions({ layout: AdminLayout });

    const props = defineProps<{
        pages: Paginated<AdminPage>;
        filters: { search: string };
    }>();

    const search = ref(props.filters.search || '');
    const isFiltering = ref(false);

    const { currentQuery } = useNavigation();

    const { notifyWithUndo } = useFlash();

    const debouncedFilter = debounce(() => {
        router.get(
            route('admin.pages.index'),
            { search: search.value.toLowerCase() },
            { preserveState: true, replace: true },
        );
    }, 300);

    watch(search, debouncedFilter);

    const deletingIds = ref(new Set<number>());

    const deletePage = async (page: AdminPage) => {
        if (deletingIds.value.has(page.id)) return;
        if (!page.can_delete) return;

        deletingIds.value.add(page.id);

        const isDeleted = await notifyWithUndo(`Удаление страницы "${page.title}"`);

        if (isDeleted) {
            router.delete(route('admin.pages.destroy', page.id), {
                preserveScroll: true,
                preserveState: true,
                onBefore: () => {},
                onFinish: () => {
                    deletingIds.value.delete(page.id);
                },
            });
        } else {
            deletingIds.value.delete(page.id);
        }
    };

    const clearFilters = () => {
        search.value = '';
    };
</script>

<template>
    <Teleport to="#admin-header-content">
        <AdminPageHeader title="Модерация страниц" subtitle="Управление страницами сайта (CMS)" />
    </Teleport>

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <AdminSearchInput v-model="search" placeholder="Поиск страниц..." />

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
                        :is-deleting="deletingIds.has(page.id)"
                        @delete="deletePage"
                    />
                </TransitionGroup>
            </div>

            <AdminLoader v-else-if="isFiltering" key="loading" text="Синхронизация" />

            <AdminEmptyState
                v-else
                key="empty"
                title="Страницы не найдены"
                @action="clearFilters"
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
