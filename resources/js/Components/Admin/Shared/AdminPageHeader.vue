<script setup lang="ts">
    interface Props {
        title: string;
        subtitle?: string;
    }

    defineProps<Props>();

    const emit = defineEmits<{
        (e: 'action'): void;
    }>();
</script>

<template>
    <div>
        <h1 class="text-xl font-black text-white">{{ title }}</h1>

        <p class="mt-1 text-xs uppercase tracking-tighter text-slate-500">
            {{ subtitle }}
        </p>
    </div>
</template>
