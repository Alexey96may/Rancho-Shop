<script setup lang="ts">
    import { Head } from '@inertiajs/vue3';

    import ProductCard from '@/Components/Cards/ProductCard.vue';
    import MainLayout from '@/Layouts/MainLayout.vue';
    import type { ProductWithCategory, ResourceCollection } from '@/types';

    // Принимаем данные от контроллера
    const props = defineProps<{
        products: ResourceCollection<ProductWithCategory>;
    }>();
</script>

<template>
    <Head title="Каталог продукции Ранчо" />

    <MainLayout>
        <template #header>
            <h1 class="text-slate-900 text-3xl font-black">Наши продукты</h1>
            <p class="text-slate-500 mt-1">Свежее из Симферополя прямо к вашему столу</p>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div v-if="!products.data.length" class="py-20 text-center">
                    <div class="mb-4 text-6xl">🚜</div>
                    <h3 class="text-slate-900 text-xl font-bold">Пока ничего не выросло</h3>
                    <p class="text-slate-500">Зайдите чуть позже или выберите другую категорию.</p>
                </div>

                <div
                    v-else
                    class="grid grid-cols-1 gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 xl:gap-x-8"
                >
                    <ProductCard
                        v-for="product in products.data"
                        :key="product.id"
                        :product="product"
                    />
                </div>
            </div>
        </div>
    </MainLayout>
</template>
