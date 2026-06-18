<script setup lang="ts">
    import { PropType, computed, ref } from 'vue';

    import { useForm } from '@inertiajs/vue3';

    import { PhotoIcon } from '@heroicons/vue/24/outline';

    import FeaturesSection from '@/Components/Admin/Sections/FeaturesSection.vue';
    import SEOSection from '@/Components/Admin/Sections/SEOSection.vue';
    import AdminPageHeader from '@/Components/Admin/Shared/AdminPageHeader.vue';
    import AdminBaseTextarea from '@/Components/Admin/UI/AdminBaseTextarea.vue';
    import MediaGallery from '@/Components/Shared/MediaGallery.vue';
    import BaseCancelButton from '@/Components/UI/BaseCancelButton.vue';
    import BaseCreateButton from '@/Components/UI/BaseCreateButton.vue';
    import BaseDeleteButton from '@/Components/UI/BaseDeleteButton.vue';
    import BaseInput from '@/Components/UI/BaseInput.vue';
    import BaseModal from '@/Components/UI/BaseModal.vue';
    import BaseSelect from '@/Components/UI/BaseSelect.vue';
    import BaseSwitch from '@/Components/UI/BaseSwitch.vue';
    import ImageUpload from '@/Components/UI/ImageUploader.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useAdminTabs } from '@/composables/config/useAdminTabs';
    import { useAdminCrud } from '@/composables/crud/useAdminCrud';
    import { useAdminForm } from '@/composables/crud/useAdminForm';
    import type { GalleryItem } from '@/composables/features/useMediaUpload';
    import { useMediaUpload, useSingleImagePreview } from '@/composables/features/useMediaUpload';
    import type {
        AdminProduct,
        Animal,
        AvailabilityType,
        Category,
        Media,
        ResourceSingle,
    } from '@/types';
    import { formatDateTime, formatRelativeTime } from '@/utils/format';

    defineOptions({ layout: AdminLayout });

    const props = defineProps({
        product: {
            type: Object as PropType<ResourceSingle<AdminProduct> | null>,
            required: false,
            default: null,
            validator: (value: unknown): boolean => {
                if (value === undefined || value === null) return true;

                const val = value as Record<string, unknown>;
                const hasData = !!(val?.data && typeof val.data === 'object' && val.data !== null);

                if (!hasData) {
                    console.warn(
                        'Runtime Error: The "product" prop is provided but missing a valid "data" object.',
                    );
                }
                return hasData;
            },
        },
        categories: {
            type: Object as PropType<{ data: Category[] }>,
            required: true,
            validator: (value: unknown): boolean => {
                const val = value as Record<string, unknown>;
                const hasDataArray = Array.isArray(val?.data);

                if (!hasDataArray) {
                    console.warn(
                        'Runtime Error: The "categories" prop must contain a "data" array.',
                    );
                }
                return hasDataArray;
            },
        },
        animals: {
            type: Object as PropType<{ data: Animal[] }>,
            required: true,
            validator: (value: unknown): boolean => {
                const val = value as Record<string, unknown>;
                const hasDataArray = Array.isArray(val?.data);

                if (!hasDataArray) {
                    console.warn('Runtime Error: The "animals" prop must contain a "data" array.');
                }
                return hasDataArray;
            },
        },
        backUrl: {
            type: String,
            required: true,
        },
    });

    const isEdit = computed(() => !!props.product);

    const activeTab = ref('general');
    const { tabs } = useAdminTabs();

    const form = useForm({
        name: props.product?.data?.name ?? '',
        slug: props.product?.data?.slug ?? '',
        description: props.product?.data?.description ?? '',
        category_id: props.product?.data?.category_id ?? null,
        availability_type: props.product?.data?.availability.value ?? ('stock' as AvailabilityType),
        is_active: props.product?.data?.is_active ?? true,
        attributes: props.product?.data?.attributes ?? {},
        animal_ids: props.product?.data?.animals?.map((a) => a.id) ?? [],
        // Spatie Media
        main_photo: props.product?.data?.main_photo
            ? [...props.product.data?.main_photo]
            : ([] as Array<Media | File>),
        gallery: props.product?.data?.gallery
            ? [...props.product.data?.gallery]
            : ([] as Array<Media | File>),
        remove_media: [] as number[],
        // Seo
        seo: {
            title: props.product?.data.seo?.title || '',
            description: props.product?.data.seo?.description || '',
            keywords: props.product?.data.seo?.keywords || '',
            canonical: props.product?.data.seo?.canonical || '',
            is_noindex: props.product?.data.seo?.is_noindex || false,
        },
        backUrl: props.backUrl,
        voice: null,
    });

    const { getPreview, handleFile, removeGalleryItem } = useMediaUpload(form);
    const { currentFileForUpload, currentExistingImage } = useSingleImagePreview(
        () => form.main_photo,
    );

    const showExactDate = ref(false);
    const toggleDate = () => {
        showExactDate.value = !showExactDate.value;
    };

    const { submitForm } = useAdminForm();
    const { deleteEntity, isDeleting } = useAdminCrud();

    const submit = () => {
        const id = isEdit.value && props.product ? props.product.data.id : null;
        submitForm(form, 'admin.products', id);
    };

    const deletePage = async () => {
        if (!isEdit.value || !props.product?.data.can_delete) return;

        deleteEntity(
            'admin.products',
            props.product.data.id,
            `Удаление товара «${props.product.data.name}»`,
        );
    };

    const selectedImage = ref<string | null>(null);

    const isModalOpen = computed({
        get: () => !!selectedImage.value,
        set: (value) => {
            if (!value) selectedImage.value = null;
        },
    });

    const availabilityOptions = [
        { value: 'stock', label: 'В наличии' },
        { value: 'daily', label: 'По графику' },
        { value: 'preorder', label: 'Предзаказ' },
    ];

    const openImage = (item: GalleryItem) => {
        const previewUrl = getPreview(item);
        if (previewUrl) {
            selectedImage.value = previewUrl;
        }
    };

    const handleMainPhotoUpdate = (file: File | null) => {
        form.main_photo = file ? [file] : (null as any);
    };
</script>

<template>
    <Teleport to="#admin-header-content">
        <AdminPageHeader
            :title="isEdit ? 'Редактирование товара' : 'Новый товар'"
            :subtitle="form.name"
        />
    </Teleport>

    <div class="space-y-6">
        <BaseCancelButton :href="backUrl" label="Назад" />

        <nav class="flex gap-2 border-b border-slate-800 pb-px" role="tablist">
            <button
                v-for="tab in tabs"
                :key="tab.id"
                @click="activeTab = tab.id"
                role="tab"
                :aria-selected="activeTab === tab.id"
                class="flex items-center gap-2 px-6 py-4 text-xs font-black uppercase tracking-widest transition-all"
                :class="
                    activeTab === tab.id
                        ? 'border-b-2 border-emerald-600 text-white'
                        : 'text-slate-500 hover:text-slate-300'
                "
            >
                <component :is="tab.icon" class="h-4 w-4" />
                {{ tab.name }}
            </button>
        </nav>

        <form @submit.prevent="submit" class="grid grid-cols-1 gap-8 xl:grid-cols-12">
            <div class="xl:col-span-8">
                <div
                    v-show="activeTab === 'general'"
                    class="space-y-8 rounded-3xl border border-slate-800 bg-slate-900/50 p-6 md:p-8"
                >
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <ImageUpload
                            :model-value="currentFileForUpload"
                            :existing-image="currentExistingImage"
                            @update:model-value="handleMainPhotoUpdate"
                            label="Главное фото товара"
                            :error="form.errors.main_photo"
                        />

                        <BaseInput
                            v-model="form.name"
                            v-model:error="form.errors.name"
                            label="Название товара"
                            placeholder="Напр: Молоко козье пастеризованное"
                            required
                        />

                        <BaseInput
                            v-model="form.slug"
                            v-model:error="form.errors.slug"
                            label="URL Slug (оставьте пустым для авто)"
                            placeholder="moloko-kozie"
                        />

                        <BaseSelect
                            v-model="form.category_id"
                            v-model:error="form.errors.category_id"
                            :options="categories.data"
                            label="Категория"
                            placeholder="Выберите категорию"
                            variant="admin"
                        />

                        <BaseSelect
                            v-model="form.availability_type"
                            v-model:error="form.errors.availability_type"
                            :options="availabilityOptions"
                            valueKey="value"
                            labelKey="label"
                            label="Тип доступности"
                            variant="admin"
                        />
                    </div>

                    <AdminBaseTextarea
                        v-model="form.description"
                        v-model:error="form.errors.description"
                        label="Описание товара"
                        placeholder="Опишите преимущества, вкус и особенности..."
                        rows="6"
                    />

                    <div>
                        <label
                            class="mb-3 block text-[10px] font-black uppercase tracking-widest text-slate-500"
                        >
                            Источник (Животные)
                        </label>
                        <div class="flex flex-wrap gap-3">
                            <label
                                v-for="animal in animals.data"
                                :key="animal.id"
                                class="flex cursor-pointer items-center gap-2 rounded-2xl border px-4 py-2 transition-all"
                                :class="
                                    form.animal_ids.includes(animal.id)
                                        ? 'border-emerald-600 bg-emerald-600/10 text-white'
                                        : 'border-slate-800 text-slate-500 hover:border-slate-700'
                                "
                            >
                                <input
                                    type="checkbox"
                                    :value="animal.id"
                                    v-model="form.animal_ids"
                                    class="hidden"
                                />
                                <span class="text-xs font-bold">{{ animal.name }}</span>
                            </label>
                        </div>
                    </div>
                </div>

                <MediaGallery
                    v-show="activeTab === 'media'"
                    v-model="form.gallery"
                    @remove="removeGalleryItem"
                    @preview="openImage"
                    ><label
                        class="relative flex aspect-square cursor-pointer flex-col items-center justify-center rounded-[2rem] border-2 border-dashed border-slate-800 bg-slate-950/20 transition-all hover:border-orange-500/50 hover:bg-slate-950/40"
                    >
                        <input
                            type="file"
                            multiple
                            accept="image/*"
                            @change="handleFile($event, 'gallery')"
                            class="sr-only"
                        />
                        <PhotoIcon class="mb-2 h-8 w-8 text-slate-700" />
                        <span
                            class="text-[10px] font-black uppercase tracking-widest text-slate-500"
                            >Добавить</span
                        >
                    </label>
                </MediaGallery>

                <FeaturesSection
                    v-if="activeTab === 'features'"
                    v-model:features="form.attributes"
                />

                <SEOSection
                    v-if="activeTab === 'seo'"
                    v-model="form.seo"
                    :disabled="form.processing"
                />
            </div>

            <aside class="xl:col-span-4">
                <div class="sticky top-6 space-y-6">
                    <div class="rounded-3xl border border-slate-800 bg-slate-900/50 p-6">
                        <BaseSwitch
                            v-model="form.is_active"
                            label="Статус публикации"
                            active-text="Опубликован"
                            inactive-text="Черновик"
                        />

                        <hr class="my-6 border-slate-800" />

                        <div class="space-y-4">
                            <div
                                class="flex justify-between text-[10px] font-black uppercase tracking-widest"
                            >
                                <span class="text-slate-500">Создан:</span>
                                <Transition name="fade-date" mode="out-in">
                                    <time
                                        :key="showExactDate.toString()"
                                        :datetime="product?.data.created_at"
                                        :title="
                                            showExactDate
                                                ? 'Нажмите, чтобы увидеть время назад'
                                                : 'Нажмите, чтобы увидеть точную дату'
                                        "
                                        @click="toggleDate"
                                        class="cursor-pointer select-none text-[10px] font-medium text-slate-500 transition-colors hover:text-slate-300"
                                    >
                                        {{
                                            showExactDate
                                                ? formatDateTime(product?.data.created_at || null)
                                                : formatRelativeTime(
                                                      product?.data.created_at || null,
                                                  )
                                        }}
                                    </time>
                                </Transition>
                            </div>
                            <div
                                class="flex justify-between text-[10px] font-black uppercase tracking-widest"
                            >
                                <span class="text-slate-500">ID товара:</span>
                                <span class="text-slate-300">{{
                                    product?.data?.id ?? 'Новый'
                                }}</span>
                            </div>
                        </div>

                        <BaseCreateButton
                            type="submit"
                            :label="isEdit ? 'Обновить данные' : 'Создать товар'"
                            :disabled="form.processing"
                            class="mt-8 w-full"
                        />
                    </div>

                    <div
                        v-if="isEdit"
                        class="rounded-3xl border border-red-900/30 bg-red-900/10 p-6"
                    >
                        <h4 class="text-[10px] font-black uppercase tracking-widest text-red-500">
                            Опасная зона
                        </h4>
                        <p class="mt-2 text-xs text-red-900/70">
                            Удаление продукта приведет к его перемещению в корзину.
                        </p>

                        <BaseDeleteButton
                            v-if="product?.data.can_delete"
                            :disabled="isDeleting(product?.data.id)"
                            @confirm="deletePage"
                            ><span v-if="isDeleting(product?.data.id)">Удаление...</span>
                            <span v-else>Удалить продукт</span>
                        </BaseDeleteButton>

                        <div
                            v-else
                            class="rounded-xl bg-orange-500/10 p-3 text-center text-[9px] font-bold uppercase text-orange-500"
                        >
                            Системная страница: удаление запрещено
                        </div>
                    </div>
                </div>
            </aside>
        </form>

        <BaseModal :show="isModalOpen" variant="lightbox" @close="selectedImage = null">
            <AppImage
                v-if="selectedImage"
                :src="selectedImage"
                alt="Полноэкранный просмотр изображения"
            />
        </BaseModal>
    </div>
</template>

<style scoped>
    .fade-date-enter-active,
    .fade-date-leave-active {
        transition:
            opacity 0.2s ease,
            transform 0.2s ease;
    }

    .fade-date-enter-from {
        opacity: 1;
        transform: translateY(2px);
    }

    .fade-date-leave-to {
        opacity: 0;
        transform: translateY(-2px);
    }
</style>
