<script setup lang="ts">
    import { onMounted, onUnmounted, watch } from 'vue';

    import { XMarkIcon } from '@heroicons/vue/24/outline';

    const props = withDefaults(
        defineProps<{
            show: boolean;
            title?: string;
            variant?: 'default' | 'lightbox';
        }>(),
        {
            variant: 'default',
        },
    );

    const emit = defineEmits(['close']);
    const close = () => emit('close');

    watch(
        () => props.show,
        (isVisible) => {
            if (typeof document === 'undefined') return;
            document.body.style.overflow = isVisible ? 'hidden' : '';
        },
        { immediate: true },
    );

    const handleEsc = (e: KeyboardEvent) => {
        if (e.key === 'Escape' && props.show) close();
    };

    onMounted(() => window.addEventListener('keydown', handleEsc));
    onUnmounted(() => {
        window.removeEventListener('keydown', handleEsc);
        document.body.style.overflow = '';
    });
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
                class="bg-rancho-ink-950/95 fixed inset-0 z-[100] flex items-center justify-center overflow-y-auto p-4 backdrop-blur-md"
                role="dialog"
                aria-modal="true"
                :aria-labelledby="title"
                @click="close"
            >
                <div
                    :class="[
                        variant === 'lightbox'
                            ? 'relative max-h-[90vh] max-w-[90vw]'
                            : 'border-rancho-ink-800 bg-rancho-ink-900 shadow-2xl relative my-auto w-full max-w-[90vw] rounded-2xl border px-3 py-6 sm:rounded-3xl sm:p-6 sm:p-8',
                    ]"
                    @click.stop
                >
                    <button
                        @click="close"
                        :class="[
                            variant === 'lightbox'
                                ? 'absolute -top-12 right-0 text-white/50 transition-all hover:rotate-90 hover:text-white'
                                : 'text-rancho-ink-500 absolute right-4 top-6 z-10 transition-colors hover:text-white sm:right-6 sm:top-6',
                        ]"
                        aria-label="Закрыть модальное окно"
                    >
                        <XMarkIcon :class="variant === 'lightbox' ? 'h-8 w-8' : 'h-6 w-6'" />
                    </button>

                    <h3
                        v-if="title && variant !== 'lightbox'"
                        :id="title"
                        class="text:xs mb-6 pl-2 pr-8 font-black tracking-tight text-white sm:text-xl"
                    >
                        {{ title }}
                    </h3>

                    <div
                        :class="
                            variant === 'lightbox'
                                ? ''
                                : 'custom-scrollbar max-h-[70vh] overflow-y-auto'
                        "
                    >
                        <slot />
                    </div>
                </div>
            </div>
        </transition>
    </teleport>
</template>

<style scoped>
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #403a2d; /* rancho-ink-700 */
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #756d57; /* rancho-ink-500 */
    }
</style>
