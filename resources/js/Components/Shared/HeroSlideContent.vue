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
    <div class="relative z-10 flex h-full items-center sm:px-6 lg:px-20">
        <div
            class="animate-in shadow-xl flex max-w-full translate-y-[-10%] flex-col gap-6 rounded-xl border border-rancho-olive bg-rancho-paper/10 p-4 backdrop-blur-lg md:max-w-sm lg:p-8 2xl:max-w-lg"
        >
            <h2
                :class="[
                    'text-center text-sm font-bold leading-tight sm:text-lg lg:text-2xl',
                    titleColor || 'text-rancho-forest',
                ]"
            >
                {{ title }}
            </h2>

            <p class="text-xs leading-relaxed text-zinc-900 sm:text-sm lg:text-lg">
                {{ description }}
            </p>

            <Link
                :href="route(routeName)"
                :aria-label="`${buttonText}: ${title}`"
                class="inline-flex items-center gap-3 self-center rounded-xl bg-rancho-buttercup px-6 py-3 text-xs font-bold text-rancho-forest transition-colors duration-200 hover:bg-rancho-pine hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-rancho-pine focus-visible:ring-offset-2 active:scale-95 sm:rounded-full sm:px-8 sm:py-4 sm:text-sm"
            >
                {{ buttonText }}
                <MoveRight :size="18" aria-hidden="true" />
            </Link>
        </div>
    </div>
</template>

<style scoped>
    .animate-in {
        opacity: 1;
        transform: translateY(0);
    }

    .swiper-slide-active .animate-in {
        animation: slideIn 1s ease forwards;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
