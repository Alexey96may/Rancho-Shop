<script setup lang="ts">
    import { Link } from '@inertiajs/vue3';

    defineProps<{
        href: string;
        active: boolean;
        icon?: any;
        label: string;
    }>();
</script>

<template>
    <Link
        :href="href"
        role="menuitem"
        :aria-current="active ? 'page' : undefined"
        :aria-label="label"
        class="group flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-bold uppercase tracking-wider outline-none transition-all duration-200"
        :class="
            active
                ? 'shadow-lg bg-orange-600 text-white shadow-orange-900/20 focus-visible:ring-4 focus-visible:ring-orange-500/50'
                : 'text-slate-400 hover:bg-slate-800 hover:text-white focus-visible:bg-slate-800 focus-visible:text-white'
        "
    >
        <component :is="icon" v-if="icon" class="h-5 w-5" aria-hidden="true" />
        <span class="truncate">{{ label }}</span>
    </Link>
</template>
