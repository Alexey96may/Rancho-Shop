<script setup lang="ts">
    import type { Component } from 'vue';

    interface Props {
        label: string;
        value: string | number;
        icon?: Component;
        labelColor?: string;
    }

    withDefaults(defineProps<Props>(), {
        labelColor: 'text-slate-500',
    });
</script>

<template>
    <div
        class="group relative flex items-center justify-between overflow-hidden rounded-2xl border border-slate-800/50 bg-slate-900/50 p-6"
        role="region"
        :aria-label="`Статистика: ${label}`"
    >
        <div
            class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-indigo-500/5 opacity-0 blur-3xl transition-opacity group-hover:opacity-100"
        ></div>

        <div>
            <p
                :class="[
                    labelColor === 'text-slate-500' ? 'group-hover:text-slate-300' : '',
                    'mb-1 text-[10px] font-black uppercase tracking-[0.2em] transition-colors duration-300',
                    labelColor,
                ]"
                aria-hidden="true"
            >
                {{ label }}
            </p>

            <p class="mt-2 text-2xl font-black text-white" role="status" :aria-atomic="true">
                {{ value }}
            </p>
        </div>

        <div
            v-if="icon"
            class="group-hover:shadow-lg rounded-xl bg-slate-800/50 p-3 text-indigo-400 transition-all duration-300 group-hover:bg-indigo-500 group-hover:text-white group-hover:shadow-indigo-500/20"
        >
            <component :is="icon" class="h-6 w-6" />
        </div>
    </div>
</template>
