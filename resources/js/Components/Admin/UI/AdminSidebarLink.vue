<script setup lang="ts">
    import type { Component } from 'vue';

    import { Link } from '@inertiajs/vue3';

    defineProps<{
        href: string;
        active: boolean;
        icon?: Component;
        label: string;
    }>();
</script>

<template>
    <Link
        :href="href"
        role="menuitem"
        :aria-current="active ? 'page' : undefined"
        :aria-label="label"
        class="group flex items-center gap-3 rounded-xl px-4 py-3 text-xs font-bold uppercase tracking-wider outline-none transition-all duration-200 focus-visible:ring-4 sm:text-xs"
        :class="
            active
                ? 'shadow-lg bg-rancho-pine/20 text-rancho-mint shadow-black/20 ring-1 ring-rancho-pine/40 focus-visible:ring-rancho-pine/50'
                : 'text-rancho-ink-400 hover:bg-rancho-ink-800 hover:text-white focus-visible:bg-rancho-ink-800 focus-visible:text-white focus-visible:ring-rancho-pine/30'
        "
    >
        <component
            :is="icon"
            v-if="icon"
            class="h-5 w-5 shrink-0 transition-transform group-hover:scale-110"
            aria-hidden="true"
        />

        <span class="truncate">{{ label }}</span>
    </Link>
</template>
