<script setup lang="ts">
    import { computed, ref } from 'vue';

    import { Link, router, useForm } from '@inertiajs/vue3';

    import {
        AdjustmentsHorizontalIcon,
        ChevronLeftIcon,
        DocumentTextIcon,
        MagnifyingGlassIcon,
        PhotoIcon,
    } from '@heroicons/vue/24/outline';

    import FeaturesSection from '@/Components/Admin/Sections/FeaturesSection.vue';
    import SEOSection from '@/Components/Admin/Sections/SEOSection.vue';
    import AdminPageHeader from '@/Components/Admin/Shared/AdminPageHeader.vue';
    import AdminBaseTextarea from '@/Components/Admin/UI/AdminBaseTextarea.vue';
    import MediaGallery from '@/Components/Shared/MediaGallery.vue';
    import BaseCancelButton from '@/Components/UI/BaseCancelButton.vue';
    import BaseCreateButton from '@/Components/UI/BaseCreateButton.vue';
    import BaseDeleteButton from '@/Components/UI/BaseDeleteButton.vue';
    import BaseInput from '@/Components/UI/BaseInput.vue';
    import BaseSelect from '@/Components/UI/BaseSelect.vue';
    import BaseSwitch from '@/Components/UI/BaseSwitch.vue';
    import ImageUpload from '@/Components/UI/ImageUploader.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useFlash } from '@/composables/useFlash';
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

    const props = defineProps<{
        product?: ResourceSingle<AdminProduct> | null;
        categories: { data: Category[] };
        animals: { data: Animal[] };
        backUrl: string;
    }>();

    const isEdit = computed(() => !!props.product);

    const tabs = [
        { id: 'general', name: 'Основное', icon: DocumentTextIcon },
        { id: 'gallery', name: 'Галерея', icon: PhotoIcon },
        { id: 'features', name: 'Параметры', icon: AdjustmentsHorizontalIcon },
        { id: 'seo', name: 'SEO данные', icon: MagnifyingGlassIcon },
    ];
    const activeTab = ref('general');

    const form = useForm({
        name: props.product?.data?.name ?? '',
        slug: props.product?.data?.slug ?? '',
        description: props.product?.data?.description ?? '',
        category_id: props.product?.data?.category_id ?? null,
        availability_type: props.product?.data?.availability_type ?? ('stock' as AvailabilityType),
        is_active: props.product?.data?.is_active ?? true,
        attributes: props.product?.data?.attributes ?? {},
        schedule: props.product?.data?.schedule ?? { days: [] },
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
        seo: props.product?.data?.seo
            ? { ...props.product.data?.seo }
            : {
                  title: '',
                  description: '',
                  keywords: '',
                  canonical: '',
                  is_noindex: false,
              },
        backUrl: props.backUrl,
    });

    const submit = () => {
        if (isEdit.value) {
            form.transform((data) => ({
                ...data,
                _method: 'PUT',
            })).post(route('admin.products.update', props.product!.data?.id), {
                preserveScroll: true,
            });
        } else {
            form.post(route('admin.products.store'));
        }
    };

    const { notifyWithUndo } = useFlash();
    const isDeleting = ref(false);

    const showExactDate = ref(false);
    const toggleDate = () => {
        showExactDate.value = !showExactDate.value;
    };

    const deletePage = async () => {
        if (!props.product?.data.can_delete) return;

        if (isDeleting.value) return;
        isDeleting.value = true;

        const isDeleted = await notifyWithUndo(`Удаление товара "${props.product?.data.name}"`);

        if (isDeleted) {
            router.delete(route('admin.products.destroy', props.product?.data.id), {
                onFinish: () => {
                    isDeleting.value = false;
                },
            });
        } else {
            isDeleting.value = false;
        }
    };

    const availabilityOptions = [
        { value: 'stock', label: 'В наличии' },
        { value: 'daily', label: 'По графику' },
        { value: 'preorder', label: 'Предзаказ' },
    ];

    //Изобр

    const MAX_POST_SIZE = 20 * 1024 * 1024; // 20 MB

    const removeImage = (index: number) => {
        form.gallery.splice(index, 1);
    };

    const handleFile = (e: Event, field: 'voice' | 'gallery') => {
        const target = e.target as HTMLInputElement;
        const files = target.files;
        if (!files || files.length === 0) return;

        let currentTotalSize = 0;

        // Считаем текущий вес файлов в форме
        form.gallery.forEach((f: any) => {
            if (f instanceof File) currentTotalSize += f.size;
        });

        const incomingFiles = Array.from(files);
        const newFilesSize = incomingFiles.reduce((acc, file) => acc + file.size, 0);

        if (currentTotalSize + newFilesSize > MAX_POST_SIZE) {
            alert('Общий размер файлов слишком велик! Лимит 20 МБ.');
            target.value = ''; // Сбрасываем инпут
            return;
        }

        if (field === 'gallery') {
            // Важно: создаем новый массив, чтобы Vue/Inertia увидели изменения
            form.gallery = [...form.gallery, ...incomingFiles];
        }

        target.value = ''; // Очищаем инпут, чтобы можно было выбрать тот же файл повторно
    };

    const selectedImage = ref<string | null>(null);

    const existingAvatarUrl = computed(() => {
        return props.product?.data.main_photo?.[0]?.url || null;
    });
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
                        ? 'border-b-2 border-orange-600 text-white'
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
                            v-model="form.main_photo"
                            label="Аватар особи"
                            :existing-image="existingAvatarUrl"
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
                                        ? 'border-orange-600 bg-orange-600/10 text-white'
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
                    v-show="activeTab === 'gallery'"
                    v-model="form.gallery"
                    @remove="removeImage"
                    @preview="(url) => (selectedImage = url)"
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

                <SEOSection v-if="activeTab === 'seo'" v-model="form.seo" />
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
                            :disabled="isDeleting"
                            @confirm="deletePage"
                            ><span v-if="isDeleting">Удаление...</span>
                            <span v-else>Удалить страницу</span>
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
