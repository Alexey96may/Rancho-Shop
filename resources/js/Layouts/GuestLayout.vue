<script setup lang="ts">
    import TheHeader from '@/Components/Sections/TheHeader.vue';
    import SeoMeta from '@/Components/Shared/SeoMeta.vue';
    import Toaster from '@/Components/Shared/Toaster.vue';
    import { useFlashToasts } from '@/composables/useFlashToasts';

    useFlashToasts();
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-100">
        <SeoMeta />
        <Toaster />

        <TheHeader />

        <div
            class="flex min-h-[calc(100vh-4rem)] flex-col items-center justify-center px-4 py-12 sm:px-6"
        >
            <div class="w-full sm:max-w-md">
                <div
                    class="shadow-lg overflow-hidden rounded-2xl border border-slate-200 bg-white/80 px-6 py-6 shadow-slate-200/50 backdrop-blur sm:px-8 sm:py-8"
                >
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
