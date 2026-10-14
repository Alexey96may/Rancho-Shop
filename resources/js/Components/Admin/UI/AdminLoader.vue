<script setup lang="ts">
    interface Props {
        text?: string;
    }

    withDefaults(defineProps<Props>(), {
        text: 'Загрузка данных',
    });
</script>

<template>
    <div class="flex flex-col items-center justify-center gap-3 py-10">
        <div
            class="border-rancho-ink-700 border-t-rancho-mint h-8 w-8 animate-spin rounded-full border-2"
            aria-hidden="true"
        ></div>

        <span class="text-rancho-ink-400 text-[10px] font-black uppercase tracking-[0.3em]">
            {{ text }}
        </span>
    </div>
</template>
