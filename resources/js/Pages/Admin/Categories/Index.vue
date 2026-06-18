<script setup lang="ts">
    import { PropType, watch } from 'vue';

    import { useForm } from '@inertiajs/vue3';

    import CategoryCard from '@/Components/Admin/Cards/CategoryCard.vue';
    import AdminEmptyState from '@/Components/Admin/Shared/AdminEmptyState.vue';
    import AdminPageHeader from '@/Components/Admin/Shared/AdminPageHeader.vue';
    import AdminPagination from '@/Components/Admin/Shared/AdminPagination.vue';
    import AdminLoader from '@/Components/Admin/UI/AdminLoader.vue';
    import AdminNumberInput from '@/Components/Admin/UI/AdminNumberInput.vue';
    import AdminSearchInput from '@/Components/Admin/UI/AdminSearchInput.vue';
    import BaseCreateButton from '@/Components/UI/BaseCreateButton.vue';
    import BaseInput from '@/Components/UI/BaseInput.vue';
    import AdminModal from '@/Components/UI/BaseModal.vue';
    import AdminSelect from '@/Components/UI/BaseSelect.vue';
    import BaseStatusToggle from '@/Components/UI/BaseStatusToggle.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useAdminCrud } from '@/composables/crud/useAdminCrud';
    import { useAdminForm } from '@/composables/crud/useAdminForm';
    import { useAdminFilters } from '@/composables/routing/useAdminFilters';
    import { AdminCategory, Paginated } from '@/types';

    const props = defineProps({
        categories: {
            type: Object as PropType<Paginated<AdminCategory>>,
            required: true,
            validator: (value: unknown): boolean => {
                const val = value as Record<string, unknown>;
                const hasData = Array.isArray(val?.data);
                const hasMeta = val?.meta && typeof val.meta === 'object';

                if (!hasData || !hasMeta) {
                    console.warn(
                        'Runtime Error: The "categories" prop must match the Laravel Paginated structure (contain data array and meta object).',
                    );
                }
                return !!(hasData && hasMeta);
            },
        },
        filters: {
            type: Object as PropType<{ search?: string; type?: string }>,
            required: true,
            default: () => ({ search: '', type: '' }),
        },
    });

    defineOptions({ layout: AdminLayout });

    const form = useForm({
        name: '',
        description: '',
        type: 'product',
        sort_order: 999,
        is_active: true,
        icon: '',
    });

    const filterForm = useForm({
        search: props.filters.search || '',
        type: props.filters.type || '',
    });

    const typeOptions = [
        { id: 2, name: 'Продукты', slug: 'product' },
        { id: 3, name: 'Животные', slug: 'animal' },
    ];

    const modalTypeOptions = [
        { id: 1, name: 'Продукт', slug: 'product' },
        { id: 2, name: 'Животное', slug: 'animal' },
    ];

    const { deleteEntity, isDeleting } = useAdminCrud();
    const { isFiltering, submitFilters, clearFilters } = useAdminFilters();
    const { submitForm, isModalOpen, editMode, currentId, openModal, closeModal } = useAdminForm();

    watch(
        () => [filterForm.search, filterForm.type],
        () => {
            submitFilters(filterForm, 'admin.categories.index');
        },
    );

    const submit = () => {
        submitForm(form, 'admin.categories', editMode.value ? currentId.value : null, {
            onSuccess: () => closeModal(form),
        });
    };
</script>

<template>
    <Teleport to="#admin-header-content">
        <AdminPageHeader title="Список Категорий" subtitle="Управление базой категорий" />
    </Teleport>

    <main id="main-content">
        <section
            aria-label="Фильтры и поиск"
            class="mb-10 flex flex-col gap-4 lg:flex-row lg:items-end"
        >
            <AdminSearchInput v-model="filterForm.search" placeholder="Найти категорию..." />

            <AdminSelect
                v-model="filterForm.type"
                :options="typeOptions"
                value-key="slug"
                label-key="name"
                variant="admin"
                width-class="lg:w-64"
                placeholder="Выберите тип"
            />

            <BaseCreateButton label="Добавить" @click="openModal(form)" />
        </section>

        <div class="relative min-h-[400px]">
            <Transition name="fade-slide">
                <div
                    v-if="categories.data.length > 0"
                    key="categories"
                    role="list"
                    aria-label="Список категорий"
                    class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
                >
                    <TransitionGroup name="category-list">
                        <CategoryCard
                            v-for="category in categories.data"
                            :key="category.id"
                            :category="category"
                            :disabled="isDeleting(category.id)"
                            @edit="openModal(form, category)"
                            @delete="
                                deleteEntity(
                                    'admin.categories',
                                    category.id,
                                    `Удаление категории «${category.name}»`,
                                )
                            "
                        />
                    </TransitionGroup>
                </div>

                <AdminLoader v-else-if="isFiltering" key="loading" text="Синхронизация" />

                <AdminEmptyState
                    v-else
                    key="empty"
                    title="Категории не найдены"
                    @action="clearFilters(filterForm)"
                    :show-action="true"
                />
            </Transition>
        </div>

        <footer>
            <AdminPagination :links="categories.meta.links" />
        </footer>

        <AdminModal
            :show="isModalOpen"
            :title="editMode ? 'Редактирование' : 'Новая категория'"
            @close="closeModal"
        >
            <form @submit.prevent="submit" class="space-y-6" aria-label="Форма категории">
                <BaseInput
                    v-model="form.name"
                    v-model:error="form.errors.name"
                    label="Название"
                    placeholder=""
                />

                <BaseInput
                    v-model="form.description"
                    v-model:error="form.errors.description"
                    label="Описание (необязательно)"
                    placeholder="Категория для..."
                />

                <BaseInput
                    v-model="form.icon"
                    v-model:error="form.errors.icon"
                    label="Иконка (lucide)"
                    placeholder="Egg"
                />

                <div class="grid grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <AdminSelect
                            label="Тип контента"
                            v-model="form.type"
                            value-key="slug"
                            label-key="name"
                            variant="admin"
                            :options="modalTypeOptions"
                        />
                    </div>

                    <div class="space-y-2">
                        <AdminNumberInput
                            label="Сортировка"
                            v-model="form.sort_order"
                            :min="0"
                            :max="999"
                        />
                    </div>
                </div>

                <BaseStatusToggle
                    v-model="form.is_active"
                    label="Доступность"
                    :disabled="form.processing"
                />

                <BaseCreateButton
                    type="submit"
                    :label="editMode ? 'Обновить данные' : 'Создать категорию'"
                    :disabled="form.processing"
                />
            </form>
        </AdminModal>
    </main>
</template>

<style scoped>
    .category-list-enter-active,
    .category-list-leave-active {
        transition: all 0.4s ease-out;
    }
    .category-list-enter-from,
    .category-list-leave-to {
        opacity: 0;
        transform: translateY(20px);
    }

    .fade-slide-enter-active {
        transition: all 0.5s ease-out;
    }
    .fade-slide-enter-from {
        opacity: 0;
        transform: scale(0.95);
    }
</style>
