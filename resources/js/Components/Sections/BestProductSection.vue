<script setup lang="ts">
    import { Link } from '@inertiajs/vue3';

    import ProductCard from '@/Components/Cards/ProductCard.vue';
    import { Product } from '@/types';

    defineProps<{
        products: Product[];
    }>();
</script>

<template>
    <AppContainer tag="section" class="py-12 sm:py-20" aria-labelledby="products-title">
        <div class="mb-12 flex items-end justify-between">
            <div class="flex flex-col gap-4">
                <h2 id="products-title" class="text-xl font-bold text-rancho-forest sm:text-4xl">
                    Наши свежие продукты
                </h2>
                <p class="max-w-2xl text-sm text-rancho-olive/70 sm:text-lg">
                    То, что едим сами — и советуем вам.
                </p>
            </div>

            <Link
                :href="route('catalog.index')"
                class="hidden font-bold text-rancho-olive hover:text-rancho-forest md:block"
            >
                Весь каталог →
            </Link>
        </div>

        <div
            class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
            role="list"
        >
            <ProductCard v-for="product in products" :key="product.id" :product="product" />
        </div>
    </AppContainer>
</template>
