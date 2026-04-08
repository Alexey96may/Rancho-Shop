<script setup lang="ts">
    import { computed } from 'vue';

    import { Head, Link } from '@inertiajs/vue3';

    import BuyButton from '@/Components/UI/BuyButton.vue';
    import MainLayout from '@/Layouts/MainLayout.vue';
    import type { Comment, ProductWithCategory, ResourceCollection, ResourceSingle } from '@/types';

    const props = defineProps<{
        product: ResourceSingle<ProductWithCategory>;
        comments: ResourceCollection<Comment>;
    }>();

    const productData = computed(() => props.product.data);

    // Форматирование цен
    const price = computed(() => (productData.value.price_rub / 100).toFixed(2));
    const oldPrice = computed(() =>
        productData.value.old_price ? (productData.value.old_price / 100).toFixed(2) : null,
    );

    const discount = computed(() => {
        if (!productData.value.old_price) return null;
        return Math.round(100 - (productData.value.price_rub / productData.value.old_price) * 100);
    });

    // Вспомогательные метки
    const availabilityLabels: Record<string, string> = {
        stock: 'В наличии на ферме',
        daily: 'Собираем ежедневно',
        preorder: 'Доступно по предзаказу',
    };

    const getDaysNames = (days: number[] | undefined) => {
        if (!days) return '';
        const names = ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'];
        return days.map((d) => names[d]).join(', ');
    };
</script>

<template>
    <Head :title="productData.name" />

    <MainLayout>
        <div class="py-8 md:py-16">
            <AppContainer>
                <nav class="mb-8 flex text-sm text-slate-400">
                    <Link :href="route('catalog.index')" class="hover:text-orange-600"
                        >Каталог</Link
                    >
                    <span class="mx-2">/</span>
                    <span class="text-slate-600">{{ productData.category?.name }}</span>
                </nav>

                <div class="grid grid-cols-1 gap-12 lg:grid-cols-2">
                    <div class="space-y-4">
                        <div
                            class="shadow-inner aspect-square overflow-hidden rounded-3xl border border-slate-100 bg-slate-100"
                        >
                            <AppImage
                                :src="productData.media?.[0] || ''"
                                :context="'product'"
                                :alt="productData.name"
                                :class-name="'h-full w-full object-cover'"
                            />
                        </div>

                        <div
                            v-if="productData.media && productData.media.length > 1"
                            class="flex gap-4"
                        >
                            <div
                                v-for="img in productData.media"
                                :key="img.id"
                                class="h-20 w-20 cursor-pointer overflow-hidden rounded-xl border border-slate-200 transition-colors hover:border-orange-500"
                            >
                                <img :src="img.url" class="h-full w-full object-cover" />
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col">
                        <div class="mb-6">
                            <span
                                class="rounded-full bg-orange-50 px-3 py-1 text-xs font-bold uppercase tracking-wider text-orange-700"
                            >
                                {{ productData.category?.name }}
                            </span>
                            <h1 class="mt-4 text-4xl font-black leading-tight text-slate-900">
                                {{ productData.name }}
                            </h1>
                        </div>

                        <div class="mb-8 flex items-baseline gap-4">
                            <span class="text-4xl font-black text-slate-900">{{ price }}₽</span>
                            <span v-if="oldPrice" class="text-xl text-slate-400 line-through"
                                >{{ oldPrice }}₽</span
                            >
                            <span
                                v-if="discount"
                                class="rounded-lg bg-red-500 px-2 py-1 text-sm font-bold text-white"
                            >
                                -{{ discount }}%
                            </span>
                            <span class="font-medium text-slate-400">/ {{ productData.unit }}</span>
                        </div>

                        <div class="space-y-4 border-y border-slate-100 py-6">
                            <div v-if="productData.attributes?.breed" class="flex justify-between">
                                <span class="text-slate-500">Порода/Сорт:</span>
                                <span class="font-bold text-slate-900">{{
                                    productData.attributes.breed
                                }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Статус:</span>
                                <span class="font-bold text-slate-900">{{
                                    availabilityLabels[productData.availability_type]
                                }}</span>
                            </div>
                            <div v-if="productData.schedule" class="flex justify-between">
                                <span class="text-slate-500">Дни доставки:</span>
                                <span class="font-bold text-blue-600">{{
                                    getDaysNames(productData.schedule.days)
                                }}</span>
                            </div>
                        </div>

                        <div class="mt-8 leading-relaxed text-slate-600">
                            <p>
                                {{
                                    productData.description ||
                                    'У этого товара пока нет подробного описания, но мы гарантируем его свежесть и качество прямо с нашего ранчо.'
                                }}
                            </p>
                        </div>

                        <div class="mt-auto pt-10">
                            <BuyButton
                                :product="productData"
                                :price="productData.price_rub"
                                :availability_type="productData.availability_type"
                            />

                            <button
                                class="flex w-full items-center justify-center gap-3 rounded-2xl bg-slate-900 py-5 text-xl font-bold text-white transition-all hover:bg-orange-600 active:scale-95 disabled:bg-slate-200"
                                :disabled="
                                    productData.stock === 0 &&
                                    productData.availability_type === 'stock'
                                "
                            >
                                <span v-if="productData.availability_type === 'preorder'"
                                    >Забронировать продукт</span
                                >
                                <span v-else>Добавить в корзину — {{ price }}₽</span>
                            </button>
                            <p class="mt-4 text-center text-xs text-slate-400">
                                Доставка по Симферополю осуществляется в течение 24 часов после
                                сбора.
                            </p>
                        </div>
                    </div>
                </div>

                <section class="mt-24 border-t border-slate-100 pt-16">
                    <div class="mb-10 flex items-center justify-between">
                        <h2 class="text-2xl font-black text-slate-900">Отзывы покупателей</h2>
                        <span class="rounded-lg bg-slate-100 px-3 py-1 font-bold text-slate-600">
                            {{ comments.data.length }}
                        </span>
                    </div>

                    <div v-if="comments.data.length" class="grid grid-cols-1 gap-8 md:grid-cols-2">
                        <div
                            v-for="comment in comments.data"
                            :key="comment.id"
                            class="shadow-sm rounded-2xl border border-slate-100 bg-white p-6"
                        >
                            <div class="mb-4 flex items-center gap-3">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-full bg-orange-100 font-bold text-orange-700"
                                >
                                    {{ comment.user_name[0] }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900">
                                        {{ comment.user_name }}
                                    </div>
                                    <div class="text-xs text-slate-400">
                                        {{ comment.created_at }}
                                    </div>
                                </div>
                            </div>
                            <p class="text-slate-600">{{ comment.content }}</p>
                        </div>
                    </div>

                    <div v-else class="rounded-3xl bg-slate-50 py-12 text-center">
                        <p class="italic text-slate-500">
                            Будьте первым, кто оставит отзыв об этом продукте!
                        </p>
                    </div>
                </section>
            </AppContainer>
        </div>
    </MainLayout>
</template>
