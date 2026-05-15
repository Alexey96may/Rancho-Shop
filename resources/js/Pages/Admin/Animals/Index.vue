<script setup lang="ts">
    import { reactive, ref, watch } from 'vue';

    import { router, useForm } from '@inertiajs/vue3';

    import {
        AdjustmentsHorizontalIcon,
        GlobeAltIcon,
        IdentificationIcon,
        PhotoIcon,
    } from '@heroicons/vue/24/outline';

    import AnimalCard from '@/Components/Admin/Cards/AdminAnimalCard.vue';
    import FeaturesSection from '@/Components/Admin/Sections/FeaturesSection.vue';
    import MediaSection from '@/Components/Admin/Sections/MediaSection.vue';
    import SEOSection from '@/Components/Admin/Sections/SEOSection.vue';
    import AdminEmptyState from '@/Components/Admin/Shared/AdminEmptyState.vue';
    import AdminPageHeader from '@/Components/Admin/Shared/AdminPageHeader.vue';
    import AdminPagination from '@/Components/Admin/Shared/AdminPagination.vue';
    import AdminBaseTextarea from '@/Components/Admin/UI/AdminBaseTextarea.vue';
    import AdminLoader from '@/Components/Admin/UI/AdminLoader.vue';
    import AdminSearchInput from '@/Components/Admin/UI/AdminSearchInput.vue';
    import BaseCancelButton from '@/Components/UI/BaseCancelButton.vue';
    import BaseCreateButton from '@/Components/UI/BaseCreateButton.vue';
    import BaseInput from '@/Components/UI/BaseInput.vue';
    import BaseSelect from '@/Components/UI/BaseSelect.vue';
    import BaseSwitch from '@/Components/UI/BaseSwitch.vue';
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

    const isFiltering = ref(false);

    const view = ref<'list' | 'form'>('list');
    const activeTab = ref('main');
    const selectedAnimal = ref<AdminAnimal | null>(null);

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
        isFiltering.value = true;

        router.get(
            route('admin.animals.index'),
            { search: val, category_id: cat },
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

    watch(categoryFilter, (newVal) => {
        performSearch(search.value, newVal);
    });

    const clearFilters = () => {
        search.value = '';
        categoryFilter.value = '';
        performSearch('', '');

        router.get(
            route('admin.animals.index'),
            {},
            {
                preserveScroll: true,
            },
        );
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

    const deletingIds = reactive<Set<number>>(new Set());

    const deleteAnimal = async (id: number) => {
        if (deletingIds.has(id)) return;
        deletingIds.add(id);
        const confirmed = await notifyWithUndo(`Удаление животного`, 4000);
        if (confirmed) {
            router.delete(route('admin.animals.destroy', id), {
                preserveScroll: true,
                onFinish: () => deletingIds.delete(id),
            });
        } else {
            deletingIds.delete(id);
        }
    };

    const tabs = [
        { id: 'main', name: 'Основное', icon: IdentificationIcon },
        { id: 'features', name: 'Параметры', icon: AdjustmentsHorizontalIcon },
        { id: 'media', name: 'Медиа', icon: PhotoIcon },
        { id: 'seo', name: 'SEO', icon: GlobeAltIcon },
    ];
</script>

<template>
    <Teleport to="#admin-header-content">
        <AdminPageHeader title="Список Животных" subtitle="Управление базой животных" />
    </Teleport>

    <div class="p-8">
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

                <BaseCreateButton label="Добавить" @click="openForm()" />
            </section>

            <div class="relative min-h-[400px]">
                <Transition name="fade-slide">
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
                            :disabled="deletingIds.has(animal.id)"
                            :class="{
                                'pointer-events-none scale-95 opacity-50': deletingIds.has(
                                    animal.id,
                                ),
                            }"
                            @edit="openForm(animal)"
                            @delete="deleteAnimal(animal.id)"
                        />
                    </TransitionGroup>

                    <AdminLoader v-else-if="isFiltering" key="loading" text="Синхронизация" />

                    <AdminEmptyState
                        v-else
                        key="empty"
                        title="Животные не найдены"
                        @action="clearFilters"
                        :show-action="true"
                    />
                </Transition>
            </div>

            <Transition name="fade-slide" mode="out-in">
                <AdminPagination v-if="!isFiltering" :links="animals.meta.links"
            /></Transition>
        </div>

        <div v-else class="mx-auto max-w-5xl">
            <BaseCancelButton @click="closeForm" label="Назад" />

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
                                :error="form.errors.avatar"
                            />

                            <div class="space-y-6">
                                <BaseInput
                                    v-model="form.name"
                                    v-model:error="form.errors.name"
                                    label="Имя / Кличка"
                                    placeholder="Напишите имя животного..."
                                />

                                <div class="grid grid-cols-2 gap-4">
                                    <BaseInput
                                        v-model="form.name"
                                        v-model:error="form.errors.name"
                                        label="Статус"
                                        placeholder="Создайте статус животному..."
                                        title="Статус корректируется программно"
                                    />

                                    <BaseSelect
                                        v-model="form.category_id"
                                        :options="categories"
                                        placeholder="Все категории"
                                        label="Категория"
                                        variant="admin"
                                    />
                                </div>

                                <BaseSwitch
                                    v-model="form.is_active"
                                    label="Публикация"
                                    active-text="Опубликовать"
                                    inactive-text="В черновик"
                                    :disabled="form.processing"
                                />
                            </div>
                        </div>

                        <AdminBaseTextarea
                            v-model="form.bio"
                            id="seoDescr"
                            label="Биография"
                            :error="form.errors?.bio"
                            :disabled="deletingIds.has(Number(form.id))"
                            rows="8"
                        />
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

                    <SEOSection
                        v-if="activeTab === 'seo'"
                        v-model="form.seo"
                        :disabled="deletingIds.has(Number(form.id))"
                    />
                </div>

                <div class="flex items-center justify-end gap-4 border-t border-slate-800">
                    <BaseCancelButton @click="closeForm" label="Отменить" />

                    <BaseCreateButton
                        type="submit"
                        :label="form.id ? 'Обновить данные' : 'Внести в реестр'"
                        :disabled="form.processing"
                        class="mt-8"
                    />
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
