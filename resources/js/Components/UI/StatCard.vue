<script setup lang="ts">
    import type { Component } from 'vue';

    interface Props {
        label: string;
        value: string | number;
        icon?: Component;
        labelColor?: string;
    }

    const props = withDefaults(defineProps<Props>(), {
        labelColor: 'text-rancho-ink-400',
    });
</script>

<template>
    <div
        class="border-rancho-ink-800 bg-rancho-ink-900/50 hover:border-rancho-ink-700 group relative flex items-center justify-between overflow-hidden rounded-2xl border p-6 transition-colors"
        role="region"
        :aria-label="`Статистика: ${label}`"
    >
        <!-- Decorative glow -->
        <div
            class="pointer-events-none absolute -right-4 -top-4 h-24 w-24 rounded-full bg-rancho-buttercup/10 opacity-0 blur-3xl transition-opacity group-hover:opacity-100"
            aria-hidden="true"
        ></div>

        <div class="relative">
            <p
                :class="[
                    labelColor === 'text-rancho-ink-400' ? 'group-hover:text-rancho-ink-200' : '',
                    'mb-1 text-[10px] font-black uppercase tracking-[0.2em] transition-colors duration-300',
                    labelColor,
                ]"
            >
                {{ label }}
            </p>

            <p class="mt-2 text-2xl font-black text-white" role="status" aria-atomic="true">
                {{ value }}
            </p>
        </div>

        <div
            v-if="icon"
            class="bg-rancho-ink-800 group-hover:text-rancho-ink-950 group-hover:shadow-lg relative rounded-xl p-3 text-rancho-buttercup transition-all duration-300 group-hover:bg-rancho-buttercup group-hover:shadow-rancho-buttercup/20"
        >
            <component :is="icon" class="h-6 w-6" aria-hidden="true" />
        </div>
    </div>
</template>
