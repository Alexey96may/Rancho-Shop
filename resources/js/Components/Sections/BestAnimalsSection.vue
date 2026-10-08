<script setup lang="ts">
    import { Link } from '@inertiajs/vue3';

    import AnimalCard from '@/Components/Cards/AnimalCard.vue';
    import type { Animal } from '@/types/Animal';

    defineProps<{
        animals: Animal[];
    }>();
</script>

<template>
    <AppContainer tag="section" class="py-20" aria-labelledby="animals-section-title">
        <div class="mb-12 flex items-end justify-between">
            <div>
                <h2 id="animals-section-title" class="text-4xl font-bold text-rancho-forest">
                    Наши любимцы
                </h2>
                <p class="mx-auto mt-4 max-w-2xl text-rancho-olive/70">
                    Познакомьтесь с обитателями нашей фермы. У каждого свой характер и роль в нашей
                    большой семье.
                </p>
            </div>

            <Link
                :href="route('animals.index')"
                class="hidden font-bold text-rancho-olive hover:text-rancho-forest md:block"
            >
                Весь каталог →
            </Link>
        </div>

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
