<script setup lang="ts">
    import { computed, ref } from 'vue';

    import { Head, router, useForm } from '@inertiajs/vue3';

    import {
        AdjustmentsHorizontalIcon,
        ChevronLeftIcon,
        GlobeAltIcon,
        IdentificationIcon,
        PhotoIcon,
        PlusIcon,
    } from '@heroicons/vue/24/outline';

    import AnimalCard from '@/Components/Admin/Cards/AdminAnimalCard.vue';
    import ImageUpload from '@/Components/UI/ImageUploader.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useFlash } from '@/composables/useFlash';
    import { AdminAnimal, Category, Paginated } from '@/types';

    import FeaturesSection from './FormSections/FeaturesSection.vue';
    import MediaSection from './FormSections/MediaSection.vue';
    import SEOSection from './FormSections/SEOSection.vue';

    const props = defineProps<{
        animals: Paginated<AdminAnimal>;
        categories: Category[];
    }>();

    const { notifyWithUndo, undoRequested } = useFlash();

    const view = ref<'list' | 'form'>('list');
    const activeTab = ref('main');
    const selectedAnimal = ref<AdminAnimal | null>(null);

    const form = useForm({
        _method: 'PUT',
        id: null as number | null,
        name: '',
        category_id: '',
        parent_id: null as number | null,
        status: '',
        bio: '',
        features: {} as Record<string, string>,
        avatar: null as File | null,
        voice: null as File | null,
        gallery: [] as File[],
        seo: {
            title: '',
            description: '',
            keywords: '',
        },
    });

    const openForm = (animal: AdminAnimal | null = null) => {
        if (animal) {
            selectedAnimal.value = animal;
            form.id = animal.id;
            form.name = animal.name;
            form.category_id = String(animal.category_id);
            form.parent_id = animal.parent_id;
            form.status = animal.status || 'active';
            form.bio = animal.bio || '';
            form.features = animal.features || {};
            form.seo = {
                title: animal.seo?.title || '',
                description: animal.seo?.description || '',
                keywords: animal.seo?.keywords || '',
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
        form.post(url, { forceFormData: form.id ? true : false, onSuccess: () => closeForm() });
    };

    const deletingIds = ref<Set<number>>(new Set());

    const deleteAnimal = async (id: number) => {
        if (deletingIds.value.has(id)) return;
        undoRequested.value = true;
        deletingIds.value.add(id);

        try {
            const confirmed = await notifyWithUndo(`Удаление животного в корзину`, 2000);
            if (confirmed) {
                router.delete(route('admin.animals.destroy', id), {
                    preserveScroll: true,
                    onFinish: () => deletingIds.value.delete(id),
                });
            } else {
                deletingIds.value.delete(id);
            }
        } catch (e) {
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
        if (selectedAnimal.value?.avatars && selectedAnimal.value.avatars.length > 0) {
            return selectedAnimal.value.avatars[0].url;
        }
        return null;
    });
</script>

<template>
    <Head title="Реестр животных" />

    <AdminLayout>
        <div class="p-8">
            <header class="mb-12 flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-black uppercase tracking-tighter text-white">
                        Список <span class="text-orange-500">Животных</span>
                    </h1>
                    <p class="mt-2 text-xs font-bold uppercase tracking-widest text-slate-500">
                        {{
                            view === 'list'
                                ? 'Управление базой данных особей'
                                : 'Редактирование профиля'
                        }}
                    </p>
                </div>

                <button
                    v-if="view === 'list'"
                    @click="openForm()"
                    aria-label="Добавить новую особь"
                    class="group flex items-center gap-3 rounded-2xl bg-orange-500 px-6 py-4 transition-all hover:bg-orange-600 active:scale-95"
                >
                    <PlusIcon class="h-5 w-5 text-white" aria-hidden="true" />
                    <span class="text-xs font-black uppercase tracking-widest text-white"
                        >Добавить особь</span
                    >
                </button>

                <button
                    v-else
                    @click="closeForm"
                    aria-label="Вернуться к списку животных"
                    class="group flex items-center gap-3 rounded-2xl bg-slate-800 px-6 py-4 transition-all hover:bg-slate-700"
                >
                    <ChevronLeftIcon class="h-5 w-5 text-slate-400" aria-hidden="true" />
                    <span class="text-xs font-black uppercase tracking-widest text-slate-400"
                        >Назад к списку</span
                    >
                </button>
            </header>

            <main
                v-if="view === 'list'"
                class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3"
                role="region"
                aria-label="Список карточек животных"
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
            </main>

            <div v-else class="mx-auto max-w-5xl">
                <form @submit.prevent="submit" class="space-y-8" novalidate>
                    <nav
                        class="shadow-inner flex gap-2 rounded-[2rem] bg-slate-950 p-2"
                        role="tablist"
                    >
                        <button
                            v-for="tab in tabs"
                            :key="tab.id"
                            :id="`tab-${tab.id}`"
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
                        :id="`panel-${activeTab}`"
                        :aria-labelledby="`tab-${activeTab}`"
                    >
                        <div v-if="activeTab === 'main'" class="space-y-10">
                            <div class="grid grid-cols-1 gap-10 lg:grid-cols-2">
                                <div class="flex flex-col">
                                    <ImageUpload
                                        v-model="form.avatar"
                                        label="Аватар особи"
                                        :existing-image="existingAvatarUrl"
                                        :error="form.errors.avatar"
                                    />
                                </div>

                                <div class="space-y-6">
                                    <div class="group space-y-2">
                                        <label
                                            for="animal_name"
                                            class="ml-2 text-[10px] font-black uppercase tracking-widest text-slate-500"
                                        >
                                            Кличка / Название
                                        </label>
                                        <input
                                            id="animal_name"
                                            v-model="form.name"
                                            type="text"
                                            placeholder="Введите имя..."
                                            :aria-invalid="!!form.errors.name"
                                            aria-describedby="name-error"
                                            class="w-full rounded-2xl border-slate-800 bg-slate-950 text-white focus:border-orange-500 focus:ring-orange-500/20"
                                        />
                                        <p
                                            v-if="form.errors.name"
                                            id="name-error"
                                            role="alert"
                                            class="ml-2 mt-1 text-[10px] font-bold text-red-500"
                                        >
                                            {{ form.errors.name }}
                                        </p>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="group space-y-2">
                                            <label
                                                for="animal_status"
                                                class="ml-2 text-[10px] font-black uppercase tracking-widest text-slate-500"
                                                >Статус</label
                                            >
                                            <input
                                                id="animal_status"
                                                v-model="form.status"
                                                type="text"
                                                placeholder="active, draft..."
                                                class="w-full rounded-2xl border-slate-800 bg-slate-950 text-white focus:border-orange-500 focus:ring-orange-500/20"
                                            />
                                        </div>

                                        <div class="group space-y-2">
                                            <label
                                                for="animal_category"
                                                class="ml-2 text-[10px] font-black uppercase tracking-widest text-slate-500"
                                                >Категория</label
                                            >
                                            <select
                                                id="animal_category"
                                                v-model="form.category_id"
                                                class="w-full rounded-2xl border-slate-800 bg-slate-950 text-white focus:border-orange-500 focus:ring-orange-500/20"
                                            >
                                                <option value="" disabled>
                                                    Выберите категорию
                                                </option>
                                                <option
                                                    v-for="cat in categories"
                                                    :key="cat.id"
                                                    :value="cat.id"
                                                >
                                                    {{ cat.name }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="group space-y-2">
                                        <label
                                            for="animal_parent"
                                            class="ml-2 text-[10px] font-black uppercase tracking-widest text-slate-500"
                                            >Родительская особь</label
                                        >
                                        <select
                                            id="animal_parent"
                                            v-model="form.parent_id"
                                            class="w-full rounded-2xl border-slate-800 bg-slate-950 text-white focus:border-orange-500 focus:ring-orange-500/20"
                                        >
                                            <option :value="null">-- Без родителя --</option>
                                            <option
                                                v-for="a in animals.data"
                                                :key="a.id"
                                                :value="a.id"
                                                v-show="a.id !== form.id"
                                            >
                                                {{ a.name }} (ID: {{ a.id }})
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="group w-full space-y-4">
                                <label
                                    for="animal_bio"
                                    class="ml-2 text-[10px] font-black uppercase tracking-widest text-slate-500"
                                    >Краткая биография</label
                                >
                                <textarea
                                    id="animal_bio"
                                    v-model="form.bio"
                                    rows="3"
                                    placeholder="История, особенности происхождения..."
                                    class="w-full rounded-xl border-slate-800 bg-slate-950 text-white focus:border-orange-500 focus:ring-orange-500/20"
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
                            class="text-xs font-black uppercase tracking-widest text-slate-500 hover:text-white"
                        >
                            Отменить
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-2xl bg-white px-10 py-4 text-xs font-black uppercase tracking-widest text-black transition-all hover:bg-orange-500 hover:text-white disabled:opacity-50"
                        >
                            {{ form.id ? 'Обновить данные' : 'Внести в реестр' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
