<script setup lang="ts">
    import { ref, watch } from 'vue';

    import { router, useForm } from '@inertiajs/vue3';

    import debounce from 'lodash/debounce';
    import { Loader2Icon, XIcon } from 'lucide-vue-next';
    import draggable from 'vuedraggable';

    import UnitCard from '@/Components/Admin/Cards/AdminUnitCard.vue';
    import AdminEmptyState from '@/Components/Admin/Shared/AdminEmptyState.vue';
    import AdminPagination from '@/Components/Admin/Shared/AdminPagination.vue';
    import AdminLoader from '@/Components/Admin/UI/AdminLoader.vue';
    import AdminSearchInput from '@/Components/Admin/UI/AdminSearchInput.vue';
    import BaseCreateButton from '@/Components/UI/BaseCreateButton.vue';
    import BaseInput from '@/Components/UI/BaseInput.vue';
    import BaseSubmitButton from '@/Components/UI/BaseSubmitButton.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useFlash } from '@/composables/useFlash';
    import { Paginated, UnitAdmin } from '@/types';

    defineOptions({ layout: AdminLayout });

    interface DraggableEvent {
        oldIndex: number;
        newIndex: number;
        item: HTMLElement;
        from: HTMLElement;
        to: HTMLElement;
    }

    const props = defineProps<{
        units: Paginated<UnitAdmin>;
        filters: { search: string };
    }>();

    const isModalOpen = ref(false);
    const editingUnit = ref<UnitAdmin | null>(null);
    const isSavingOrder = ref(false);

    const search = ref(props.filters.search || '');
    const { notifyWithUndo } = useFlash();

    const isFiltering = ref(false);

    watch(search, () => {
        isFiltering.value = true;

        performSearch();
    });

    const performSearch = debounce(() => {
        router.get(
            route('admin.units.index'),
            { search: search.value },
            {
                preserveState: true,
                replace: true,
                onFinish: () => {
                    isFiltering.value = false;
                },
            },
        );
    }, 300);

    const form = useForm({
        name: '',
        short: '',
        slug: '',
        position: 0,
    });

    const openModal = (unit: UnitAdmin | null = null) => {
        editingUnit.value = unit;

        if (unit) {
            form.name = unit.name;
            form.short = unit.short;
            form.slug = unit.slug;
            form.position = unit.position;
        } else {
            form.reset();
        }
        isModalOpen.value = true;
    };

    const closeModal = () => {
        isModalOpen.value = false;
        form.reset();
    };

    const submit = () => {
        if (editingUnit.value) {
            form.put(route('admin.units.update', editingUnit.value.id), {
                onError: (e) => console.log(e),
                onSuccess: () => closeModal(),
            });
        } else {
            form.post(route('admin.units.store'), {
                onSuccess: () => closeModal(),
            });
        }
    };

    const deletingIds = ref(new Set<number>());

    const deleteUnit = async (unit: UnitAdmin) => {
        if (deletingIds.value.has(unit.id)) return;
        deletingIds.value.add(unit.id);

        const isTimeOut = await notifyWithUndo('Удаление единицы «' + unit.name + '»');

        if (isTimeOut) {
            router.delete(route('admin.units.destroy', unit.id), {
                preserveScroll: true,
                onFinish: () => {
                    deletingIds.value.delete(unit.id);
                },
            });
        } else {
            deletingIds.value.delete(unit.id);
        }
    };

    const onDragEnd = (e: DraggableEvent) => {
        if (e.oldIndex === e.newIndex) {
            return;
        }

        const droppedItem = e.item;
        droppedItem.classList.remove('drop-highlight');

        void droppedItem.offsetWidth;

        droppedItem.classList.add('drop-highlight');

        setTimeout(() => {
            droppedItem.classList.remove('drop-highlight');
        }, 2000);

        isSavingOrder.value = true;

        router.patch(
            route('admin.units.reorder'),
            {
                ids: props.units.data.map((u) => u.id),
            },
            {
                onFinish: () => (isSavingOrder.value = false),
                preserveScroll: true,
                preserveState: true,
            },
        );
    };

    const clearFilters = () => {
        search.value = '';
    };

    const vibrate = () => {
        if (!window) return;

        if (window.navigator && window.navigator.vibrate) {
            window.navigator.vibrate(10);
        }
    };
</script>

<template>
    <Teleport to="#admin-header-content">
        <div class="flex items-center gap-4">
            <h1 class="text-xl font-black uppercase tracking-wider text-white">
                Единицы измерения
            </h1>
            <span
                v-if="isSavingOrder"
                class="flex animate-pulse items-center gap-2 text-[10px] font-bold text-orange-500"
            >
                <Loader2Icon class="h-3 w-3 animate-spin" /> СОХРАНЕНИЕ ПОРЯДКА
            </span>
        </div>
    </Teleport>

    <div class="space-y-6">
        <div class="flex justify-end">
            <AdminSearchInput v-model="search" placeholder="Поиск вопроса..." />

            <BaseCreateButton @click="openModal(null)" label="Добавить" />
        </div>

        <div class="animate-in fade-in slide-in-from-bottom-4 duration-700">
            <Transition name="fade-slide" mode="out-in">
                <draggable
                    v-if="units.data.length"
                    v-model="units.data"
                    item-key="id"
                    tag="div"
                    key="units"
                    :animation="300"
                    fallback-tolerance="3"
                    :force-fallback="true"
                    handle=".drag-handle"
                    @start="vibrate"
                    @end="onDragEnd"
                    ghost-class="ghost-card"
                    chosen-class="chosen-card"
                    drag-class="drag-card"
                    class="space-y-3"
                >
                    <template #item="{ element: unit }">
                        <UnitCard
                            class="sortable-item"
                            :key="unit.id"
                            :unit="unit"
                            @edit="openModal"
                            @delete="deleteUnit"
                            :disabled="deletingIds.has(unit.id)"
                        />
                    </template>
                </draggable>

                <AdminLoader v-else-if="isFiltering" key="loading" text="Синхронизация" />

                <AdminEmptyState
                    v-else
                    key="empty"
                    :title="
                        search ? 'Единицы измерения не найдены' : 'Список единиц измерения пуст'
                    "
                    @action="search ? clearFilters() : openModal()"
                    :action-text="search ? 'Очистить фильтр' : 'Добавить единицу измерения'"
                    :show-action="true"
                    :description="
                        search
                            ? 'По запросу «' + search + '» совпадений нет'
                            : 'Нет ни одной единицы измерения'
                    "
                />
            </Transition>
        </div>

        <AdminPagination :links="units.meta.links" />
    </div>

    <div
        v-if="isModalOpen"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        role="dialog"
        aria-modal="true"
    >
        <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm" @click="closeModal"></div>

        <div
            class="shadow-2xl animate-in zoom-in-95 relative w-full max-w-md rounded-[2.5rem] border border-slate-800 bg-slate-900 p-8 duration-200"
        >
            <button
                @click="closeModal"
                class="absolute right-6 top-6 text-slate-500 hover:text-white"
            >
                <XIcon class="h-6 w-6" />
            </button>

            <h2 class="mb-8 text-lg font-black uppercase tracking-widest text-white">
                {{ editingUnit ? 'Редактировать' : 'Новая единица' }}
            </h2>

            <form @submit.prevent="submit" class="space-y-5">
                <BaseInput
                    v-model="form.name"
                    v-model:error="form.errors.name"
                    label="Название"
                    placeholder="Килограмм"
                    :disabled="form.processing"
                />

                <div class="grid grid-cols-2 gap-4">
                    <BaseInput
                        v-model="form.short"
                        v-model:error="form.errors.short"
                        label="Сокращение"
                        placeholder="кг"
                        :disabled="form.processing"
                    />

                    <BaseInput
                        v-model="form.slug"
                        v-model:error="form.errors.slug"
                        label="Slug"
                        placeholder="kg"
                        :disabled="form.processing"
                    />
                </div>

                <BaseSubmitButton :processing="form.processing" label="Подтвердить" />
            </form>
        </div>
    </div>
</template>

<style scoped>
    .sortable-item {
        user-select: none;
        -webkit-user-select: none;
        transition: opacity 0.2s ease;
    }

    .faq-answer-content {
        user-select: text;
    }

    .chosen-card {
        opacity: 0.4;
        background-color: rgba(249, 115, 22, 0.05);
    }

    .drag-card {
        opacity: 1 !important;
        transform: scale(1.02);
        cursor: grabbing;
        box-shadow:
            0 20px 25px -5px rgb(0 0 0 / 0.5),
            0 8px 10px -6px rgb(0 0 0 / 0.5);
        z-index: 9999;
        transition: none !important;
    }

    .ghost-card {
        background-color: rgba(249, 115, 22, 0.1) !important;
        border: 1px dashed rgb(92, 92, 92) !important;
        border-radius: 1.5rem;
        opacity: 0.4 !important;
    }

    .drag-handle {
        touch-action: none;
        cursor: grab;
    }

    .drop-highlight {
        animation: pulse-border 2s cubic-bezier(0.4, 0, 0.6, 1);
        border-radius: 1.5rem;
        position: relative;
        z-index: 10;
    }

    @keyframes pulse-border {
        0% {
            outline: 1px solid #22c55e; /* green-500 */
            box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.5);
            transform: scale(1.005);
        }
        30% {
            outline: 1px solid #ffffff;
            box-shadow: 0 0 0 10px rgba(255, 255, 255, 0);
            transform: scale(1);
        }
        100% {
            outline: 1px solid rgba(255, 255, 255, 0);
            box-shadow: 0 0 0 0 rgba(255, 255, 255, 0);
        }
    }

    .fade-slide-enter-active {
        transition: all 0.4s ease-out;
    }

    .fade-slide-enter-from {
        opacity: 0;
        transform: translateY(-10px);
    }
</style>
