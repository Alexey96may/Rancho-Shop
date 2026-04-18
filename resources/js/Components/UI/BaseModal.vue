<script setup lang="ts">
    import { onMounted, onUnmounted } from 'vue';

    import { XMarkIcon } from '@heroicons/vue/24/outline';

    defineProps<{
        show: boolean;
        title?: string;
    }>();

    const emit = defineEmits(['close']);

    const close = () => emit('close');

    // Закрытие по нажатию Esc
    const handleEsc = (e: KeyboardEvent) => {
        if (e.key === 'Escape') close();
    };

    onMounted(() => window.addEventListener('keydown', handleEsc));
    onUnmounted(() => window.removeEventListener('keydown', handleEsc));
</script>

<template>
    <teleport to="body">
        <transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-950/80 p-4 backdrop-blur-sm"
                role="dialog"
                aria-modal="true"
                :aria-labelledby="title"
            >
                <div
                    class="shadow-2xl relative w-full max-w-md rounded-3xl border border-slate-800 bg-slate-900 p-8"
                    @click.stop
                >
                    <button
                        @click="close"
                        class="absolute right-6 top-6 text-slate-500 transition-colors hover:text-white"
                        aria-label="Закрыть модальное окно"
                    >
                        <XMarkIcon class="h-6 w-6" />
                    </button>

                    <h3
                        :id="title"
                        class="mb-6 text-xl font-black uppercase tracking-tight text-white"
                    >
                        {{ title }}
                    </h3>

                    <slot />
                </div>
            </div>
        </transition>
    </teleport>
</template>
