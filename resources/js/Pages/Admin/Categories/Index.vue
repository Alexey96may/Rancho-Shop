<script setup lang="ts">
    import { ref, watch } from 'vue';

    import { Head, router, useForm } from '@inertiajs/vue3';

    import { MagnifyingGlassIcon, PlusIcon } from '@heroicons/vue/24/outline';
    import debounce from 'lodash/debounce';

    // Твой новый компонент
    import CategoryCard from '@/Components/Admin/Cards/CategoryCard.vue';
    import AdminModal from '@/Components/UI/BaseModal.vue';
    import AdminSelect from '@/Components/UI/BaseSelect.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';

    const props = defineProps<{
        categories: { data: any[] };
        filters: { search?: string; type?: string };
    }>();

    const isModalOpen = ref(false);
    const editMode = ref(false);
    const currentId = ref<number | null>(null);

    const form = useForm({
        name: '',
        type: 'product',
        sort_order: 0,
        is_active: true,
    });

    // Настройки для твоего Select
    const typeOptions = [
        { id: 2, name: 'Продукты', slug: 'product' },
        { id: 3, name: 'Животные', slug: 'animal' },
    ];

    const modalTypeOptions = [
        { id: 1, name: 'Продукт', slug: 'product' },
        { id: 2, name: 'Животное', slug: 'animal' },
    ];

    const search = ref(props.filters.search || '');
    const typeFilter = ref(props.filters.type || '');

    const openModal = (category: any = null) => {
        editMode.value = !!category;
        if (category) {
            currentId.value = category.id;
            form.name = category.name;
            form.type = category.type;
            form.sort_order = category.sort_order;
            form.is_active = category.is_active;
        } else {
            form.reset();
        }
        isModalOpen.value = true;
    };

    const performSearch = (searchValue: string, typeValue: string) => {
        router.get(
            route('admin.categories.index'),
            { search: searchValue, type: typeValue },
            {
                preserveState: true,
                replace: true,
                preserveScroll: true,
            },
        );
    };

    const debouncedSearch = debounce((val: string) => {
        performSearch(val, typeFilter.value);
    }, 300);

    watch(search, (newVal) => {
        debouncedSearch(newVal);
    });

    watch(typeFilter, (newVal) => {
        performSearch(search.value, newVal);
    });

    const submit = () => {
        const options = { onSuccess: () => (isModalOpen.value = false) };
        if (editMode.value) {
            form.put(route('admin.categories.update', currentId.value!), options);
        } else {
            form.post(route('admin.categories.store'), options);
        }
    };
</script>

<template>
    <Head title="Категории" />

    <AdminLayout>
        <template #header>Категории</template>

        <div class="mb-10 flex flex-col gap-4 lg:flex-row lg:items-end">
            <div class="relative flex-1">
                <label for="search" class="sr-only">Поиск</label>
                <MagnifyingGlassIcon
                    class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-500"
                />
                <input
                    id="search"
                    v-model="search"
                    type="text"
                    placeholder="Найти категорию..."
                    class="w-full rounded-2xl border-slate-800 bg-slate-900/50 py-3 pl-12 text-white focus:border-orange-500 focus:ring-0"
                />
            </div>

            <AdminSelect
                v-model="typeFilter"
                :options="typeOptions"
                width-class="lg:w-64"
                placeholder="Выберите тип"
            />

            <button
                @click="openModal()"
                class="flex h-[50px] items-center justify-center gap-2 rounded-2xl bg-orange-600 px-8 text-sm font-black uppercase tracking-widest text-white transition-all hover:bg-orange-500"
            >
                <PlusIcon class="h-5 w-5" />
                Создать
            </button>
        </div>

        <div
            role="list"
            class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
        >
            <CategoryCard
                v-for="category in categories.data"
                :key="category.id"
                :category="category"
                @edit="openModal"
                @delete="(id) => router.delete(route('admin.categories.destroy', id))"
            />
        </div>

        <AdminModal
            :show="isModalOpen"
            :title="editMode ? 'Редактирование' : 'Новая категория'"
            @close="isModalOpen = false"
        >
            <form @submit.prevent="submit" class="space-y-6">
                <div class="space-y-2">
                    <label
                        class="ml-1 text-[10px] font-black uppercase tracking-widest text-slate-500"
                        >Название</label
                    >
                    <input
                        v-model="form.name"
                        type="text"
                        class="w-full rounded-2xl border-slate-800 bg-slate-950 p-4 text-white focus:border-orange-500"
                    />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <AdminSelect
                        label="Тип контента"
                        v-model="form.type"
                        :options="modalTypeOptions"
                        placeholder="Все типы"
                    />
                    <div class="space-y-2">
                        <label
                            class="ml-1 text-[10px] font-black uppercase tracking-widest text-slate-500"
                            >Сортировка</label
                        >
                        <input
                            v-model.number="form.sort_order"
                            type="number"
                            class="w-full rounded-2xl border-slate-800 bg-slate-950 p-4 text-white"
                        />
                    </div>
                </div>

                <button
                    type="button"
                    @click="form.is_active = !form.is_active"
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
                    class="w-full rounded-2xl bg-orange-600 py-4 text-xs font-black uppercase tracking-[0.2em] text-white hover:bg-orange-500"
                >
                    {{ editMode ? 'Обновить данные' : 'Создать категорию' }}
                </button>
            </form>
        </AdminModal>
    </AdminLayout>
</template>
