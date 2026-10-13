<script setup lang="ts">
    import { type PropType, computed, ref } from 'vue';

    import { Link, usePage } from '@inertiajs/vue3';

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

    const isAvailable = computed(() => !!productData.value?.default_variant?.is_in_stock);

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

    /** Порог, ниже которого показываем «Осталось мало» */
    const LOW_STOCK_THRESHOLD = 5;

    type StockState = 'out' | 'low' | 'in';

    const stockState = computed<StockState>(() => {
        const variant = productData.value?.default_variant;
        if (!variant || !variant.is_in_stock || variant.stock <= 0) return 'out';
        if (variant.stock <= LOW_STOCK_THRESHOLD) return 'low';
        return 'in';
    });

    const stockLabel = computed(() => {
        switch (stockState.value) {
            case 'out':
                return 'Нет в наличии';
            case 'low':
                return 'Осталось мало';
            case 'in':
                return 'В наличии';
        }
    });

    const stockBadgeClass = computed(() => {
        switch (stockState.value) {
            case 'out':
                return 'bg-rancho-ink-100 text-rancho-ink-500 ring-rancho-ink-200';
            case 'low':
                return 'bg-rancho-ember/10 text-rancho-ember ring-rancho-ember/30';
            case 'in':
                return 'bg-rancho-pine/10 text-rancho-pine ring-rancho-pine/30';
        }
    });
</script>

<template>
    <AppContainer class="min-h-screen bg-rancho-paper">
        <div class="grid gap-12 py-12 sm:py-20 lg:grid-cols-2">
            <!-- IMAGES -->
            <div v-scroll-reveal class="space-y-4">
                <div
                    class="border-rancho-ink-200 bg-rancho-ink-50 aspect-square overflow-hidden rounded-3xl border"
                >
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
                />
            </div>

            <!-- INFO -->
            <div v-scroll-reveal class="flex flex-col">
                <!-- Категория -->
                <span
                    v-if="productData.category?.name"
                    class="text-rancho-ember text-[10px] font-black uppercase tracking-widest"
                >
                    {{ productData.category.name }}
                </span>

                <!-- Название -->
                <h1 class="mt-3 text-3xl font-black text-rancho-forest sm:text-4xl">
                    {{ productData.name }}
                </h1>

                <!-- PRICE -->
                <div v-if="price" class="mt-6 flex flex-wrap items-baseline gap-x-4 gap-y-2">
                    <span class="text-3xl font-black text-rancho-forest sm:text-4xl">
                        {{ price }}
                    </span>

                    <span
                        v-if="discount"
                        class="text-rancho-ink-400 text-lg line-through sm:text-xl"
                    >
                        {{ oldPrice }}
                    </span>

                    <span
                        v-if="discount"
                        class="bg-rancho-ember rounded-lg px-2 py-1 text-xs font-black uppercase tracking-wider text-white"
                    >
                        −{{ discount }}%
                    </span>

                    <span class="text-rancho-ink-500 text-sm">
                        / {{ productData.default_variant?.unit?.short ?? 'шт' }}
                    </span>
                </div>

                <!-- META -->
                <div class="border-rancho-ink-200 mt-8 space-y-3 border-y py-6 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-rancho-ink-500">Статус</span>
                        <span class="font-bold text-rancho-forest">
                            {{ productData.availability.label }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-rancho-ink-500">Наличие</span>
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-black uppercase tracking-wider ring-1 ring-inset"
                            :class="stockBadgeClass"
                        >
                            <span
                                class="h-1.5 w-1.5 rounded-full"
                                :class="{
                                    'bg-rancho-ink-400': stockState === 'out',
                                    'bg-rancho-ember': stockState === 'low',
                                    'bg-rancho-pine': stockState === 'in',
                                }"
                                aria-hidden="true"
                            />
                            {{ stockLabel }}
                        </span>
                    </div>
                </div>

                <!-- DESCRIPTION -->
                <p class="text-rancho-ink-600 mt-6 text-sm leading-relaxed sm:text-base">
                    {{ productData.description || 'Описание пока отсутствует' }}
                </p>

                <!-- BUY -->
                <div class="mt-auto pt-10">
                    <BuyButton v-if="productData.default_variant" :product="productData" />
                </div>
            </div>
        </div>

        <!-- COMMENTS -->
        <div v-scroll-reveal class="mt-16 pb-12 sm:pb-20">
            <CommentsSection
                :comments="comments"
                @submit="submitComment"
                :is-authenticated="isAuthenticated"
            />
        </div>
    </AppContainer>

    <BaseModal :show="isModalOpen" variant="lightbox" @close="selectedImage = null">
        <AppImage
            v-if="selectedImage"
            :src="selectedImage"
            alt="Полноэкранный просмотр изображения"
        />
    </BaseModal>
</template>
