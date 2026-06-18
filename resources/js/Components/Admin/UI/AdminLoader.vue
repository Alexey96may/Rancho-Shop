<template>
    <div class="flex flex-col items-center justify-center gap-4 py-12">
        <div class="relative flex h-16 w-16 items-center justify-center">
            <div class="absolute h-full w-full animate-ping rounded-2xl bg-orange-500/10"></div>

            <div class="relative h-10 w-10 animate-spin">
                <div
                    class="h-full w-full rounded-xl border-2 border-orange-600 border-t-transparent shadow-[0_0_15px_rgba(234,88,12,0.2)]"
                ></div>
            </div>
        </div>

        <div class="flex flex-col items-center gap-1">
            <span
                class="animate-pulse text-[10px] font-black uppercase tracking-[0.4em] text-white/80"
            >
                {{ text }}
            </span>
            <div
                class="h-[1px] w-8 bg-gradient-to-r from-transparent via-orange-500 to-transparent"
            ></div>
        </div>
    </div>
</template>

<script setup lang="ts">
    interface Props {
        text?: string;
    }

    withDefaults(defineProps<Props>(), {
        text: 'Загрузка данных',
    });
</script>
