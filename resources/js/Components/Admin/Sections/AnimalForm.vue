<script setup lang="ts">
    import { ref } from 'vue';

    import { useForm } from '@inertiajs/vue3';

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
    import { useAdminTabs } from '@/composables/config/useAdminTabs';
    import { useAdminForm } from '@/composables/crud/useAdminForm';
    import { useSingleImagePreview } from '@/composables/features/useMediaUpload';
    import { AdminAnimal, Category, Media } from '@/types';

    const props = defineProps<{
        animal: AdminAnimal | null;
        categories: Category[];
        backUrl: string;
    }>();

    const { tabs } = useAdminTabs();
    const activeTab = ref('general');

    const { submitForm } = useAdminForm();

    const form = useForm({
        id: props.animal?.id ?? null,
        name: props.animal?.name ?? '',
        category_id: props.animal?.category_id ?? null,
        parent_id: props.animal?.parent_id ?? null,
        status: props.animal?.status ?? '',
        is_active: props.animal?.is_active ?? false,
        bio: props.animal?.bio ?? '',
        features: props.animal?.features ?? {},
        avatar: props.animal?.avatars ? [...props.animal.avatars] : ([] as Array<Media | File>),
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
        const id = props.animal?.id ?? null;

        submitForm(form, 'admin.animals', id, {
            hasFiles: !!id,
        });
    };

    const { currentFileForUpload, currentExistingImage } = useSingleImagePreview(() => form.avatar);

    const handleMainPhotoUpdate = (file: File | null) => {
        form.avatar = file ? [file] : (null as any);
    };
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
                <TransitionGroup name="tab-fade" mode="out-in">
                    <div v-if="activeTab === 'general'" class="space-y-10">
                        <div class="grid grid-cols-1 gap-10 lg:grid-cols-2">
                            <ImageUpload
                                :model-value="currentFileForUpload"
                                @update:model-value="handleMainPhotoUpdate"
                                :existing-image="currentExistingImage"
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
                                        v-model="form.status"
                                        :error="form.errors.status"
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

                    <MediaSection
                        v-if="activeTab === 'media'"
                        v-model="form"
                        :existing-voice-url="animal?.voice_url"
                    />

                    <FeaturesSection
                        v-if="activeTab === 'features'"
                        v-model:features="form.features"
                    />

                    <SEOSection
                        v-if="activeTab === 'seo'"
                        v-model="form.seo"
                        :disabled="form.processing"
                    />
                </TransitionGroup>
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

<style scoped>
    .tab-fade-enter-active,
    .tab-fade-leave-active {
        transition: all 0.25s ease;
    }

    .tab-fade-enter-from {
        opacity: 0;
        transform: translateY(8px);
    }

    .tab-fade-leave-to {
        opacity: 0;
        transform: translateY(-8px);
    }
</style>
