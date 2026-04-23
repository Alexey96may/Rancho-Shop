<script setup lang="ts">
    import { computed, ref, watch } from 'vue';

    import { Head, router, useForm } from '@inertiajs/vue3';

    import { Switch } from '@headlessui/vue';
    import {
        AdjustmentsHorizontalIcon,
        ChevronLeftIcon,
        GlobeAltIcon,
        IdentificationIcon,
        InboxIcon,
        // Для пустого состояния
        PhotoIcon,
        PlusIcon,
    } from '@heroicons/vue/24/outline';

    import AnimalCard from '@/Components/Admin/Cards/AdminAnimalCard.vue';
    import FeaturesSection from '@/Components/Admin/Sections/FeaturesSection.vue';
    import MediaSection from '@/Components/Admin/Sections/MediaSection.vue';
    import SEOSection from '@/Components/Admin/Sections/SEOSection.vue';
    import AdminPageHeader from '@/Components/Admin/Shared/AdminPageHeader.vue';
    import AdminPagination from '@/Components/Admin/Shared/AdminPagination.vue';
    import AdminSearchInput from '@/Components/Admin/UI/AdminSearchInput.vue';
    // Твой исправленный инпут
    import BaseSelect from '@/Components/UI/BaseSelect.vue';
    import ImageUpload from '@/Components/UI/ImageUploader.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useFlash } from '@/composables/useFlash';
    import { AdminAnimal, Category, Media, Paginated, SeoData } from '@/types';

    const props = defineProps<{
        animals: Paginated<AdminAnimal>;
        categories: Category[];
        filters: { search?: string; category_id?: string | number };
        seo: SeoData;
    }>();

    defineOptions({ layout: AdminLayout });

    const { notifyWithUndo, notify } = useFlash();

    // Состояние страницы
    const view = ref<'list' | 'form'>('list');
    const activeTab = ref('main');
    const selectedAnimal = ref<AdminAnimal | null>(null);

    // Фильтры
    const search = ref(props.filters.search || '');
    const categoryFilter = ref(props.filters.category_id || '');

    const form = useForm({
        _method: 'PUT',
        id: null as number | null,
        name: '',
        category_id: null as number | null,
        parent_id: null as number | null,
        status: '',
        is_active: false,
        bio: '',
        features: {} as Record<string, string>,
        avatar: null as File | null,
        voice: null as File | null,
        gallery: [] as Array<Media | File>,
        seo: {
            title: '',
            description: '',
            keywords: '',
            canonical: '',
            is_noindex: false,
        },
    });

    const performSearch = (val: string = search.value, cat: any = categoryFilter.value) => {
        router.get(
            route('admin.animals.index'),
            { search: val, category_id: cat },
            { preserveState: true, replace: true, preserveScroll: true },
        );
    };

    watch(categoryFilter, (newVal) => performSearch(search.value, newVal));

    const clearFilters = () => {
        search.value = '';
        categoryFilter.value = '';
        performSearch('', '');

        router.get(
            route('admin.animals.index'),
            {},
            {
                onBefore: () => {
                    // Можно добавить лоадер
                },
                preserveScroll: true,
            },
        );
        notify('Фильтры очищены', 'info');
    };

    const openForm = (animal: AdminAnimal | null = null) => {
        form.clearErrors();
        if (animal) {
            selectedAnimal.value = animal;
            form.id = animal.id;
            form.name = animal.name;
            form.category_id = animal.category_id;
            form.parent_id = animal.parent_id;
            form.status = animal.status || 'active';
            form.is_active = animal.is_active || false;
            form.bio = animal.bio || '';
            form.features = animal.features || {};
            form.gallery = animal.gallery || [];
            form.voice = null;
            form.seo = {
                title: animal.seo?.title || '',
                description: animal.seo?.description || '',
                keywords: animal.seo?.keywords || '',
                canonical: animal.seo?.canonical || '',
                is_noindex: animal.seo?.is_noindex || false,
            };
        } else {
            selectedAnimal.value = null;
            form.reset();
        }
        view.value = 'form';
    };

    const closeForm = () => {
        view.value = 'list';
        selectedAnimal.value = null;
        form.reset();
    };

    const submit = () => {
        const url = form.id ? route('admin.animals.update', form.id) : route('admin.animals.store');
        form.post(url, {
            forceFormData: true,
            onSuccess: () => closeForm(),
            onError: () => notify('Проверьте поля формы', 'error'),
        });
    };

    const deletingIds = ref<Set<number>>(new Set());
    const deleteAnimal = async (id: number) => {
        if (deletingIds.value.has(id)) return;
        deletingIds.value.add(id);
        const confirmed = await notifyWithUndo(`Удаление животного`, 4000);
        if (confirmed) {
            router.delete(route('admin.animals.destroy', id), {
                preserveScroll: true,
                onFinish: () => deletingIds.value.delete(id),
            });
        } else {
            deletingIds.value.delete(id);
        }
    };

    const tabs = [
        { id: 'main', name: 'Основное', icon: IdentificationIcon },
        { id: 'features', name: 'Параметры', icon: AdjustmentsHorizontalIcon },
        { id: 'media', name: 'Медиа', icon: PhotoIcon },
        { id: 'seo', name: 'SEO', icon: GlobeAltIcon },
    ];

    const existingAvatarUrl = computed(() => {
        return selectedAnimal.value?.avatars?.[0]?.url || null;
    });
</script>

<template>
    <Head title="Реестр животных" />

    <div class="p-8">
        <AdminPageHeader
            title-normal="Список"
            title-orange="Животных"
            subtitle="Управление базой животных"
            @action="openForm()"
        >
            <template #actions>
                <button
                    v-if="view === 'list'"
                    @click="openForm()"
                    aria-label="Добавить новую особь"
                    class="group flex items-center gap-3 rounded-xl bg-orange-500 px-6 py-4 transition-all hover:bg-orange-600 active:scale-95"
                >
                    <PlusIcon class="h-5 w-5 text-white" aria-hidden="true" />
                    <span class="text-xs font-black uppercase tracking-widest text-white"
                        >Добавить</span
                    >
                </button>

                <button
                    v-else
                    @click="closeForm"
                    aria-label="Вернуться к списку"
                    class="group flex items-center gap-3 rounded-xl bg-slate-800 px-6 py-4 transition-all hover:bg-slate-700"
                >
                    <ChevronLeftIcon class="h-5 w-5 text-slate-400" aria-hidden="true" />
                    <span class="text-xs font-black uppercase tracking-widest text-slate-400"
                        >Назад</span
                    >
                </button>
            </template>
        </AdminPageHeader>

        <div v-if="view === 'list'" class="space-y-10">
            <section class="flex flex-col gap-4 lg:flex-row lg:items-end" aria-label="Фильтрация">
                <AdminSearchInput
                    v-model="search"
                    @search="performSearch"
                    placeholder="Поиск по имени..."
                    label="Поиск животных"
                />

                <BaseSelect
                    v-model="categoryFilter"
                    :options="categories"
                    placeholder="Все категории"
                    variant="admin"
                    class="lg:w-64"
                />
            </section>

            <div class="relative min-h-[400px]">
                <TransitionGroup
                    v-if="animals.data.length > 0"
                    :key="animals.data.length"
                    tag="main"
                    name="animal-grid"
                    class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3"
                    role="list"
                >
                    <AnimalCard
                        v-for="animal in animals.data"
                        :key="animal.id"
                        :animal="animal"
                        :class="{
                            'pointer-events-none scale-95 opacity-50': deletingIds.has(animal.id),
                        }"
                        @edit="openForm(animal)"
                        @delete="deleteAnimal(animal.id)"
                    />
                </TransitionGroup>

                <Transition name="fade-slide">
                    <div
                        v-if="animals.data.length === 0"
                        class="flex flex-col items-center justify-center rounded-[3rem] border border-dashed border-slate-800 bg-slate-900/10 py-24 text-center"
                        role="alert"
                    >
                        <div class="shadow-inner rounded-full bg-slate-900 p-6 text-slate-700">
                            <InboxIcon class="h-12 w-12" />
                        </div>
                        <h3 class="mt-6 text-xl font-bold text-white">Животные не найдены</h3>
                        <p class="mt-2 text-sm text-slate-500">
                            По вашему запросу ничего не нашлось в реестре
                        </p>
                        <button
                            @click="clearFilters"
                            class="hover:orange-400 mt-8 text-xs font-black uppercase tracking-widest text-orange-500"
                        >
                            Сбросить фильтры
                        </button>
                    </div>
                </Transition>
            </div>

            <AdminPagination :links="animals.meta.links" />
        </div>

        <div v-else class="mx-auto max-w-5xl">
            <form @submit.prevent="submit" class="space-y-8" novalidate>
                <nav class="shadow-inner flex gap-2 rounded-[2rem] bg-slate-950 p-2" role="tablist">
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        type="button"
                        role="tab"
                        :aria-selected="activeTab === tab.id"
                        :aria-controls="`panel-${tab.id}`"
                        @click="activeTab = tab.id"
                        :class="[
                            activeTab === tab.id
                                ? 'shadow-lg bg-orange-500 text-white'
                                : 'text-slate-500 hover:text-slate-300',
                            'flex flex-1 items-center justify-center gap-3 rounded-[1.5rem] py-4 transition-all',
                        ]"
                    >
                        <component :is="tab.icon" class="h-5 w-5" aria-hidden="true" />
                        <span class="text-[10px] font-black uppercase tracking-widest">{{
                            tab.name
                        }}</span>
                    </button>
                </nav>

                <div
                    class="min-h-[400px] rounded-[3rem] border border-slate-800 bg-slate-900/30 p-10 backdrop-blur-sm"
                    role="tabpanel"
                    aria-live="polite"
                >
                    <div v-if="activeTab === 'main'" class="space-y-10">
                        <div class="grid grid-cols-1 gap-10 lg:grid-cols-2">
                            <ImageUpload
                                v-model="form.avatar"
                                label="Аватар особи"
                                :existing-image="existingAvatarUrl"
                                :error="form.errors.avatar"
                            />

                            <div class="space-y-6">
                                <div class="space-y-2">
                                    <label
                                        for="animal_name"
                                        class="ml-2 text-[10px] font-black uppercase tracking-widest text-slate-500"
                                        >Кличка / Название</label
                                    >
                                    <input
                                        id="animal_name"
                                        v-model="form.name"
                                        type="text"
                                        required
                                        :class="[
                                            'w-full rounded-xl border bg-slate-950 px-4 py-3 text-white transition-colors',
                                            form.errors.name
                                                ? 'border-red-500'
                                                : 'border-slate-800 focus:border-orange-500',
                                        ]"
                                    />
                                    <p
                                        v-if="form.errors.name"
                                        class="ml-2 text-[10px] font-bold text-red-500"
                                        role="alert"
                                    >
                                        {{ form.errors.name }}
                                    </p>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <label
                                            class="ml-2 text-[10px] font-black uppercase tracking-widest text-slate-500"
                                            >Статус</label
                                        >
                                        <input
                                            v-model="form.status"
                                            type="text"
                                            placeholder="Дома..."
                                            class="w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-3 text-white focus:border-orange-500"
                                        />
                                    </div>
                                    <div class="space-y-2">
                                        <label
                                            class="ml-2 text-[10px] font-black uppercase tracking-widest text-slate-500"
                                            >Категория</label
                                        >
                                        <BaseSelect
                                            v-model="form.category_id"
                                            :options="categories"
                                            variant="admin"
                                        />
                                    </div>
                                </div>

                                <div class="flex items-center gap-4">
                                    <label
                                        class="ml-2 text-[10px] font-black uppercase tracking-widest text-slate-500"
                                        >Публикация</label
                                    >
                                    <Switch
                                        v-model="form.is_active"
                                        :class="form.is_active ? 'bg-emerald-700' : 'bg-slate-800'"
                                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors"
                                    >
                                        <span
                                            :class="
                                                form.is_active ? 'translate-x-6' : 'translate-x-1'
                                            "
                                            class="inline-block h-4 w-4 transform rounded-full bg-white transition"
                                        />
                                    </Switch>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label
                                class="ml-2 text-[10px] font-black uppercase tracking-widest text-slate-500"
                                >Биография</label
                            >
                            <textarea
                                v-model="form.bio"
                                rows="4"
                                class="w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-3 text-white focus:border-orange-500"
                            ></textarea>
                        </div>
                    </div>

                    <FeaturesSection
                        v-if="activeTab === 'features'"
                        v-model:features="form.features"
                    />
                    <MediaSection
                        v-if="activeTab === 'media'"
                        v-model="form"
                        :existing-voice-url="selectedAnimal?.voice_url"
                    />
                    <SEOSection v-if="activeTab === 'seo'" v-model="form.seo" />
                </div>

                <div class="flex items-center justify-end gap-4 border-t border-slate-800 pt-8">
                    <button
                        type="button"
                        @click="closeForm"
                        class="text-xs font-black uppercase tracking-widest text-slate-500 transition-colors hover:text-white"
                    >
                        Отменить
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-xl bg-white px-10 py-4 text-xs font-black uppercase tracking-widest text-black transition-all hover:bg-orange-500 hover:text-white disabled:opacity-50"
                    >
                        {{ form.id ? 'Обновить данные' : 'Внести в реестр' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<style scoped>
    .animal-grid-enter-active,
    .animal-grid-leave-active {
        transition: all 0.5s ease-out;
    }
    .animal-grid-enter-from {
        opacity: 0;
        transform: translateY(30px) scale(0.9);
    }
    .animal-grid-leave-to {
        opacity: 0;
        transform: scale(0.8);
    }
    .animal-grid-move {
        transition: transform 0.5s ease;
    }
    .animal-grid-leave-active {
        position: absolute;
        width: 100%;
    }

    .fade-slide-enter-active {
        transition: all 0.4s ease-out;
    }
    .fade-slide-enter-from {
        opacity: 0;
        transform: translateY(-10px);
    }
</style>
