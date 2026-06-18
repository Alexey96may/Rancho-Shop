<script setup lang="ts">
    import { ref } from 'vue';

    import AppImage from '@/Components/UI/AppImage.vue';
    import type { Media } from '@/types';

    interface Props {
        images: Media[];
        columns?: number;
        context?: 'product' | 'animal' | 'general';
    }

    const props = withDefaults(defineProps<Props>(), {
        columns: 4,
        context: 'general',
    });

    // State for opening the modal (if you don't want to install libraries)
    const selectedImage = ref<string | null>(null);

    const openImage = (url: string) => {
        selectedImage.value = url;
    };
</script>

<template>
    <div class="gallery-container">
        <div
            class="grid gap-4 md:gap-6"
            :style="`grid-template-columns: repeat(${columns}, minmax(0, 1fr))`"
        >
            <div
                v-for="(image, index) in images"
                :key="image.id || index"
                class="shadow-sm hover:shadow-xl group relative aspect-square cursor-zoom-in overflow-hidden rounded-[2.5rem] bg-slate-100 transition-all hover:-translate-y-1"
                @click="openImage(image.url)"
            >
                <AppImage
                    :src="image"
                    type="thumbnails"
                    :context="context"
                    :alt="image.name"
                    class="h-full w-full object-cover"
                />

                <div
                    class="absolute inset-0 flex items-center justify-center bg-slate-900/10 opacity-0 transition-opacity group-hover:opacity-100"
                >
                    <div class="shadow-lg rounded-full bg-white/90 p-3">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-6 w-6 text-slate-900"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"
                            />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <Transition name="fade">
            <div
                v-if="selectedImage"
                class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/90 p-4 backdrop-blur-sm"
                @click="selectedImage = null"
            >
                <button
                    class="absolute right-8 top-8 text-white transition-transform hover:rotate-90"
                >
                    <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>

                <img
                    :src="selectedImage"
                    class="shadow-2xl max-h-[90vh] max-w-[90vw] rounded-2xl object-contain"
                    alt="Full size"
                />
            </div>
        </Transition>
    </div>
</template>

<style scoped>
    .fade-enter-active,
    .fade-leave-active {
        transition: opacity 0.3s ease;
    }
    .fade-enter-from,
    .fade-leave-to {
        opacity: 0;
    }
</style>
