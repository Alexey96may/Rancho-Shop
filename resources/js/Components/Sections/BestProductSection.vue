<script setup lang="ts">
    import ProductCard from '@/Components/Cards/ProductCard.vue';
    import { Product } from '@/types';

    defineProps<{
        products: Product[];
    }>();
</script>

<template>
    <AppContainer tag="section" class="py-20" aria-labelledby="products-title">
        <div class="mb-12 flex items-end justify-between">
            <div>
                <h2 id="products-title" class="text-rancho-forest text-4xl font-bold">
                    Наши свежие продукты
                </h2>
                <p class="text-rancho-olive/70 mt-4">
                    Собрано сегодня утром, чтобы быть на вашем столе к вечеру.
                </p>
            </div>

            <Link
                :href="route('home')"
                class="text-rancho-olive hover:text-rancho-forest hidden font-bold md:block"
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
