<script setup lang="ts">
    import { PropType, watch } from 'vue';

    import { useForm } from '@inertiajs/vue3';

    import draggable from 'vuedraggable';

    import UnitCard from '@/Components/Admin/Cards/AdminUnitCard.vue';
    import AdminEmptyState from '@/Components/Admin/Shared/AdminEmptyState.vue';
    import AdminPageHeader from '@/Components/Admin/Shared/AdminPageHeader.vue';
    import AdminPagination from '@/Components/Admin/Shared/AdminPagination.vue';
    import AdminLoader from '@/Components/Admin/UI/AdminLoader.vue';
    import AdminSearchInput from '@/Components/Admin/UI/AdminSearchInput.vue';
    import BaseCreateButton from '@/Components/UI/BaseCreateButton.vue';
    import BaseInput from '@/Components/UI/BaseInput.vue';
    import Modal from '@/Components/UI/BaseModal.vue';
    import BaseSubmitButton from '@/Components/UI/BaseSubmitButton.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useAdminCrud } from '@/composables/crud/useAdminCrud';
    import { useAdminForm } from '@/composables/crud/useAdminForm';
    import { useAdminFilters } from '@/composables/routing/useAdminFilters';
    import { type DraggableEvent, useAdminReorder } from '@/composables/routing/useAdminReorder';
    import { Paginated, UnitAdmin } from '@/types';
    import { triggerVibration } from '@/utils/navigator';

    defineOptions({ layout: AdminLayout });

    const props = defineProps({
        units: {
            type: Object as PropType<Paginated<UnitAdmin>>,
            required: true,
            validator: (value: unknown): boolean => {
                const val = value as Record<string, unknown>;
                const hasData = Array.isArray(val?.data);
                const hasMeta = val?.meta && typeof val.meta === 'object';

                if (!hasData || !hasMeta) {
                    console.warn(
                        'Runtime Error: The "units" prop must match the Paginated structure.',
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

    const form = useForm({
        name: '',
        short: '',
        slug: '',
        position: 0,
    });

    const filterForm = useForm({
        search: props.filters.search || '',
    });

    const { deleteEntity, isDeleting } = useAdminCrud();
    const { isFiltering, submitFilters, clearFilters } = useAdminFilters();
    const { submitForm, isModalOpen, editMode, currentId, openModal, closeModal } = useAdminForm();
    const { handleReorder } = useAdminReorder();

    const submit = () => {
        submitForm(form, 'admin.units', editMode.value ? currentId.value : null, {
            onSuccess: () => closeModal(form),
        });
    };

    const onReorder = (e: DraggableEvent) => {
        handleReorder(e, 'admin.units.reorder', props.units.data);
    };

    const vibrateDraggable = () => {
        triggerVibration('click');
    };

    watch(
        () => [filterForm.search],
        () => {
            submitFilters(filterForm, 'admin.units.index');
        },
    );
</script>

<template>
    <Teleport to="#admin-header-content">
        <AdminPageHeader
            title="Единицы измерения"
            subtitle="Управление единицами измерения магазина"
        />
    </Teleport>

    <div class="space-y-6">
        <div class="flex justify-end">
            <AdminSearchInput
                v-model="filterForm.search"
                placeholder="Поиск по имени, слагу и сокращению..."
            />

            <BaseCreateButton @click="openModal(form)" label="Добавить" />
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
                    @start="vibrateDraggable"
                    @end="onReorder"
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
                            @edit="openModal(form, unit)"
                            @delete="
                                deleteEntity(
                                    'admin.units',
                                    unit.id,
                                    `Удаление единицы «${unit.name}»`,
                                )
                            "
                            :disabled="isDeleting(unit.id)"
                        />
                    </template>
                </draggable>

                <AdminLoader v-else-if="isFiltering" key="loading" text="Синхронизация" />

                <AdminEmptyState
                    v-else
                    key="empty"
                    :title="
                        filterForm.search
                            ? 'Единицы измерения не найдены'
                            : 'Список единиц измерения пуст'
                    "
                    @action="filterForm.search ? clearFilters(filterForm) : openModal(form)"
                    :action-text="
                        filterForm.search ? 'Очистить фильтр' : 'Добавить единицу измерения'
                    "
                    :show-action="true"
                    :description="
                        filterForm.search
                            ? 'По запросу «' + filterForm.search + '» совпадений нет'
                            : 'Нет ни одной единицы измерения'
                    "
                />
            </Transition>
        </div>

        <Transition name="fade-slide" mode="out-in">
            <AdminPagination v-show="!isFiltering" :links="units.meta.links" />
        </Transition>
    </div>

    <Modal :show="isModalOpen" @close="closeModal" max-width="2xl" aria-labelledby="modal-title">
        <h2 class="mb-8 text-lg font-black uppercase tracking-widest text-white">
            {{ editMode ? 'Редактировать' : 'Новая единица' }}
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

            <BaseSubmitButton
                :processing="form.processing"
                :is-edit="editMode"
                :label="editMode ? 'Обновить' : 'Создать '"
            />
        </form>
    </Modal>
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
