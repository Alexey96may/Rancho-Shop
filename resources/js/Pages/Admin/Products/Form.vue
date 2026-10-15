<script setup lang="ts">
    import { type PropType, computed, ref, useId } from 'vue';

    import { useForm } from '@inertiajs/vue3';

    import { PhotoIcon } from '@heroicons/vue/24/outline';

    import FeaturesSection from '@/Components/Admin/Sections/FeaturesSection.vue';
    import ProductScheduleFields from '@/Components/Admin/Sections/ProductScheduleFields.vue';
    import SEOSection from '@/Components/Admin/Sections/SEOSection.vue';
    import AdminPageHeader from '@/Components/Admin/Shared/AdminPageHeader.vue';
    import AdminBaseTextarea from '@/Components/Admin/UI/AdminBaseTextarea.vue';
    import DangerZone from '@/Components/Sections/DangerZone.vue';
    import MediaGallery from '@/Components/Shared/MediaGallery.vue';
    import AppImage from '@/Components/UI/AppImage.vue';
    import BaseCancelButton from '@/Components/UI/BaseCancelButton.vue';
    import BaseCreateButton from '@/Components/UI/BaseCreateButton.vue';
    import BaseInput from '@/Components/UI/BaseInput.vue';
    import BaseModal from '@/Components/UI/BaseModal.vue';
    import BaseSelect from '@/Components/UI/BaseSelect.vue';
    import BaseSmartTime from '@/Components/UI/BaseSmartTime.vue';
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
        AvailabilityType,
        Category,
        Media,
        ProductAttribute,
        ProductSchedule,
        ResourceSingle,
    } from '@/types';

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
            type: Array as PropType<Array<{ id: number; name: string }>>,
            required: true,
        },
        backUrl: {
            type: String,
            required: true,
        },
    });

    const isEdit = computed(() => !!props.product);

    const activeTab = ref('general');
    const { tabs } = useAdminTabs();

    let attrCounter = 0;
    const makeAttrId = () => `attr-${++attrCounter}`;

    const form = useForm({
        name: props.product?.data?.name ?? '',
        slug: props.product?.data?.slug ?? '',
        description: props.product?.data?.description ?? '',
        category_id: props.product?.data?.category_id ?? null,
        availability_type: props.product?.data?.availability.value ?? 'stock',
        is_active: props.product?.data?.is_active ?? true,
        is_commentable: props.product?.data?.is_commentable ?? true,
        attributes: (props.product?.data?.attributes ?? []).map((a) => ({
            ...a,
            id: a.id ?? makeAttrId(),
        })) as ProductAttribute[],
        animal_ids: props.product?.data?.animals?.map((a) => a.id) ?? [],
        main_photo: props.product?.data?.main_photo
            ? [...props.product.data?.main_photo]
            : ([] as Array<Media | File>),
        gallery: props.product?.data?.gallery
            ? [...props.product.data?.gallery]
            : ([] as Array<Media | File>),
        remove_media: [] as number[],
        schedule: (props.product?.data.schedule ?? {
            next_delivery: props.product?.data.schedule?.next_delivery ?? null,
            interval_days: props.product?.data.schedule?.interval_days ?? null,
            note: props.product?.data.schedule?.note ?? null,
        }) as ProductSchedule,
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

    const { handleFile, removeGalleryItem } = useMediaUpload(form);
    const { currentFileForUpload, currentExistingImage } = useSingleImagePreview(
        () => form.main_photo,
    );

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

    interface AvailabilityOption {
        value: AvailabilityType;
        label: string;
    }

    const availabilityOptions: AvailabilityOption[] = [
        { value: 'stock', label: 'В наличии' },
        { value: 'weekly', label: 'Раз в неделю' },
        { value: 'daily', label: 'По графику' },
        { value: 'preorder', label: 'Предзаказ' },
    ];

    const openImage = (url: string) => {
        if (url) {
            selectedImage.value = url;
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

    <div class="space-y-12 sm:space-y-8">
        <BaseCancelButton :href="backUrl" label="Назад" theme="dark" />

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
                @click="activeTab = tab.id"
                role="tab"
                :aria-selected="activeTab === tab.id"
                :class="[
                    activeTab === tab.id
                        ? 'shadow-lg bg-rancho-mint text-rancho-ink-950 shadow-rancho-mint/20'
                        : 'text-rancho-ink-400 hover:bg-rancho-ink-900 hover:text-rancho-ink-100',
                    'flex shrink-0 items-center justify-center gap-2 rounded-xl px-3 py-2.5 text-[10px] font-black uppercase tracking-widest outline-none transition-all focus-visible:ring-2 focus-visible:ring-rancho-mint/40 sm:flex-1 sm:gap-3 sm:rounded-2xl sm:px-4 sm:py-3.5',
                ]"
            >
                <component :is="tab.icon" class="h-4 w-4" aria-hidden="true" />
                {{ tab.name }}
            </button>
        </nav>

        <!-- FORM -->
        <form @submit.prevent="submit" class="grid grid-cols-1 gap-6 xl:grid-cols-12">
            <!-- LEFT COLUMN -->
            <div class="xl:col-span-8">
                <!-- GENERAL TAB -->
                <div
                    v-show="activeTab === 'general'"
                    class="space-y-6 rounded-2xl border border-rancho-ink-800 bg-rancho-ink-900/50 p-4 sm:space-y-8 sm:rounded-3xl sm:p-6 md:p-8"
                >
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <ImageUpload
                            :model-value="currentFileForUpload"
                            :existing-image="currentExistingImage"
                            @update:model-value="handleMainPhotoUpdate"
                            label="Главное фото товара"
                            :error="form.errors.main_photo"
                        />

                        <div class="flex flex-col gap-4">
                            <BaseInput
                                v-model="form.name"
                                v-model:error="form.errors.name"
                                label="Название товара"
                                placeholder="Напр: Молоко козье пастеризованное"
                                required
                                theme="dark"
                            />

                            <BaseInput
                                v-model="form.slug"
                                v-model:error="form.errors.slug"
                                label="URL Slug (оставьте пустым для авто)"
                                placeholder="moloko-kozie"
                                theme="dark"
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
                                value-key="value"
                                label-key="label"
                                label="Тип доступности"
                                variant="admin"
                            />
                        </div>
                    </div>

                    <AdminBaseTextarea
                        v-model="form.description"
                        v-model:error="form.errors.description"
                        label="Описание товара"
                        placeholder="Опишите преимущества, вкус и особенности..."
                        rows="6"
                    />

                    <ProductScheduleFields v-model="form.schedule" :errors="form.errors" />

                    <!-- ANIMALS -->
                    <div>
                        <label
                            class="mb-3 block text-[10px] font-black uppercase tracking-widest text-rancho-ink-500"
                        >
                            Источник (Животные)
                        </label>

                        <div
                            class="flex max-h-48 flex-wrap gap-2 overflow-auto sm:max-h-60 sm:gap-3"
                        >
                            <label
                                v-for="animal in animals"
                                :key="animal.id"
                                class="flex cursor-pointer items-center gap-2 rounded-2xl border px-3 py-2 transition-all sm:px-4"
                                :class="
                                    form.animal_ids.includes(animal.id)
                                        ? 'border-rancho-mint bg-rancho-mint/10 text-white'
                                        : 'border-rancho-ink-800 text-rancho-ink-400 hover:border-rancho-ink-600 hover:text-rancho-ink-200'
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

                <!-- MEDIA TAB -->
                <MediaGallery
                    v-show="activeTab === 'media'"
                    v-model="form.gallery"
                    theme="dark"
                    @remove="removeGalleryItem"
                    @preview="openImage"
                >
                    <label
                        class="bg-ranko-ink-950/50 group relative flex aspect-square cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-rancho-ink-700 transition-all focus-within:border-rancho-mint focus-within:ring-4 focus-within:ring-rancho-mint/20 hover:border-rancho-mint/50 hover:bg-rancho-ink-900 sm:rounded-3xl"
                    >
                        <input
                            type="file"
                            multiple
                            accept="image/*"
                            @change="handleFile($event, 'gallery')"
                            class="sr-only"
                        />

                        <PhotoIcon
                            class="mb-2 h-7 w-7 text-rancho-ink-500 transition-colors group-hover:text-rancho-mint sm:h-8 sm:w-8"
                            aria-hidden="true"
                        />

                        <span
                            class="text-[10px] font-black uppercase tracking-widest text-rancho-ink-500 transition-colors group-hover:text-rancho-mint"
                        >
                            Добавить
                        </span>
                    </label>
                </MediaGallery>

                <FeaturesSection
                    v-if="activeTab === 'features'"
                    :errors="form.errors"
                    v-model:attributes="form.attributes"
                />

                <SEOSection
                    v-if="activeTab === 'seo'"
                    v-model="form.seo"
                    :page-name="form.name"
                    :disabled="form.processing"
                />
            </div>

            <!-- RIGHT COLUMN -->
            <aside class="xl:col-span-4">
                <div class="space-y-6 xl:sticky xl:top-6">
                    <!-- ACTIONS -->
                    <div
                        class="rounded-2xl border border-rancho-ink-800 bg-rancho-ink-900/50 p-4 sm:rounded-3xl sm:p-6"
                    >
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

                        <hr class="my-6 border-rancho-ink-800" />

                        <div class="space-y-4">
                            <div class="flex justify-between">
                                <span
                                    class="text-[10px] font-black uppercase tracking-widest text-rancho-ink-500"
                                    >Создан:</span
                                >

                                <BaseSmartTime
                                    v-if="product?.data.created_at"
                                    :date="product?.data.created_at"
                                    theme="dark"
                                />
                            </div>

                            <div class="flex items-center justify-between">
                                <span
                                    class="text-[10px] font-black uppercase tracking-widest text-rancho-ink-500"
                                    >ID товара:</span
                                >
                                <span class="text-sm font-black text-rancho-ink-300">
                                    {{ product?.data?.id ?? 'Новый' }}
                                </span>
                            </div>
                        </div>

                        <div class="mt-8 w-full text-end">
                            <BaseCreateButton
                                type="submit"
                                :label="isEdit ? 'Обновить' : 'Создать'"
                                :disabled="form.processing"
                            />
                        </div>
                    </div>

                    <DangerZone
                        v-if="product?.data.id"
                        :show="isEdit"
                        description="Удаление продукта приведёт к его перемещению в корзину."
                        :button-title="`Удалить товар «${product.data.name}»`"
                        button-label="Удалить продукт"
                        :disabled="isDeleting(product.data.id)"
                        @confirm="deletePage"
                    />
                </div>
            </aside>
        </form>

        <!-- LIGHTBOX -->
        <BaseModal :show="isModalOpen" variant="lightbox" @close="selectedImage = null">
            <AppImage
                v-if="selectedImage"
                :src="selectedImage"
                alt="Полноэкранный просмотр изображения"
            />
        </BaseModal>
    </div>
</template>
