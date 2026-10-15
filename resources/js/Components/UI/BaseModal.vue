<script setup lang="ts">
    import { onMounted, onUnmounted, watch } from 'vue';

    import { XMarkIcon } from '@heroicons/vue/24/outline';

    const props = withDefaults(
        defineProps<{
            show: boolean;
            title?: string;
            variant?: 'default' | 'lightbox' | 'ui';
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
                :class="[
                    'fixed inset-0 z-[100] flex items-center justify-center backdrop-blur-md',
                    variant === 'ui'
                        ? 'bg-rancho-paper/95 p-2 sm:p-4'
                        : 'overflow-y-auto bg-rancho-ink-950/95 p-4',
                ]"
                role="dialog"
                aria-modal="true"
                :aria-labelledby="title"
                @click="close"
            >
                <!-- UI: адаптивный лайтбокс -->
                <template v-if="variant === 'ui'">
                    <div
                        class="relative flex min-h-full w-full items-center justify-center"
                        @click.stop
                    >
                        <!-- Кнопка закрытия -->
                        <button
                            type="button"
                            class="fixed right-4 top-4 z-20 flex h-10 w-10 items-center justify-center rounded-full bg-rancho-ink-950/40 text-white backdrop-blur-md transition-all hover:rotate-90 hover:bg-rancho-ink-950/60 focus:outline-none focus-visible:ring-2 focus-visible:ring-rancho-pine/40 sm:right-6 sm:top-6 sm:h-11 sm:w-11"
                            aria-label="Закрыть"
                            @click="close"
                        >
                            <XMarkIcon class="h-5 w-5 sm:h-6 sm:w-6" />
                        </button>

                        <!-- Контент: на мобилке — без отступов, на десктопе — с воздухом -->
                        <div class="flex items-center justify-center p-2 sm:p-6 lg:p-10">
                            <slot />
                        </div>
                    </div>
                </template>

                <!-- LIGHTBOX -->
                <div
                    v-else-if="variant === 'lightbox'"
                    class="relative max-h-[90vh] max-w-[90vw]"
                    @click.stop
                >
                    <button
                        @click="close"
                        class="absolute -top-12 right-0 text-white/50 transition-all hover:rotate-90 hover:text-white"
                        aria-label="Закрыть модальное окно"
                    >
                        <XMarkIcon class="h-8 w-8" />
                    </button>

                    <slot />
                </div>

                <!-- DEFAULT (без изменений) -->
                <div
                    v-else
                    class="shadow-2xl relative my-auto w-full max-w-[90vw] rounded-2xl border border-rancho-ink-800 bg-rancho-ink-900 px-3 py-6 sm:rounded-3xl sm:p-6 sm:p-8"
                    @click.stop
                >
                    <button
                        @click="close"
                        class="absolute right-4 top-6 z-10 text-rancho-ink-500 transition-colors hover:text-white sm:right-6 sm:top-6"
                        aria-label="Закрыть модальное окно"
                    >
                        <XMarkIcon class="h-6 w-6" />
                    </button>

                    <h3
                        v-if="title"
                        :id="title"
                        class="text:xs mb-6 pl-2 pr-8 font-black tracking-tight text-white sm:text-xl"
                    >
                        {{ title }}
                    </h3>

                    <div class="custom-scrollbar max-h-[70vh] overflow-y-auto">
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
