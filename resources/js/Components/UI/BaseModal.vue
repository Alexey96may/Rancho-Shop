<script setup lang="ts">
    import { onMounted, onUnmounted, watch } from 'vue';

    import { XMarkIcon } from '@heroicons/vue/24/outline';

    const props = defineProps<{
        show: boolean;
        title?: string;
    }>();

    const emit = defineEmits(['close']);

    const close = () => emit('close');

    // Блокируем скролл основной страницы при открытой модалке
    watch(
        () => props.show,
        (isVisible) => {
            if (isVisible) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        },
    );

    const handleEsc = (e: KeyboardEvent) => {
        if (e.key === 'Escape' && props.show) close();
    };

    onMounted(() => window.addEventListener('keydown', handleEsc));
    onUnmounted(() => {
        window.removeEventListener('keydown', handleEsc);
        document.body.style.overflow = ''; // На всякий случай возвращаем скролл
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
                class="fixed inset-0 z-[60] flex items-start justify-center overflow-y-auto bg-slate-950/80 p-4 backdrop-blur-sm sm:items-center"
                role="dialog"
                aria-modal="true"
                :aria-labelledby="title"
                @click="close"
            >
                <div
                    class="shadow-2xl relative my-auto w-full max-w-md rounded-3xl border border-slate-800 bg-slate-900 p-6 sm:p-8"
                    @click.stop
                >
                    <button
                        @click="close"
                        class="absolute right-4 top-4 z-10 text-slate-500 transition-colors hover:text-white sm:right-6 sm:top-6"
                        aria-label="Закрыть модальное окно"
                    >
                        <XMarkIcon class="h-6 w-6" />
                    </button>

                    <h3
                        v-if="title"
                        :id="title"
                        class="mb-6 pr-8 text-xl font-black uppercase tracking-tight text-white"
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
    /* Плавный скроллбар для эстетики */
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #1e293b;
        border-radius: 10px;
    }
</style>
