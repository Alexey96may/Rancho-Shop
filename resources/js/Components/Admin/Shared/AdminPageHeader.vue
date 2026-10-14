<script setup lang="ts">
    interface Props {
        title: string;
        subtitle?: string;
    }

    defineProps<Props>();
</script>

<template>
    <div class="min-w-0">
        <h1 class="line-clamp-1 truncate text-sm font-black text-white sm:text-xl">
            {{ title }}
        </h1>

        <p
            v-if="subtitle"
            class="text-rancho-ink-400 mt-0.5 line-clamp-1 truncate text-[10px] tracking-tight sm:text-xs"
        >
            {{ subtitle }}
        </p>
    </div>
</template>
