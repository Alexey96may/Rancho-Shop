<script setup lang="ts">
    import AnimalCard from '@/Components/Cards/AnimalCard.vue';
    import type { FarmAnimal } from '@/types/Animal';

    defineProps<{
        animals: FarmAnimal[];
    }>();
</script>

<template>
    <AppContainer tag="section" class="py-20" aria-labelledby="animals-section-title">
        <header class="mb-12 text-center">
            <h2 id="animals-section-title" class="text-rancho-forest text-4xl font-bold">
                Наши любимцы
            </h2>
            <p class="text-rancho-olive/70 mx-auto mt-4 max-w-2xl">
                Познакомьтесь с обитателями нашей фермы. У каждого свой характер и роль в нашей
                большой семье.
            </p>
        </header>

        <div
            class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
            role="list"
        >
            <AnimalCard
                v-for="animal in animals"
                :key="animal.id"
                :animal="animal"
                role="listitem"
            />
        </div>
    </AppContainer>
</template>
