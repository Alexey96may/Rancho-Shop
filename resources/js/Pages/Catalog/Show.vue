<script setup lang="ts">
    import { type PropType, computed, ref } from 'vue';

    import { Head, Link, usePage } from '@inertiajs/vue3';

    import CommentsSection from '@/Components/Sections/CommentsSection.vue';
    import MediaGallery from '@/Components/Shared/MediaGallery.vue';
    import BaseModal from '@/Components/UI/BaseModal.vue';
    import BuyButton from '@/Components/UI/BuyButton.vue';
    import MainLayout from '@/Layouts/MainLayout.vue';
    import { useComments } from '@/composables/crud/useComments';
    import type { GalleryItem } from '@/composables/features/useMediaUpload';
    import type { Comment, Paginated, Product, ResourceSingle, SharedData } from '@/types';
    import { formatMoney } from '@/utils/format';

    defineOptions({ layout: MainLayout });

    const props = defineProps({
        product: {
            type: Object as PropType<ResourceSingle<Product>>,
            required: true,
            validator: (value: ResourceSingle<Product>) => {
                return Boolean(
                    value &&
                    typeof value === 'object' &&
                    value.data &&
                    typeof value.data.id !== 'undefined',
                );
            },
        },
        comments: {
            type: Object as PropType<Paginated<Comment>>,
            required: true,
            validator: (value: Paginated<Comment>) => {
                return Boolean(value && Array.isArray(value.data));
            },
        },
    });

    const productData = computed(() => props.product.data);

    /**
     * PRICES — ONLY FROM VARIANT
     */
    const price = computed(() =>
        productData.value?.default_variant?.price
            ? formatMoney(productData.value?.default_variant?.price)
            : null,
    );

    const oldPrice = computed(() =>
        productData.value?.default_variant?.old_price
            ? formatMoney(productData.value?.default_variant?.old_price)
            : null,
    );

    const discount = computed(() => {
        if (
            !productData.value?.default_variant?.price ||
            !productData.value?.default_variant?.old_price
        )
            return null;
        if (
            productData.value.default_variant.price >= productData.value?.default_variant?.old_price
        )
            return null;

        return Math.round(
            100 -
                (productData.value?.default_variant?.price /
                    productData.value?.default_variant?.old_price) *
                    100,
        );
    });

    const selectedImage = ref<string | null>(null);

    const openImage = (item: GalleryItem) => {
        const previewUrl = getPreview(item);

        if (previewUrl) {
            selectedImage.value = previewUrl;
        }
    };

    const getPreview = (item: GalleryItem | string | null | undefined): string => {
        if (!item) return '';

        if (typeof item === 'string') {
            return item;
        }

        return '';
    };

    const isModalOpen = computed({
        get: () => !!selectedImage.value,
        set: (value) => {
            if (!value) selectedImage.value = null;
        },
    });

    const { submitComment } = useComments('product', productData.value.id);

    const page = usePage<SharedData>();
    const isAuthenticated = computed(() => !!page.props.auth?.user);
</script>

<template>
    <div class="py-8 md:py-16">
        <Head :title="productData.name" />

        <AppContainer>
            <!-- BREADCRUMBS -->
            <nav class="mb-8 flex text-sm text-slate-400">
                <Link :href="route('catalog.index')" class="hover:text-orange-600"> Каталог </Link>
                <span class="mx-2">/</span>
                <span class="text-slate-600">
                    {{ productData.category?.name }}
                </span>
            </nav>

            <div class="grid gap-12 lg:grid-cols-2">
                <!-- IMAGES -->
                <div class="space-y-4">
                    <div class="aspect-square overflow-hidden rounded-3xl border bg-slate-100">
                        <AppImage
                            :src="productData.main_photo?.[0] || ''"
                            :alt="productData.name"
                            class-name="h-full w-full object-cover"
                        />
                    </div>

                    <MediaGallery
                        v-model="product.data.gallery"
                        @preview="openImage"
                        :is-view-mode="true"
                    ></MediaGallery>
                </div>

                <!-- INFO -->
                <div class="flex flex-col">
                    <span class="text-xs font-bold uppercase text-orange-600">
                        {{ productData.category?.name }}
                    </span>

                    <h1 class="mt-3 text-4xl font-black">
                        {{ productData.name }}
                    </h1>

                    <!-- PRICE -->
                    <div class="mt-6 flex items-baseline gap-4">
                        <span v-if="price" class="text-4xl font-black"> {{ price }} </span>

                        <span v-if="discount" class="text-xl text-slate-400 line-through">
                            {{ oldPrice }}
                        </span>

                        <span
                            v-if="discount"
                            class="rounded bg-red-500 px-2 py-1 text-sm font-bold text-white"
                        >
                            -{{ discount }}%
                        </span>

                        <span class="text-slate-400">
                            / {{ productData.default_variant?.unit?.short ?? 'шт' }}
                        </span>
                    </div>

                    <!-- META -->
                    <div class="mt-8 space-y-3 border-y py-6 text-sm">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Статус</span>
                            <span class="font-bold">
                                {{ productData.availability.label }}
                            </span>
                        </div>

                        <div v-if="productData.default_variant">
                            <div class="flex justify-between">
                                <span class="text-slate-500">В наличии</span>
                                <span class="font-bold">
                                    {{ productData.default_variant.stock }} /
                                    {{ productData.default_variant.unit?.short }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- DESCRIPTION -->
                    <p class="mt-6 text-slate-600">
                        {{ productData.description || 'Описание пока отсутствует' }}
                    </p>

                    <!-- BUY -->
                    <div class="mt-auto pt-10">
                        <BuyButton v-if="productData.default_variant" :product="productData" />
                    </div>
                </div>
            </div>

            <!-- COMMENTS -->
            <div class="mx-auto mt-12 max-w-4xl px-6 pb-16">
                <CommentsSection
                    :comments="comments"
                    @submit="submitComment"
                    :is-authenticated="isAuthenticated"
                />
            </div>
        </AppContainer>

        <BaseModal :show="isModalOpen" variant="lightbox" @close="selectedImage = null"
            ><AppImage
                v-if="selectedImage"
                :src="selectedImage"
                alt="Полноэкранный просмотр изображения"
            />
        </BaseModal>
    </div>
</template>
