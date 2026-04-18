<script setup lang="ts">
    import { ref, watch } from 'vue';

    import { Head, router, useForm } from '@inertiajs/vue3';

    import {
        AdjustmentsHorizontalIcon,
        ChevronLeftIcon,
        GlobeAltIcon,
        MagnifyingGlassIcon,
        PhotoIcon,
        PlusIcon,
    } from '@heroicons/vue/24/outline';
    import debounce from 'lodash/debounce';

    import AnimalCard from '@/Components/Admin/Cards/AdminAnimalCard.vue';
    import AdminSelect from '@/Components/UI/BaseSelect.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';

    const props = defineProps<{
        animals: { data: any[] };
        categories: any[];
        filters: any;
    }>();

    // Переключатель вида: 'list' или 'form'
    const view = ref<'list' | 'form'>('list');
    const activeTab = ref('main');
    const editMode = ref(false);
    const currentId = ref<number | null>(null);

    const form = useForm({
        name: '',
        category_id: '',
        parent_id: '',
        status: 'healthy',
        bio: '',
        features: {} as any,
        is_active: true,
        seo: { title: '', description: '', keywords: '' },
        avatar: null as File | null,
    });

    // Фильтрация списка
    const search = ref(props.filters.search || '');
    const catFilter = ref(props.filters.category_id || '');

    const debouncedFilter = debounce(() => {
        router.get(
            route('admin.animals.index'),
            { search: search.value, category_id: catFilter.value },
            { preserveState: true, replace: true },
        );
    }, 300);

    watch([search, catFilter], debouncedFilter);

    const openForm = (animal: any = null) => {
        editMode.value = !!animal;
        activeTab.value = 'main';
        if (animal) {
            currentId.value = animal.id;
            form.name = animal.name;
            form.category_id = animal.category?.id || '';
            form.status = animal.status;
            form.bio = animal.bio;
            form.features = animal.features || {};
            form.is_active = animal.is_active;
            form.seo = animal.seo || { title: '', description: '', keywords: '' };
        } else {
            form.reset();
        }
        view.value = 'form';
    };

    const closeForm = () => {
        view.value = 'list';
        form.reset();
    };

    const submit = () => {
        const url = editMode.value
            ? route('admin.animals.update', currentId.value!)
            : route('admin.animals.store');

        form.post(url, {
            onSuccess: () => closeForm(),
            forceFormData: true,
        });
    };

    const handleFileChange = (event: Event) => {
        const target = event.target as HTMLInputElement;
        if (target.files?.length) {
            form.avatar = target.files[0];
        }
    };

    /**
     * Добавляет новую пустую характеристику
     */
    const addFeature = () => {
        // Используем временный ключ, пока пользователь не введет название
        const newKey = `Параметр ${Object.keys(form.features).length + 1}`;
        form.features[newKey] = '';
    };

    /**
     * Удаляет характеристику по ключу
     */
    const removeFeature = (key: string) => {
        delete form.features[key];
    };

    /**
     * Переименовывает ключ в объекте (хитрая магия JS, чтобы сохранить реактивность)
     */
    const renameFeatureKey = (oldKey: string, newKey: string) => {
        if (oldKey !== newKey) {
            Object.defineProperty(
                form.features,
                newKey,
                Object.getOwnPropertyDescriptor(form.features, oldKey)!,
            );
            delete form.features[oldKey];
        }
    };
</script>

<template>
    <Head title="Управление стадом" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <button
                    v-if="view === 'form'"
                    @click="closeForm"
                    class="group flex h-10 w-10 items-center justify-center rounded-full border border-slate-800 bg-slate-900 transition-all hover:border-orange-500"
                    aria-label="Вернуться к списку"
                >
                    <ChevronLeftIcon class="h-5 w-5 text-slate-500 group-hover:text-orange-500" />
                </button>
                <span>{{
                    view === 'list'
                        ? 'Наше Стадо'
                        : editMode
                          ? 'Редактирование: ' + form.name
                          : 'Новое животное'
                }}</span>
            </div>
        </template>

        <div v-if="view === 'list'" class="animate-in fade-in duration-500">
            <div class="mb-10 flex flex-col gap-4 lg:flex-row lg:items-end">
                <div class="relative flex-1">
                    <MagnifyingGlassIcon
                        class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-500"
                    />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Поиск по кличке..."
                        class="w-full rounded-2xl border-slate-800 bg-slate-900/50 py-3.5 pl-12 text-white focus:border-orange-500"
                    />
                </div>

                <AdminSelect
                    v-model="catFilter"
                    :options="categories"
                    placeholder="Все категории"
                    width-class="lg:w-64"
                />

                <button
                    @click="openForm()"
                    class="shadow-lg flex h-[54px] items-center justify-center gap-2 rounded-2xl bg-orange-600 px-8 text-sm font-black uppercase tracking-widest text-white shadow-orange-900/20 transition-all hover:bg-orange-500"
                >
                    <PlusIcon class="h-5 w-5" /> Добавить
                </button>
            </div>

            <div
                role="list"
                class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
            >
                <AnimalCard
                    v-for="animal in animals.data"
                    :key="animal.id"
                    :animal="animal"
                    @edit="openForm"
                    @delete="(id) => router.delete(route('admin.animals.destroy', id))"
                />
            </div>
        </div>

        <div v-else class="animate-in slide-in-from-right-4 fade-in max-w-5xl duration-300">
            <div
                class="rounded-[2.5rem] border border-slate-800 bg-slate-900/30 p-8 backdrop-blur-sm"
            >
                <nav class="mb-10 flex gap-8 border-b border-slate-800" role="tablist">
                    <button
                        v-for="tab in [
                            { id: 'main', name: 'Инфо', icon: PhotoIcon },
                            {
                                id: 'features',
                                name: 'Характеристики',
                                icon: AdjustmentsHorizontalIcon,
                            },
                            { id: 'seo', name: 'SEO оптимизация', icon: GlobeAltIcon },
                        ]"
                        :key="tab.id"
                        @click="activeTab = tab.id"
                        class="relative flex items-center gap-2 pb-4 text-xs font-black uppercase tracking-widest transition-all"
                        :class="
                            activeTab === tab.id
                                ? 'text-orange-500'
                                : 'text-slate-500 hover:text-slate-300'
                        "
                        role="tab"
                        :aria-selected="activeTab === tab.id"
                    >
                        <component :is="tab.icon" class="h-4 w-4" />
                        {{ tab.name }}
                        <div
                            v-if="activeTab === tab.id"
                            class="absolute bottom-0 left-0 h-0.5 w-full bg-orange-500 shadow-[0_0_8px_#f97316]"
                        ></div>
                    </button>
                </nav>

                <form @submit.prevent="submit" class="space-y-8">
                    <div
                        v-if="activeTab === 'main'"
                        class="animate-in fade-in zoom-in-95 grid grid-cols-1 gap-8 lg:grid-cols-2"
                    >
                        <div class="space-y-6">
                            <div class="space-y-2">
                                <label
                                    class="ml-2 text-[10px] font-black uppercase tracking-widest text-slate-500"
                                    >Кличка животного</label
                                >
                                <input
                                    v-model="form.name"
                                    type="text"
                                    required
                                    class="w-full rounded-2xl border-slate-800 bg-slate-950 p-4 text-white transition-all focus:border-orange-500"
                                />
                            </div>

                            <AdminSelect
                                label="Категория"
                                v-model="form.category_id"
                                :options="categories"
                            />

                            <div class="space-y-2">
                                <label
                                    class="ml-2 text-[10px] font-black uppercase tracking-widest text-slate-500"
                                    >Фото (Аватар)</label
                                >
                                <div
                                    class="relative flex items-center justify-center rounded-2xl border-2 border-dashed border-slate-800 bg-slate-950/50 p-8 transition-colors hover:border-orange-500/50"
                                >
                                    <input
                                        type="file"
                                        @change="handleFileChange"
                                        accept="image/*"
                                        class="absolute inset-0 cursor-pointer opacity-0"
                                        aria-label="Загрузить фото"
                                    />
                                    <div class="text-center">
                                        <PhotoIcon class="mx-auto h-10 w-10 text-slate-700" />
                                        <p class="mt-2 text-xs text-slate-500">
                                            {{
                                                form.avatar
                                                    ? form.avatar.name
                                                    : 'Нажмите или перетащите файл'
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div class="space-y-2">
                                <label
                                    class="ml-2 text-[10px] font-black uppercase tracking-widest text-slate-500"
                                    >Описание / Биография</label
                                >
                                <textarea
                                    v-model="form.bio"
                                    rows="10"
                                    class="w-full rounded-2xl border-slate-800 bg-slate-950 p-4 text-white focus:border-orange-500"
                                    placeholder="Расскажите об особенностях животного..."
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="activeTab === 'seo'"
                        class="animate-in fade-in zoom-in-95 max-w-2xl space-y-6"
                    >
                        <div
                            class="rounded-2xl border border-orange-500/20 bg-orange-500/5 p-4 text-[11px] leading-relaxed text-orange-200/60"
                        >
                            Настройте отображение этой страницы в поисковых системах. Это поможет
                            людям быстрее находить информацию о вашем хозяйстве.
                        </div>
                        <div class="space-y-2">
                            <label
                                class="ml-2 text-[10px] font-black uppercase tracking-widest text-slate-500"
                                >Meta Title</label
                            >
                            <input
                                v-model="form.seo.title"
                                type="text"
                                class="w-full rounded-2xl border-slate-800 bg-slate-950 p-4 text-white focus:border-orange-500"
                            />
                        </div>
                        <div class="space-y-2">
                            <label
                                class="ml-2 text-[10px] font-black uppercase tracking-widest text-slate-500"
                                >Meta Description</label
                            >
                            <textarea
                                v-model="form.seo.description"
                                rows="4"
                                class="w-full rounded-2xl border-slate-800 bg-slate-950 p-4 text-white focus:border-orange-500"
                            ></textarea>
                        </div>
                    </div>

                    <div
                        v-if="activeTab === 'features'"
                        class="animate-in fade-in zoom-in-95 space-y-6"
                    >
                        <div class="flex items-center justify-between">
                            <div class="space-y-1">
                                <h3 class="text-sm font-black uppercase tracking-widest text-white">
                                    Дополнительные параметры
                                </h3>
                                <p class="text-[10px] uppercase tracking-tighter text-slate-500">
                                    Любые данные: вес, окрас, прививки и т.д.
                                </p>
                            </div>
                            <button
                                type="button"
                                @click="addFeature"
                                class="flex items-center gap-2 rounded-xl bg-slate-800 px-4 py-2 text-[10px] font-black uppercase tracking-widest text-orange-500 transition-all hover:bg-slate-700"
                            >
                                <PlusIcon class="h-4 w-4" /> Добавить поле
                            </button>
                        </div>

                        <div class="grid grid-cols-1 gap-4">
                            <TransitionGroup name="list">
                                <div
                                    v-for="(value, key) in form.features"
                                    :key="key"
                                    class="group flex items-center gap-4 rounded-2xl border border-slate-800 bg-slate-950/50 p-2 pl-4 transition-all focus-within:border-orange-500/50"
                                >
                                    <input
                                        type="text"
                                        :value="key"
                                        @change="
                                            (e) =>
                                                renameFeatureKey(
                                                    String(key),
                                                    (e.target as HTMLInputElement).value,
                                                )
                                        "
                                        placeholder="Название (н-р: Вес)"
                                        class="w-1/3 border-none bg-transparent p-2 text-[11px] font-bold uppercase tracking-wider text-orange-500 focus:ring-0"
                                    />

                                    <div class="h-6 w-px bg-slate-800"></div>

                                    <input
                                        v-model="form.features[key]"
                                        type="text"
                                        placeholder="Значение"
                                        class="flex-1 border-none bg-transparent p-2 text-sm text-white focus:ring-0"
                                    />

                                    <button
                                        type="button"
                                        @click="removeFeature(String(key))"
                                        class="mr-2 rounded-lg p-2 text-slate-600 opacity-0 transition-all hover:bg-red-500/10 hover:text-red-500 group-hover:opacity-100"
                                        title="Удалить параметр"
                                    >
                                        <TrashIcon class="h-4 w-4" />
                                    </button>
                                </div>
                            </TransitionGroup>

                            <div
                                v-if="Object.keys(form.features).length === 0"
                                class="flex flex-col items-center justify-center rounded-[2rem] border-2 border-dashed border-slate-800 py-12"
                            >
                                <AdjustmentsHorizontalIcon class="h-12 w-12 text-slate-800" />
                                <p
                                    class="mt-4 text-[10px] font-black uppercase tracking-widest text-slate-600"
                                >
                                    Список параметров пуст
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 border-t border-slate-800 pt-8">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="flex-1 rounded-2xl bg-orange-600 py-4 text-xs font-black uppercase tracking-[0.2em] text-white transition-all hover:bg-orange-500 disabled:opacity-50"
                        >
                            {{ editMode ? 'Сохранить изменения' : 'Внести в реестр' }}
                        </button>
                        <button
                            type="button"
                            @click="closeForm"
                            class="px-8 py-4 text-xs font-black uppercase tracking-[0.2em] text-slate-500 transition-colors hover:text-white"
                        >
                            Отмена
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
