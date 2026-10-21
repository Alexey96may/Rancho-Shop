<script setup lang="ts">
    import { type PropType, computed, ref } from 'vue';

    import { Link, usePage } from '@inertiajs/vue3';

    import {
        ArrowPathIcon,
        InformationCircleIcon,
        TagIcon,
        TruckIcon,
    } from '@heroicons/vue/24/outline';

    import AnimalCard from '@/Components/Cards/AnimalCard.vue';
    import CommentsSection from '@/Components/Sections/CommentsSection.vue';
    import ImageLightbox from '@/Components/Shared/ImageLightbox.vue';
    import ProductGallery from '@/Components/Shared/UIGallery.vue';
    import BuyButton from '@/Components/UI/BuyButton.vue';
    import ShareButton from '@/Components/UI/ShareButton.vue';
    import MainLayout from '@/Layouts/MainLayout.vue';
    import { useComments } from '@/composables/crud/useComments';
    import type { Comment, Paginated, Product, ResourceSingle, SharedData } from '@/types';
    import { formatDateTime, formatMoney } from '@/utils/format';

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

    // gallery
    const openImageIndex = ref<number | null>(null);

    const allImages = computed<string[]>(() => {
        const list: string[] = [];

        const main = productData.value.main_photo?.[0];
        const mainUrl = typeof main === 'string' ? main : main?.url;
        if (mainUrl) list.push(mainUrl);

        productData.value.gallery?.forEach((g) => {
            const url = typeof g === 'string' ? g : g?.url;
            if (url) list.push(url);
        });

        return list;
    });

    const openImage = (url: string) => {
        const idx = allImages.value.indexOf(url);
        if (idx >= 0) openImageIndex.value = idx;
    };

    const { submitComment, errors } = useComments('product', productData.value.id);

    const page = usePage<SharedData>();
    const isAuthenticated = computed(() => !!page.props.auth?.user);

    /** Порог низкого остатка берём из единицы измерения дефолтного варианта */
    const lowStockThreshold = computed<number | null>(() => {
        const threshold = productData.value?.unit?.low_stock_threshold;

        if (threshold === null || threshold === undefined) return null;
        const n = Number(threshold);
        return Number.isFinite(n) && n > 0 ? n : null;
    });

    type StockState = 'out' | 'low' | 'in';

    const stockState = computed<StockState>(() => {
        const variant = productData.value?.default_variant;
        if (!variant || !variant.is_in_stock || variant.stock <= 0) return 'out';

        const threshold = lowStockThreshold.value;
        if (threshold !== null && variant.stock <= threshold) return 'low';

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

    const pluralizeDays = (n: number): string => {
        const mod10 = n % 10;
        const mod100 = n % 100;

        if (mod10 === 1 && mod100 !== 11) return 'день';
        if (mod10 >= 2 && mod10 <= 4 && (mod100 < 10 || mod100 >= 20)) return 'дня';
        return 'дней';
    };
</script>

<template>
    <main>
        <AppContainer class="min-h-screen bg-rancho-paper">
            <section class="grid grid-cols-1 gap-12 pt-12 sm:pt-20 lg:grid-cols-2">
                <ProductGallery
                    v-scroll-reveal
                    :main-photo="productData.main_photo?.[0]"
                    :gallery="productData.gallery"
                    :alt="productData.name"
                    @preview="openImage"
                />

                <!-- INFO -->
                <div v-scroll-reveal class="flex flex-col gap-4">
                    <!-- Категория -->
                    <div class="flex flex-col gap-4 sm:items-start">
                        <span
                            v-if="productData.category?.name"
                            class="inline-flex items-center gap-1.5 self-start rounded-full bg-rancho-ember/10 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-rancho-ember ring-1 ring-inset ring-rancho-ember/20 transition-all hover:bg-rancho-ember/20"
                        >
                            <TagIcon class="h-3 w-3" aria-hidden="true" />
                            {{ productData.category.name }}
                        </span>

                        <div
                            class="flex items-center justify-between gap-4 sm:flex-col sm:items-start"
                        >
                            <!-- Название -->
                            <h1 class="text-3xl font-black text-rancho-forest sm:text-4xl">
                                {{ productData.name }}
                            </h1>

                            <ShareButton
                                v-if="productData.seo"
                                :seo="productData.seo"
                                :title="
                                    productData.name ? `Купить «${product.data.name}»` : undefined
                                "
                                :text="productData.description ?? undefined"
                                :image="allImages[0]"
                            />
                        </div>
                    </div>

                    <!-- PRICE -->
                    <div v-if="price" class="mt-6 flex flex-col gap-1">
                        <div v-if="discount" class="flex items-center gap-2">
                            <span class="text-sm font-medium text-rancho-ink-400 line-through">
                                {{ oldPrice }}
                            </span>
                            <span
                                class="inline-flex items-center rounded-full bg-rancho-ember/10 px-2 py-0.5 text-[10px] font-black uppercase tracking-wider text-rancho-ember ring-1 ring-inset ring-rancho-ember/20"
                            >
                                −{{ discount }}%
                            </span>
                        </div>

                        <div class="flex items-baseline gap-2">
                            <span
                                class="text-3xl font-black leading-none text-rancho-forest sm:text-4xl"
                            >
                                {{ price }}
                            </span>
                            <span class="text-sm font-medium text-rancho-ink-500">
                                / {{ productData.default_variant?.unit?.name ?? 'шт' }}
                            </span>
                        </div>
                    </div>

                    <!-- BUY -->
                    <div v-if="productData.default_variant" class="mt-6">
                        <BuyButton :product="productData" />
                    </div>

                    <!-- META (только статус и наличие) -->
                    <div class="mt-8 space-y-3 border-y border-rancho-ink-200 py-5 text-sm">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-rancho-ink-500">Статус</span>
                            <span class="font-bold text-rancho-forest">
                                {{ productData.availability.label }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between gap-2">
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

                    <!-- DELIVERY INFO — отдельный акцентный блок -->
                    <div
                        v-if="
                            productData.next_delivery_date ||
                            productData.schedule?.note ||
                            productData.schedule?.interval_days
                        "
                        class="mt-6 space-y-4 rounded-2xl border border-rancho-ember/20 bg-rancho-ember/5 p-4 sm:p-5"
                    >
                        <div v-if="productData.next_delivery_date" class="flex items-center gap-3">
                            <TruckIcon
                                class="mt-0.5 h-5 w-5 shrink-0 text-rancho-ember"
                                aria-hidden="true"
                            />
                            <div class="flex flex-col gap-0.5 text-sm sm:text-base">
                                <span
                                    class="text-[10px] font-black uppercase tracking-widest text-rancho-ember"
                                >
                                    Ближайшая поставка
                                </span>
                                <span class="font-bold text-rancho-forest">
                                    {{
                                        formatDateTime(
                                            productData.next_delivery_date ?? '',
                                            'do MMMM yyyy',
                                        )
                                    }}
                                </span>
                            </div>
                        </div>

                        <div
                            v-if="productData.schedule?.interval_days"
                            class="flex items-center gap-3 text-sm sm:text-base"
                        >
                            <ArrowPathIcon
                                class="mt-0.5 h-5 w-5 shrink-0 text-rancho-ember"
                                aria-hidden="true"
                            />
                            <div class="flex flex-col gap-0.5">
                                <span
                                    class="text-[10px] font-black uppercase tracking-widest text-rancho-ember"
                                >
                                    Периодичность
                                </span>
                                <span class="font-bold text-rancho-forest">
                                    каждые {{ productData.schedule.interval_days }}
                                    {{ pluralizeDays(productData.schedule.interval_days) }}
                                </span>
                            </div>
                        </div>

                        <div
                            v-if="productData.schedule?.note"
                            class="flex items-center gap-3 text-sm sm:text-base"
                        >
                            <InformationCircleIcon
                                class="mt-0.5 h-5 w-5 shrink-0 text-rancho-ember"
                                aria-hidden="true"
                            />
                            <div class="flex flex-col gap-0.5">
                                <span
                                    class="text-[10px] font-black uppercase tracking-widest text-rancho-ember"
                                >
                                    Примечание
                                </span>
                                <span class="text-rancho-ink-600">
                                    {{ productData.schedule.note }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- ATTRIBUTES -->
                    <div
                        v-if="productData.attributes?.length"
                        class="mt-8 grow overflow-hidden rounded-2xl border border-rancho-ink-100 bg-white px-4 py-8"
                    >
                        <h2
                            class="mb-4 text-center text-xs font-black uppercase tracking-widest text-rancho-ink-500"
                        >
                            Характеристики
                        </h2>

                        <dl class="mt-3 divide-y divide-rancho-ink-100 text-sm">
                            <div
                                v-for="(attr, index) in productData.attributes"
                                :key="index"
                                class="flex items-start justify-between gap-4 py-2.5"
                            >
                                <dt class="text-rancho-ink-500">{{ attr.label }}</dt>
                                <dd class="text-right font-medium text-rancho-forest">
                                    {{ attr.value }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </section>

            <!-- DESCRIPTION — вынесено из INFO, на всю ширину -->
            <section
                v-if="productData.description"
                v-scroll-reveal
                class="mt-10 space-y-10 sm:mt-20"
            >
                <h2
                    class="text-text-xs text-center font-black uppercase tracking-widest text-rancho-ink-500"
                >
                    О товаре
                </h2>
                <p class="text-sm leading-relaxed text-rancho-ink-600 sm:text-base">
                    {{ productData.description }}
                </p>
            </section>

            <!-- PRODUCTS -->
            <section
                v-if="product.data.animals?.length"
                v-scroll-reveal
                class="mt-8 border-t border-rancho-ink-200 pt-8 sm:mt-20"
                aria-label="Животные"
            >
                <header class="mb-6 flex items-end justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-black text-rancho-forest sm:text-3xl">
                            Кого благодарим
                        </h2>
                        <p class="mt-2 text-xs text-rancho-ink-500 sm:text-sm">
                            Без них не было бы этого продукта
                        </p>
                    </div>

                    <Link
                        :href="route('catalog.index')"
                        class="hidden text-xs font-bold text-rancho-pine transition-colors hover:text-rancho-forest sm:inline-flex"
                    >
                        Все животные →
                    </Link>
                </header>

                <div
                    class="grid grid-cols-1 justify-items-center gap-x-4 gap-y-8 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4"
                >
                    <AnimalCard
                        v-for="animal in product.data.animals"
                        :key="animal.id"
                        :animal="animal"
                        class="w-full"
                    />
                </div>

                <div class="mt-6 sm:hidden">
                    <Link
                        :href="route('animals.index')"
                        class="inline-flex w-full items-center justify-center rounded-xl border border-rancho-ink-200 bg-white px-4 py-3 text-xs font-bold text-rancho-forest transition-colors hover:border-rancho-pine hover:bg-rancho-ink-50"
                    >
                        Все животные →
                    </Link>
                </div>
            </section>

            <!-- COMMENTS -->
            <section v-if="productData.is_commentable" v-scroll-reveal class="pb-6 sm:pb-16">
                <CommentsSection
                    :comments="comments"
                    @submit="submitComment"
                    :is-authenticated="isAuthenticated"
                    :errors="errors"
                />
            </section>
        </AppContainer>

        <ImageLightbox
            v-model="openImageIndex"
            :images="allImages"
            :alt="`Фото продукта ${productData.name}`"
        />
    </main>
</template>
