<script setup lang="ts">
    import { ref } from 'vue';

    import { router, useForm } from '@inertiajs/vue3';

    import {
        AdjustmentsHorizontalIcon,
        GlobeAltIcon,
        IdentificationIcon,
        PhotoIcon,
    } from '@heroicons/vue/24/outline';

    import FeaturesSection from '@/Components/Admin/Sections/FeaturesSection.vue';
    import MediaSection from '@/Components/Admin/Sections/MediaSection.vue';
    import SEOSection from '@/Components/Admin/Sections/SEOSection.vue';
    import AdminBaseTextarea from '@/Components/Admin/UI/AdminBaseTextarea.vue';
    import BaseCancelButton from '@/Components/UI/BaseCancelButton.vue';
    import BaseCreateButton from '@/Components/UI/BaseCreateButton.vue';
    import BaseInput from '@/Components/UI/BaseInput.vue';
    import BaseSelect from '@/Components/UI/BaseSelect.vue';
    import BaseSwitch from '@/Components/UI/BaseSwitch.vue';
    import ImageUpload from '@/Components/UI/ImageUploader.vue';
    import { useFlash } from '@/composables/useFlash';
    import { AdminAnimal, Category, ResourceSingle } from '@/types';

    const props = defineProps<{
        animal: AdminAnimal | null;
        categories: Category[];
        backUrl: string;
    }>();

    const { notify } = useFlash();
    const activeTab = ref('main');

    const form = useForm({
        _method: 'PUT',
        id: props.animal?.id ?? null,
        name: props.animal?.name ?? '',
        category_id: props.animal?.category_id ?? null,
        parent_id: props.animal?.parent_id ?? null,
        status: props.animal?.status ?? 'active',
        is_active: props.animal?.is_active ?? false,
        bio: props.animal?.bio ?? '',
        features: props.animal?.features ?? {},
        avatar: null as File | null,
        voice: null as File | null,
        gallery: props.animal?.gallery ?? [],
        seo: {
            title: props.animal?.seo?.title ?? '',
            description: props.animal?.seo?.description ?? '',
            keywords: props.animal?.seo?.keywords ?? '',
            canonical: props.animal?.seo?.canonical ?? '',
            is_noindex: props.animal?.seo?.is_noindex ?? false,
        },
        backUrl: props.backUrl,
    });

    const submit = () => {
        const url = form.id ? route('admin.animals.update', form.id) : route('admin.animals.store');
        form.post(url, {
            forceFormData: true,
            onSuccess: () => router.get(route('admin.animals.index')),
            onError: () => notify('Проверьте поля формы', 'error'),
        });
    };

    const tabs = [
        { id: 'main', name: 'Основное', icon: IdentificationIcon },
        { id: 'features', name: 'Параметры', icon: AdjustmentsHorizontalIcon },
        { id: 'media', name: 'Медиа', icon: PhotoIcon },
        { id: 'seo', name: 'SEO', icon: GlobeAltIcon },
    ];
</script>

<template>
    <div class="mx-auto max-w-5xl">
        <BaseCancelButton :href="backUrl" label="Назад" />

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
                                :error="form.errors.name"
                                label="Имя / Кличка"
                                placeholder="Напишите имя животного..."
                            />

                            <div class="grid grid-cols-2 gap-4">
                                <BaseInput
                                    v-model="form.name"
                                    :error="form.errors.name"
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
                        :error="form.errors.bio"
                        :disabled="form.processing"
                        rows="8"
                    />
                </div>

                <FeaturesSection v-if="activeTab === 'features'" v-model:features="form.features" />

                <MediaSection
                    v-if="activeTab === 'media'"
                    v-model="form"
                    :existing-voice-url="animal?.voice_url"
                />

                <SEOSection
                    v-if="activeTab === 'seo'"
                    v-model="form.seo"
                    :disabled="form.processing"
                />
            </div>

            <div class="flex items-center justify-end gap-4 border-t border-slate-800">
                <BaseCancelButton :href="backUrl" label="Отменить" />

                <BaseCreateButton
                    type="submit"
                    :label="form.id ? 'Обновить данные' : 'Внести в реестр'"
                    :disabled="form.processing"
                    class="mt-8"
                />
            </div>
        </form>
    </div>
</template>
