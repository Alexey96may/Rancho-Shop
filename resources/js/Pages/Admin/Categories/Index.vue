<script setup lang="ts">
    import { ref, watch } from 'vue';

    import { Head, router, useForm } from '@inertiajs/vue3';

    import { InboxIcon } from '@heroicons/vue/24/outline';

    // Иконка для пустого состояния

    import CategoryCard from '@/Components/Admin/Cards/CategoryCard.vue';
    import AdminPageHeader from '@/Components/Admin/Shared/AdminPageHeader.vue';
    import AdminPagination from '@/Components/Admin/Shared/AdminPagination.vue';
    import AdminNumberInput from '@/Components/Admin/UI/AdminNumberInput.vue';
    import AdminSearchInput from '@/Components/Admin/UI/AdminSearchInput.vue';
    import AdminModal from '@/Components/UI/BaseModal.vue';
    import AdminSelect from '@/Components/UI/BaseSelect.vue';
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
    const currentId = ref<number | null>(null);

    const form = useForm({
        name: '',
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
        const searchSanitize = searchValue.toLocaleLowerCase().trim();
        const typeSanitize = typeValue.toLocaleLowerCase().trim();

        router.get(
            route('admin.categories.index'),
            { search: searchSanitize, type: typeSanitize || typeFilter.value },
            {
                preserveState: true,
                replace: true,
                preserveScroll: true,
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
        notify('Фильтры очищены', 'info');
    };
</script>

<template>
    <AdminPageHeader
        title-normal="Список"
        title-orange="Категорий"
        subtitle="Управление базой категорий"
        button-text="Создать"
        @action="openModal()"
    />

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
            <TransitionGroup
                v-if="categories.data.length > 0"
                :key="categories.data.length"
                tag="div"
                name="category-list"
                class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
                role="list"
                aria-label="Список категорий"
            >
                <CategoryCard
                    v-for="category in categories.data"
                    :key="category.id"
                    :category="category"
                    @edit="openModal"
                    @delete="deleteCategory(category.id)"
                />
            </TransitionGroup>

            <Transition name="fade-slide">
                <div
                    v-if="categories.data.length === 0"
                    class="flex flex-col items-center justify-center rounded-3xl border border-dashed border-slate-800 bg-slate-900/20 py-20 text-center"
                    role="alert"
                    aria-live="polite"
                >
                    <div class="shadow-inner rounded-full bg-slate-900 p-6 text-slate-700">
                        <InboxIcon class="h-12 w-12" aria-hidden="true" />
                    </div>
                    <h3 class="mt-6 text-xl font-bold text-white">Категории не найдены</h3>
                    <p class="mt-2 text-sm text-slate-500">
                        Попробуйте изменить параметры поиска или создать новую категорию
                    </p>
                    <button
                        @click="clearFilters()"
                        class="mt-6 text-xs font-black uppercase tracking-widest text-orange-500 hover:text-orange-400"
                    >
                        Сбросить фильтры
                    </button>
                </div>
            </Transition>
        </div>

        <footer v-if="categories.data.length > 0">
            <AdminPagination :links="categories.meta.links" />
        </footer>

        <AdminModal
            :show="isModalOpen"
            :title="editMode ? 'Редактирование' : 'Новая категория'"
            @close="isModalOpen = false"
        >
            <form @submit.prevent="submit" class="space-y-6" aria-label="Форма категории">
                <div class="space-y-2">
                    <label
                        for="category-name"
                        class="ml-1 text-[10px] font-black uppercase tracking-widest text-slate-500"
                        >Название</label
                    >
                    <input
                        id="category-name"
                        v-model="form.name"
                        type="text"
                        required
                        :aria-invalid="!!form.errors.name"
                        :aria-describedby="form.errors.name ? 'name-error' : undefined"
                        :class="[
                            'w-full rounded-2xl border bg-slate-950 p-4 text-white transition-colors focus:ring-0',
                            form.errors.name
                                ? 'border-red-500 focus:border-red-500'
                                : 'border-slate-800 focus:border-orange-500',
                        ]"
                    />
                    <p
                        v-if="form.errors.name"
                        id="name-error"
                        class="ml-1 text-xs font-medium text-red-500"
                        role="alert"
                    >
                        {{ form.errors.name }}
                    </p>
                </div>

                <div class="space-y-2">
                    <label
                        class="ml-1 text-[10px] font-black uppercase tracking-widest text-slate-500"
                        >Иконка (lucide)</label
                    >
                    <input
                        v-model="form.icon"
                        type="text"
                        placeholder="например: Leaf"
                        :class="[
                            'w-full rounded-2xl border bg-slate-950 p-4 text-white transition-colors focus:ring-0',
                            form.errors.icon
                                ? 'border-red-500 focus:border-red-500'
                                : 'border-slate-800 focus:border-orange-500',
                        ]"
                    />
                </div>

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

                <button
                    type="button"
                    @click="form.is_active = !form.is_active"
                    role="switch"
                    :aria-checked="form.is_active"
                    class="flex w-full items-center justify-between rounded-2xl border border-slate-800 bg-slate-950 p-4 transition-colors"
                    :class="form.is_active ? 'border-emerald-500/30' : ''"
                >
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-500"
                        >Доступность</span
                    >
                    <div
                        class="h-2.5 w-2.5 rounded-full transition-all"
                        :class="
                            form.is_active
                                ? 'bg-emerald-500 shadow-[0_0_10px_#10b981]'
                                : 'bg-slate-700'
                        "
                    ></div>
                </button>

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
