<script setup lang="ts">
    import type { Component } from 'vue';

    import { Link } from '@inertiajs/vue3';

    defineProps<{
        title: string;
        description: string;
        href: string;
        icon: Component;
    }>();
</script>

<template>
    <Link :href="href" class="card group" :aria-label="title">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-[var(--forest)]">
                    {{ title }}
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    {{ description }}
                </p>
            </div>

            <div
                class="shrink-0 text-[var(--forest)] opacity-70 transition group-hover:scale-110"
                aria-hidden="true"
            >
                <component :is="icon" class="h-6 w-6" />
            </div>
        </div>
    </Link>
</template>

<style scoped>
    .card {
        @apply block rounded-xl bg-white p-5 outline-none transition-all duration-200;
    }

    .card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
    }

    .card:focus-visible {
        outline: 2px solid var(--buttercup);
        outline-offset: 2px;
    }
</style>
