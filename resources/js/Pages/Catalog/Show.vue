<script setup lang="ts">
    import { computed } from 'vue';

    import { Head, Link } from '@inertiajs/vue3';

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
                <nav class="text-slate-400 mb-8 flex text-sm">
                    <Link :href="route('catalog.index')" class="hover:text-orange-600"
                        >Каталог</Link
                    >
                    <span class="mx-2">/</span>
                    <span class="text-slate-600">{{ productData.category?.name }}</span>
                </nav>

                <div class="grid grid-cols-1 gap-12 lg:grid-cols-2">
                    <div class="space-y-4">
                        <div
                            class="bg-slate-100 border-slate-100 shadow-inner aspect-square overflow-hidden rounded-3xl border"
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
                                class="border-slate-200 hover:border-orange-500 h-20 w-20 cursor-pointer overflow-hidden rounded-xl border transition-colors"
                            >
                                <img :src="img.url" class="h-full w-full object-cover" />
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col">
                        <div class="mb-6">
                            <span
                                class="bg-orange-50 text-orange-700 rounded-full px-3 py-1 text-xs font-bold uppercase tracking-wider"
                            >
                                {{ productData.category?.name }}
                            </span>
                            <h1 class="text-slate-900 mt-4 text-4xl font-black leading-tight">
                                {{ productData.name }}
                            </h1>
                        </div>

                        <div class="mb-8 flex items-baseline gap-4">
                            <span class="text-slate-900 text-4xl font-black">{{ price }}₽</span>
                            <span v-if="oldPrice" class="text-slate-400 text-xl line-through"
                                >{{ oldPrice }}₽</span
                            >
                            <span
                                v-if="discount"
                                class="bg-red-500 text-white rounded-lg px-2 py-1 text-sm font-bold"
                            >
                                -{{ discount }}%
                            </span>
                            <span class="text-slate-400 font-medium">/ {{ productData.unit }}</span>
                        </div>

                        <div class="border-slate-100 space-y-4 border-y py-6">
                            <div v-if="productData.attributes?.breed" class="flex justify-between">
                                <span class="text-slate-500">Порода/Сорт:</span>
                                <span class="text-slate-900 font-bold">{{
                                    productData.attributes.breed
                                }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Статус:</span>
                                <span class="text-slate-900 font-bold">{{
                                    availabilityLabels[productData.availability_type]
                                }}</span>
                            </div>
                            <div v-if="productData.schedule" class="flex justify-between">
                                <span class="text-slate-500">Дни доставки:</span>
                                <span class="text-blue-600 font-bold">{{
                                    getDaysNames(productData.schedule.days)
                                }}</span>
                            </div>
                        </div>

                        <div class="text-slate-600 mt-8 leading-relaxed">
                            <p>
                                {{
                                    productData.description ||
                                    'У этого товара пока нет подробного описания, но мы гарантируем его свежесть и качество прямо с нашего ранчо.'
                                }}
                            </p>
                        </div>

                        <div class="mt-auto pt-10">
                            <button
                                class="bg-slate-900 text-white hover:bg-orange-600 disabled:bg-slate-200 flex w-full items-center justify-center gap-3 rounded-2xl py-5 text-xl font-bold transition-all active:scale-95"
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
                            <p class="text-slate-400 mt-4 text-center text-xs">
                                Доставка по Симферополю осуществляется в течение 24 часов после
                                сбора.
                            </p>
                        </div>
                    </div>
                </div>

                <section class="border-slate-100 mt-24 border-t pt-16">
                    <div class="mb-10 flex items-center justify-between">
                        <h2 class="text-slate-900 text-2xl font-black">Отзывы покупателей</h2>
                        <span class="bg-slate-100 text-slate-600 rounded-lg px-3 py-1 font-bold">
                            {{ comments.data.length }}
                        </span>
                    </div>

                    <div v-if="comments.data.length" class="grid grid-cols-1 gap-8 md:grid-cols-2">
                        <div
                            v-for="comment in comments.data"
                            :key="comment.id"
                            class="bg-white border-slate-100 shadow-sm rounded-2xl border p-6"
                        >
                            <div class="mb-4 flex items-center gap-3">
                                <div
                                    class="bg-orange-100 text-orange-700 flex h-10 w-10 items-center justify-center rounded-full font-bold"
                                >
                                    {{ comment.user_name[0] }}
                                </div>
                                <div>
                                    <div class="text-slate-900 font-bold">
                                        {{ comment.user_name }}
                                    </div>
                                    <div class="text-slate-400 text-xs">
                                        {{ comment.created_at }}
                                    </div>
                                </div>
                            </div>
                            <p class="text-slate-600">{{ comment.content }}</p>
                        </div>
                    </div>

                    <div v-else class="bg-slate-50 rounded-3xl py-12 text-center">
                        <p class="text-slate-500 italic">
                            Будьте первым, кто оставит отзыв об этом продукте!
                        </p>
                    </div>
                </section>
            </AppContainer>
        </div>
    </MainLayout>
</template>
