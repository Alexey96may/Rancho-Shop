<script setup lang="ts">
    interface Props {
        label: string;
        value: string | number;
        labelColor?: string;
    }

    withDefaults(defineProps<Props>(), {
        labelColor: 'text-slate-500',
    });
</script>

<template>
    <div
        class="rounded-3xl border border-slate-800 bg-slate-900/50 p-5"
        role="region"
        :aria-label="`Статистика: ${label}`"
    >
        <p
            :class="['text-[10px] font-black uppercase tracking-widest', labelColor]"
            aria-hidden="true"
        >
            {{ label }}
        </p>

        <p class="mt-2 text-2xl font-black text-white" role="status" :aria-atomic="true">
            {{ value }}
        </p>
    </div>
</template>
