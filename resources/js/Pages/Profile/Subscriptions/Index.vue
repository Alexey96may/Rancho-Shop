<script setup lang="ts">
    import { Head, Link } from '@inertiajs/vue3';

    import { BellIcon } from '@heroicons/vue/24/outline';

    import ProductCard from '@/Components/Cards/ProductCard.vue';
    import MainPagination from '@/Components/Shared/MainPagination.vue';
    import ProfileLayout from '@/Layouts/ProfileLayout.vue';
    import type { Paginated, Product } from '@/types';

    defineOptions({ layout: ProfileLayout });

    defineProps<{
        products: Paginated<Product>;
    }>();
</script>

<template>
    <Head title="Мои подписки" />

    <div class="space-y-10">
        <!-- EMPTY STATE -->
        <div
            v-if="products.data.length === 0"
            v-scroll-reveal
            class="flex flex-col items-center gap-6 rounded-2xl border border-dashed border-rancho-ink-800 bg-rancho-ink-950/60 p-8 text-center sm:p-12"
        >
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-rancho-ink-800">
                <BellIcon class="h-7 w-7 text-rancho-ink-500" aria-hidden="true" />
            </div>

            <div class="space-y-2">
                <p class="text-sm font-bold text-white">Вы пока ни на что не подписаны</p>
                <p class="text-xs text-rancho-ink-400">
                    Подпишитесь на товар — и мы сообщим, когда он появится в наличии
                </p>
            </div>

            <Link
                :href="route('catalog.index')"
                class="inline-flex items-center justify-center rounded-xl bg-rancho-pine px-6 py-3 text-xs font-black uppercase tracking-widest text-white transition-colors hover:bg-rancho-forest focus:outline-none focus-visible:ring-2 focus-visible:ring-rancho-buttercup/40"
            >
                Перейти в каталог
            </Link>
        </div>

        <!-- GRID -->
        <div
            v-else
            v-scroll-reveal
            class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
        >
            <ProductCard
                v-for="product in products.data"
                :key="product.id"
                :product="product"
                theme="dark"
            />
        </div>

        <!-- PAGINATION -->
        <MainPagination v-scroll-reveal :links="products?.meta?.links" theme="dark" />
    </div>
</template>
