<script setup lang="ts">
    import { computed, ref, watch } from 'vue';

    import { useForm } from '@inertiajs/vue3';

    import { CheckIcon } from '@heroicons/vue/24/outline';

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
    import type { AdminAnimal, AnimalFeature, Category, Media } from '@/types';

    const props = defineProps<{
        animal: AdminAnimal | null;
        categories: Category[];
        parents: Array<{ id: number; name: string; category_id: number }>;
        backUrl: string;
    }>();

    const availableParents = computed(() => {
        if (!form.category_id) return props.parents;

        return props.parents.filter((p) => p.category_id === form.category_id && p.id !== form.id);
    });

    const { tabs } = useAdminTabs();
    const activeTab = ref('general');

    const { submitForm } = useAdminForm();
    let attrCounter = 0;
    const makeAttrId = () => `attr-${++attrCounter}`;

    const form = useForm({
        id: props.animal?.id ?? null,
        name: props.animal?.name ?? '',
        category_id: props.animal?.category_id ?? null,
        parent_id: props.animal?.parent_id ?? null,
        status: props.animal?.status ?? '',
        is_active: props.animal?.is_active ?? false,
        is_commentable: props.animal?.is_commentable ?? true,
        bio: props.animal?.bio ?? '',
        features: (props.animal?.features ?? []).map((a) => ({
            ...a,
            id: a.id ?? makeAttrId(),
        })) as AnimalFeature[],
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

    watch(
        () => form.category_id,
        (newCategory) => {
            if (!form.parent_id) return;

            const currentParent = props.parents.find((p) => p.id === form.parent_id);
            if (!currentParent || currentParent.category_id !== newCategory) {
                form.parent_id = null;
            }
        },
    );
</script>

<template>
    <div class="mx-auto max-w-5xl space-y-4 sm:space-y-6">
        <!-- НАЗАД (top) -->
        <BaseCancelButton :href="backUrl" label="Назад" />

        <form @submit.prevent="submit" class="space-y-4 sm:space-y-6 lg:space-y-8" novalidate>
            <!-- TABS -->
            <nav
                class="flex gap-1 overflow-x-auto rounded-2xl border border-rancho-ink-800 bg-rancho-ink-950 p-1.5 sm:gap-2 sm:rounded-3xl sm:p-2"
                role="tablist"
                aria-label="Разделы формы"
            >
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
                            ? 'shadow-lg bg-rancho-mint text-rancho-ink-950 shadow-rancho-mint/20'
                            : 'text-rancho-ink-400 hover:bg-rancho-ink-900 hover:text-rancho-ink-100',
                        'flex shrink-0 items-center justify-center gap-2 rounded-xl px-3 py-2.5 text-[10px] font-black uppercase tracking-widest outline-none transition-all focus-visible:ring-2 focus-visible:ring-rancho-mint/40 sm:flex-1 sm:gap-3 sm:rounded-2xl sm:px-4 sm:py-3.5',
                    ]"
                >
                    <component
                        :is="tab.icon"
                        class="h-4 w-4 shrink-0 sm:h-5 sm:w-5"
                        aria-hidden="true"
                    />
                    <span class="whitespace-nowrap">{{ tab.name }}</span>
                </button>
            </nav>

            <!-- PANEL -->
            <div
                class="rounded-2xl border border-rancho-ink-800 bg-rancho-ink-900/30 px-3 py-4 backdrop-blur-sm sm:rounded-3xl sm:p-6 md:p-8 lg:p-10"
                role="tabpanel"
                aria-live="polite"
            >
                <TransitionGroup name="tab-fade" mode="out-in">
                    <!-- GENERAL -->
                    <div
                        v-if="activeTab === 'general'"
                        class="space-y-6 sm:space-y-8 lg:space-y-10"
                    >
                        <div class="grid grid-cols-1 gap-8 sm:gap-8 lg:grid-cols-2 lg:gap-10">
                            <div class="my-4">
                                <ImageUpload
                                    :model-value="currentFileForUpload"
                                    @update:model-value="handleMainPhotoUpdate"
                                    :existing-image="currentExistingImage"
                                    label="Аватар особи"
                                    class="mx-auto block"
                                    :error="form.errors.avatar"
                                />
                            </div>

                            <div class="space-y-4 sm:space-y-6">
                                <BaseInput
                                    v-model="form.name"
                                    :error="form.errors.name"
                                    label="Имя / Кличка"
                                    placeholder="Напишите имя животного..."
                                    theme="dark"
                                />

                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <BaseInput
                                        v-model="form.status"
                                        :error="form.errors.status"
                                        label="Статус"
                                        placeholder="Создайте статус..."
                                        title="Статус корректируется программно"
                                        theme="dark"
                                    />

                                    <BaseSelect
                                        v-model="form.category_id"
                                        v-model:error="form.errors.category_id"
                                        :options="categories"
                                        placeholder="Выберите категорию"
                                        label="Категория"
                                        variant="admin"
                                    />

                                    <BaseSelect
                                        v-model="form.parent_id"
                                        v-model:error="form.errors.parent_id"
                                        :options="availableParents"
                                        placeholder="Без родителя"
                                        label="Родитель"
                                        variant="admin"
                                        :disabled="
                                            !form.category_id || availableParents.length === 0
                                        "
                                    />
                                </div>

                                <div class="space-y-4">
                                    <BaseSwitch
                                        v-model="form.is_active"
                                        label="Публикация"
                                        active-text="Опубликовать"
                                        inactive-text="В черновик"
                                        :disabled="form.processing"
                                        theme="dark"
                                    />

                                    <BaseSwitch
                                        v-model="form.is_commentable"
                                        label="Комментарии"
                                        active-text="Вкючены"
                                        inactive-text="Отключены"
                                        :disabled="form.processing"
                                        theme="dark"
                                    />
                                </div>
                            </div>
                        </div>

                        <AdminBaseTextarea
                            v-model="form.bio"
                            id="bio"
                            label="Биография"
                            :error="form.errors.bio"
                            :disabled="form.processing"
                            rows="8"
                        />
                    </div>

                    <!-- MEDIA -->
                    <MediaSection
                        v-if="activeTab === 'media'"
                        v-model="form"
                        :existing-voice-url="animal?.voice_url"
                    />

                    <!-- FEATURES -->
                    <FeaturesSection
                        v-if="activeTab === 'features'"
                        :errors="form.errors"
                        v-model:attributes="form.features"
                    />

                    <!-- SEO -->
                    <SEOSection
                        v-if="activeTab === 'seo'"
                        v-model="form.seo"
                        :page-name="form.name"
                        :disabled="form.processing"
                    />
                </TransitionGroup>
            </div>

            <!-- ACTIONS -->
            <div
                class="flex items-stretch justify-between gap-3 border-t border-rancho-ink-800 pt-4 sm:flex-row sm:items-center sm:justify-end sm:gap-4 sm:pt-6"
            >
                <BaseCancelButton :href="backUrl" label="Отменить" />

                <BaseCreateButton
                    type="submit"
                    :label="form.id ? 'Обновить данные' : 'Внести в реестр'"
                    :disabled="form.processing"
                    :theme="'dark'"
                    :icon="CheckIcon"
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

    @media (prefers-reduced-motion: reduce) {
        .tab-fade-enter-active,
        .tab-fade-leave-active {
            transition: none;
        }
    }

    nav[role='tablist']::-webkit-scrollbar {
        height: 0;
        display: none;
    }
    nav[role='tablist'] {
        scrollbar-width: none;
    }
</style>
