<script setup lang="ts">
    import { ref, watch } from 'vue';

    import { router, useForm } from '@inertiajs/vue3';

    import CategoryCard from '@/Components/Admin/Cards/CategoryCard.vue';
    import AdminEmptyState from '@/Components/Admin/Shared/AdminEmptyState.vue';
    import AdminPageHeader from '@/Components/Admin/Shared/AdminPageHeader.vue';
    import AdminPagination from '@/Components/Admin/Shared/AdminPagination.vue';
    import AdminLoader from '@/Components/Admin/UI/AdminLoader.vue';
    import AdminNumberInput from '@/Components/Admin/UI/AdminNumberInput.vue';
    import AdminSearchInput from '@/Components/Admin/UI/AdminSearchInput.vue';
    import BaseInput from '@/Components/UI/BaseInput.vue';
    import AdminModal from '@/Components/UI/BaseModal.vue';
    import AdminSelect from '@/Components/UI/BaseSelect.vue';
    import BaseStatusToggle from '@/Components/UI/BaseStatusToggle.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useFlash } from '@/composables/useFlash';
    import { AdminCategory, Paginated } from '@/types';

    const props = defineProps<{
        categories: Paginated<AdminCategory>;
        filters: { search?: string; type?: string };
    }>();

    defineOptions({ layout: AdminLayout });

    const isModalOpen = ref(false);
    const editMode = ref(false);
    const isFiltering = ref(false);
    const currentId = ref<number | null>(null);

    const form = useForm({
        name: '',
        description: '',
        type: 'product',
        sort_order: 999,
        is_active: true,
        icon: '',
    });

    const typeOptions = [
        { id: 2, name: 'Продукты', slug: 'product' },
        { id: 3, name: 'Животные', slug: 'animal' },
    ];

    const modalTypeOptions = [
        { id: 1, name: 'Продукт', slug: 'product' },
        { id: 2, name: 'Животное', slug: 'animal' },
    ];

    const { notifyWithUndo, notify } = useFlash();

    const search = ref(props.filters.search || '');
    const typeFilter = ref(props.filters.type || '');

    const openModal = (category: any = null) => {
        form.clearErrors();
        editMode.value = !!category;

        if (category) {
            currentId.value = category.id;
            form.name = category.name;
            form.type = category.type;
            form.sort_order = category.sort_order;
            form.is_active = category.is_active;
            form.icon = category.icon;
        } else {
            form.reset();
        }
        isModalOpen.value = true;
    };

    const performSearch = (searchValue: string, typeValue: string) => {
        isFiltering.value = true;

        const searchSanitize = searchValue.toLocaleLowerCase().trim();
        const typeSanitize = typeValue.toLocaleLowerCase().trim();

        router.get(
            route('admin.categories.index'),
            { search: searchSanitize, type: typeSanitize || typeFilter.value },
            {
                preserveState: true,
                replace: true,
                preserveScroll: true,
                onFinish: () => {
                    isFiltering.value = false;
                },
            },
        );
    };

    watch(typeFilter, (newVal) => {
        performSearch(search.value, newVal);
    });

    watch(search, (newVal) => {
        performSearch(newVal, typeFilter.value);
    });

    const submit = () => {
        const options = {
            onSuccess: () => (isModalOpen.value = false),
            onError: (errors: Record<string, string>) => {
                console.error(errors);
                notify('Ошибка валидации', 'error');
            },
        };
        if (editMode.value) {
            form.put(route('admin.categories.update', currentId.value!), options);
        } else {
            form.post(route('admin.categories.store'), options);
        }
    };

    const deleteCategory = async (id: number) => {
        const deleted = await notifyWithUndo('Удаление категории');
        if (!deleted) return;

        router.delete(route('admin.categories.destroy', id), {
            preserveScroll: true,
        });
    };

    const clearFilters = () => {
        search.value = '';
        typeFilter.value = '';
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
            <AdminSearchInput
                v-model="search"
                @search="performSearch"
                placeholder="Найти категорию..."
            />

            <AdminSelect
                v-model="typeFilter"
                :options="typeOptions"
                value-key="slug"
                label-key="name"
                variant="admin"
                width-class="lg:w-64"
                placeholder="Выберите тип"
            />
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
                            @edit="openModal"
                            @delete="deleteCategory(category.id)"
                        />
                    </TransitionGroup>
                </div>

                <AdminLoader v-else-if="isFiltering" key="loading" text="Синхронизация" />

                <AdminEmptyState
                    v-else
                    key="empty"
                    title="Категории не найдены"
                    @action="clearFilters"
                    :show-action="true"
                />
            </Transition>
        </div>

        <Transition name="fade-slide" mode="out-in">
            <footer v-if="categories.data.length > 0 && !isFiltering">
                <AdminPagination :links="categories.meta.links" />
            </footer>
        </Transition>

        <AdminModal
            :show="isModalOpen"
            :title="editMode ? 'Редактирование' : 'Новая категория'"
            @close="isModalOpen = false"
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

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full rounded-2xl bg-orange-600 py-4 text-xs font-black uppercase tracking-[0.2em] text-white hover:bg-orange-500 disabled:opacity-50"
                >
                    <span v-if="form.processing">Загрузка...</span>
                    <span v-else>{{ editMode ? 'Обновить данные' : 'Создать категорию' }}</span>
                </button>
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
