<script setup lang="ts" id="a1x9k2">
    import { computed, onMounted, ref } from 'vue';

    import AnimalCard from '@/Components/Cards/AnimalCard.vue';
    import MainLayout from '@/Layouts/MainLayout.vue';
    import type { BaseAnimal } from '@/types/Animal';

    defineOptions({ layout: MainLayout });

    const animals = ref<BaseAnimal[]>([]);
    const loading = ref(true);

    const activeStatus = ref<string | null>(null);

    const fetchAnimals = async () => {
        loading.value = true;

        const res = await fetch('/api/animals');
        const json = await res.json();

        animals.value = json.data;
        loading.value = false;
    };

    onMounted(fetchAnimals);

    // фильтр
    const filteredAnimals = computed(() => {
        if (!activeStatus.value) return animals.value;
        return animals.value.filter((a) => a.status === activeStatus.value);
    });

    // уникальные статусы
    const statuses = computed(() => {
        return [...new Set(animals.value.map((a) => a.status))];
    });
</script>

<template id="b2k4v1">
    <main class="min-h-screen" style="background: #fcfaf5" aria-label="Список животных фермы">
        <div class="mx-auto max-w-7xl px-6 py-10">
            <!-- HEADER -->
            <header class="mb-10">
                <h1 class="text-4xl font-black" style="color: #1c3f34">Наши жители фермы</h1>

                <p class="mt-2 text-sm" style="color: #597d5b">
                    Познакомьтесь с животными, которые живут на нашей ферме
                </p>
            </header>

            <!-- FILTERS -->
            <section class="mb-8 flex flex-wrap gap-2" aria-label="Фильтр по статусу">
                <button
                    @click="activeStatus = null"
                    class="rounded-full px-4 py-2 text-sm font-bold transition"
                    :style="
                        activeStatus === null
                            ? 'background:#3B7558;color:white'
                            : 'background:#E3B44B22;color:#1C3F34'
                    "
                >
                    Все
                </button>

                <button
                    v-for="status in statuses"
                    :key="status"
                    @click="activeStatus = status"
                    class="rounded-full px-4 py-2 text-sm font-bold transition"
                    :aria-pressed="activeStatus === status"
                    :style="
                        activeStatus === status
                            ? 'background:#3B7558;color:white'
                            : 'background:#E3B44B22;color:#1C3F34'
                    "
                >
                    {{ status }}
                </button>
            </section>

            <!-- GRID -->
            <section
                aria-label="Карточки животных"
                class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3"
            >
                <AnimalCard v-for="animal in filteredAnimals" :key="animal.id" :animal="animal" />
            </section>

            <!-- LOADING -->
            <div v-if="loading" class="mt-10 text-center text-sm" style="color: #597d5b">
                Загружаем животных...
            </div>
        </div>
    </main>
</template>
