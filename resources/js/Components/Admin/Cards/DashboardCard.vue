<script setup lang="ts">
    import { type Component, useId } from 'vue';

    import { Link } from '@inertiajs/vue3';

    defineProps<{
        title: string;
        description: string;
        href: string;
        icon: Component;
        count?: number | string;
    }>();

    const descriptionId = useId();
</script>

<template>
    <Link
        :href="href"
        class="border-rancho-ink-800 bg-rancho-ink-900/50 hover:border-rancho-mint/40 hover:bg-rancho-ink-900 focus-visible:ring-rancho-mint/40 group relative block overflow-hidden rounded-3xl border p-6 transition-all duration-300 hover:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2"
        :aria-label="`${title}. ${description}`"
        :aria-describedby="descriptionId"
    >
        <div class="flex items-start justify-between gap-4">
            <div class="relative z-10 min-w-0">
                <!-- Иконка -->
                <div
                    class="bg-rancho-ink-950 text-rancho-mint ring-rancho-ink-800 group-hover:bg-rancho-mint group-hover:text-rancho-ink-950 group-hover:ring-rancho-mint mb-4 inline-flex h-9 w-9 items-center justify-center rounded-full ring-1 transition-all duration-300 group-hover:scale-110 sm:mb-6 sm:h-12 sm:w-12 sm:rounded-2xl"
                    aria-hidden="true"
                >
                    <component :is="icon" class="h-4 w-4 sm:h-6 sm:w-6" />
                </div>

                <h2
                    class="group-hover:text-rancho-mint text-sm font-black uppercase tracking-tight text-white transition-colors sm:text-lg"
                >
                    {{ title }}
                </h2>

                <p
                    :id="descriptionId"
                    class="text-rancho-ink-400 group-hover:text-rancho-ink-300 mt-2 text-xs font-medium transition-colors sm:text-sm"
                >
                    {{ description }}
                </p>
            </div>

            <!-- Счётчик -->
            <div
                v-if="count !== undefined"
                class="text-rancho-ink-800 group-hover:text-rancho-mint/30 shrink-0 text-2xl font-black transition-colors sm:text-4xl"
                aria-hidden="true"
            >
                {{ count }}
            </div>
        </div>

        <!-- Декоративное свечение -->
        <div
            class="bg-rancho-mint/10 pointer-events-none absolute -right-10 -top-10 h-32 w-32 rounded-full opacity-0 blur-3xl transition-opacity duration-300 group-hover:opacity-100"
            aria-hidden="true"
        />
    </Link>
</template>
