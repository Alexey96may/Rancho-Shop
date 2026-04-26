<script setup lang="ts">
    import { ref, watch } from 'vue';

    import { Link, router } from '@inertiajs/vue3';

    import { DocumentIcon, PencilIcon, PlusIcon, TrashIcon } from '@heroicons/vue/24/outline';
    import debounce from 'lodash/debounce';

    import AdminEmptyState from '@/Components/Admin/Shared/AdminEmptyState.vue';
    import AdminSearchInput from '@/Components/Admin/UI/AdminSearchInput.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useFlash } from '@/composables/useFlash';
    import { AdminPage, Paginated } from '@/types';

    defineOptions({ layout: AdminLayout });

    const props = defineProps<{
        pages: Paginated<AdminPage>;
        filters: { search: string };
    }>();

    const search = ref(props.filters.search || '');

    const { notifyWithUndo } = useFlash();

    const debouncedFilter = debounce(() => {
        router.get(
            route('admin.pages.index'),
            { search: search.value.toLowerCase() },
            { preserveState: true, replace: true },
        );
    }, 300);

    watch(search, debouncedFilter);

    const deletePage = async (page: AdminPage) => {
        const isDeleted = await notifyWithUndo(`Удаление страницы "${page.title}"`);

        if (!page.can_delete) return;

        if (isDeleted) {
            router.delete(route('admin.pages.destroy', page.id));
        }
    };

    const clearFilters = () => {
        search.value = '';
    };
</script>

<template>
    <Teleport to="#admin-header-content">
        <h1 class="flex items-center gap-2 text-xl font-black text-white">Модерация страниц</h1>
    </Teleport>

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <AdminSearchInput v-model="search" placeholder="Поиск страниц..." />

            <Link
                :href="route('admin.pages.create')"
                class="shadow-lg flex items-center gap-2 rounded-2xl bg-orange-600 px-6 py-3 text-xs font-black uppercase tracking-widest text-white shadow-orange-900/20 transition-all hover:bg-orange-500"
            >
                <PlusIcon class="h-5 w-5" /> Создать
            </Link>
        </div>

        <div
            class="hidden grid-cols-12 gap-4 px-8 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 md:grid"
        >
            <div class="col-span-5">Заголовок / Slug</div>
            <div class="col-span-3 text-center">Тип и Шаблон</div>
            <div class="col-span-2 text-center">Статус</div>
            <div class="col-span-2 text-right">Действия</div>
        </div>

        <Transition name="fade-slide">
            <div class="space-y-3" role="list" v-if="pages.data.length" key="page-list">
                <TransitionGroup name="stagger">
                    <div
                        v-for="(page, index) in pages.data"
                        :key="page.id"
                        :style="{ '--i': index }"
                        role="listitem"
                        class="group grid grid-cols-1 items-center gap-4 rounded-[2rem] border border-slate-800 bg-slate-900/30 p-4 transition-all hover:bg-slate-800/40 md:grid-cols-12 md:px-8"
                    >
                        <div class="col-span-5">
                            <div class="flex items-center gap-4">
                                <div
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-800 text-slate-500"
                                >
                                    <DocumentIcon class="h-5 w-5" />
                                </div>
                                <div class="flex min-w-0 flex-col">
                                    <span class="truncate font-bold text-white">{{
                                        page.title
                                    }}</span>
                                    <span class="truncate font-mono text-[10px] text-slate-500">{{
                                        page.slug
                                    }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-span-3 flex flex-col items-center gap-1">
                            <span
                                :class="page.type_color"
                                class="rounded-lg px-3 py-0.5 text-[9px] font-black uppercase"
                            >
                                {{ page.type_label }}
                            </span>
                            <span class="text-[8px] font-black uppercase text-slate-500">{{
                                page.template
                            }}</span>
                        </div>

                        <div class="col-span-2 flex justify-center">
                            <div
                                :aria-label="page.is_active ? 'Активна' : 'Неактивна'"
                                :class="
                                    page.is_active
                                        ? 'bg-emerald-500 shadow-[0_0_10px_#10b981]'
                                        : 'bg-slate-700'
                                "
                                class="h-2.5 w-2.5 rounded-full"
                            ></div>
                        </div>

                        <div class="col-span-2 flex justify-end gap-2">
                            <Link
                                :href="route('admin.pages.edit', page.id)"
                                aria-label="Редактировать"
                                class="p-2 text-slate-500 transition-all hover:text-orange-500"
                            >
                                <PencilIcon class="h-5 w-5" />
                            </Link>
                            <button
                                @click="deletePage(page)"
                                v-if="page.can_delete"
                                aria-label="Удалить"
                                class="p-2 text-slate-500 transition-all hover:text-red-500"
                            >
                                <TrashIcon class="h-5 w-5" />
                            </button>
                        </div>
                    </div>
                </TransitionGroup>
            </div>

            <AdminEmptyState
                v-else
                key="empty-state"
                title="Страницы не найдены"
                @action="clearFilters"
                :show-action="true"
            />
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
