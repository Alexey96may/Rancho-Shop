<script setup lang="ts">
    import { XMarkIcon } from '@heroicons/vue/24/outline';

    interface Props {
        show: boolean;
        text?: string;
    }

    withDefaults(defineProps<Props>(), {
        text: 'Сбросить',
    });

    const emit = defineEmits<{
        (e: 'reset'): void;
    }>();
</script>

<template>
    <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="transform scale-95 opacity-0 -translate-x-2"
        enter-to-class="transform scale-100 opacity-100 translate-x-0"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="transform scale-100 opacity-100 translate-x-0"
        leave-to-class="transform scale-95 opacity-0 -translate-x-2"
    >
        <button
            v-if="show"
            type="button"
            @click="emit('reset')"
            :aria-label="text"
            :title="text"
            class="group inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-[10px] font-black uppercase tracking-widest text-slate-500 outline-none transition-all hover:bg-slate-900 hover:text-rose-400 focus:outline-none focus:ring-2 focus:ring-orange-500/50 active:scale-95"
        >
            <XMarkIcon
                class="h-3.5 w-3.5 transition-transform duration-300 ease-out group-hover:rotate-90 group-active:scale-75"
                aria-hidden="true"
            />

            <span>{{ text }}</span>
        </button>
    </Transition>
</template>
