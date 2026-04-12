<script setup lang="ts">
    import { computed } from 'vue';

    import { Head, Link } from '@inertiajs/vue3';

    import BuyButton from '@/Components/UI/BuyButton.vue';
    import MainLayout from '@/Layouts/MainLayout.vue';
    import type {
        Comment,
        ProductVariantDTO,
        ProductWithCategory,
        ResourceCollection,
        ResourceSingle,
    } from '@/types';

    const props = defineProps<{
        product: ResourceSingle<ProductWithCategory>;
        comments: ResourceCollection<Comment>;
    }>();

    const productData = computed(() => props.product.data);

    /**
     * DEFAULT VARIANT (single source of truth)
     */
    const defaultVariant = computed<ProductVariantDTO | null>(() => {
        return (
            productData.value.variants?.find((v) => v.is_default) ??
            productData.value.variants?.[0] ??
            null
        );
    });

    /**
     * PRICES — ONLY FROM VARIANT
     */
    const price = computed(() =>
        defaultVariant.value ? (defaultVariant.value.price / 100).toFixed(2) : '0.00',
    );

    const oldPrice = computed(() =>
        defaultVariant.value?.old_price ? (defaultVariant.value.old_price / 100).toFixed(2) : null,
    );

    const discount = computed(() => {
        if (!defaultVariant.value?.old_price) return null;

        return Math.round(
            100 - (defaultVariant.value.price / defaultVariant.value.old_price) * 100,
        );
    });

    /**
     * LABELS
     */
    const availabilityLabels: Record<string, string> = {
        stock: 'В наличии на ферме',
        daily: 'Собираем ежедневно',
        preorder: 'Доступно по предзаказу',
    };

    const getDaysNames = (days: number[] | undefined | null) => {
        if (!Array.isArray(days)) return '';
        const names = ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'];
        return days.map((d) => names[d]).join(', ');
    };
</script>

<template>
    <Head :title="productData.name" />

    <MainLayout>
        <div class="py-8 md:py-16">
            <AppContainer>
                <!-- BREADCRUMBS -->
                <nav class="mb-8 flex text-sm text-slate-400">
                    <Link :href="route('catalog.index')" class="hover:text-orange-600">
                        Каталог
                    </Link>
                    <span class="mx-2">/</span>
                    <span class="text-slate-600">
                        {{ productData.category?.name }}
                    </span>
                </nav>

                <div class="grid gap-12 lg:grid-cols-2">
                    <!-- IMAGE -->
                    <div class="space-y-4">
                        <div class="aspect-square overflow-hidden rounded-3xl border bg-slate-100">
                            <AppImage
                                :src="productData.media?.[0] || ''"
                                :alt="productData.name"
                                class-name="h-full w-full object-cover"
                            />
                        </div>
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
                            <span class="text-4xl font-black"> {{ price }}₽ </span>

                            <span v-if="oldPrice" class="text-xl text-slate-400 line-through">
                                {{ oldPrice }}₽
                            </span>

                            <span
                                v-if="discount"
                                class="rounded bg-red-500 px-2 py-1 text-sm font-bold text-white"
                            >
                                -{{ discount }}%
                            </span>

                            <span class="text-slate-400">
                                / {{ defaultVariant?.unit?.slug ?? 'pcs' }}
                            </span>
                        </div>

                        <!-- META -->
                        <div class="mt-8 space-y-3 border-y py-6 text-sm">
                            <div class="flex justify-between">
                                <span class="text-slate-500">Статус</span>
                                <span class="font-bold">
                                    {{ availabilityLabels[productData.availability_type] }}
                                </span>
                            </div>

                            <div v-if="productData.schedule?.days" class="flex justify-between">
                                <span class="text-slate-500">Дни доставки</span>
                                <span class="font-bold text-blue-600">
                                    {{ getDaysNames(productData.schedule.days) }}
                                </span>
                            </div>

                            <div v-if="defaultVariant">
                                <div class="flex justify-between">
                                    <span class="text-slate-500">В наличии</span>
                                    <span class="font-bold">
                                        {{ defaultVariant.stock }}
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
                            <BuyButton
                                v-if="defaultVariant"
                                :variant="defaultVariant"
                                :availability_type="productData.availability_type"
                            />
                        </div>
                    </div>
                </div>

                <!-- COMMENTS -->
                <section class="mt-24 border-t pt-16">
                    <h2 class="mb-8 text-2xl font-black">
                        Отзывы
                        <span class="ml-2 text-slate-400"> ({{ comments.data.length }}) </span>
                    </h2>

                    <div v-if="comments.data.length" class="grid gap-6 md:grid-cols-2">
                        <div
                            v-for="comment in comments.data"
                            :key="comment.id"
                            class="rounded-2xl border bg-white p-6"
                        >
                            <div class="mb-3 font-bold">
                                {{ comment.user_name }}
                            </div>
                            <p class="text-slate-600">
                                {{ comment.content }}
                            </p>
                        </div>
                    </div>

                    <div v-else class="rounded-2xl bg-slate-50 py-12 text-center">
                        <p class="italic text-slate-500">Пока нет отзывов</p>
                    </div>
                </section>
            </AppContainer>
        </div>
    </MainLayout>
</template>
