<script setup lang="ts">
    import { Link } from '@inertiajs/vue3';

    import { MoveRight } from 'lucide-vue-next';

    interface Props {
        title: string;
        description: string;
        buttonText: string;
        routeName: string;
        titleColor?: string;
    }

    defineProps<Props>();
</script>

<template>
    <div class="relative z-10 flex h-full items-center px-6 lg:px-20">
        <div
            class="animate-in fade-in slide-in-from-bottom-10 fill-mode-forwards shadow-2xl max-w-full translate-y-[-10%] rounded-xl border border-rancho-olive bg-rancho-paper/10 p-4 opacity-0 backdrop-blur-lg duration-1000 md:max-w-sm lg:p-8 2xl:max-w-lg"
        >
            <h2
                :class="[
                    'mb-6 text-lg font-bold leading-tight lg:text-2xl',
                    titleColor || 'text-rancho-forest',
                ]"
            >
                {{ title }}
            </h2>

            <p class="text-zinc-900 mb-8 text-sm leading-relaxed lg:text-lg">
                {{ description }}
            </p>

            <Link
                :href="route(routeName)"
                :aria-label="`${buttonText}: ${title}`"
                class="hover:bg-white hover:shadow-xl focus:ring-white inline-flex items-center gap-3 rounded-full bg-rancho-buttercup px-8 py-4 text-sm font-bold text-rancho-forest transition-all focus:outline-none focus:ring-2 active:scale-95"
            >
                {{ buttonText }}
                <MoveRight :size="18" aria-hidden="true" />
            </Link>
        </div>
    </div>
</template>

<style scoped>
    .animate-in {
        opacity: 1 !important;
        transform: translateY(0) !important;
    }
</style>
