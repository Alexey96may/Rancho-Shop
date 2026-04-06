<script setup lang="ts">
    import { Quote } from 'lucide-vue-next';

    import type { LandingBlock } from '@/types';

    defineProps<{
        about: LandingBlock;
    }>();
</script>

<template>
    <AppContainer tag="section" class="bg-white py-24" aria-labelledby="philosophy-heading">
        <div class="grid grid-cols-1 items-center gap-16 lg:grid-cols-2">
            <div class="relative order-2 lg:order-1" aria-hidden="true">
                <div
                    class="absolute -bottom-6 -right-6 -z-10 h-64 w-64 animate-pulse rounded-3xl bg-rancho-buttercup/20"
                ></div>

                <div class="shadow-2xl overflow-hidden rounded-[1rem]">
                    <AppImage
                        src="/images/philosophy-farm.jpg"
                        alt="Панорамный вид на наши пастбища, где животные находятся на свободном выпасе"
                        :className="'h-full w-full object-cover transition-transform duration-700 hover:scale-105'"
                    />
                </div>

                <div
                    class="text-white shadow-xl absolute -left-6 -top-10 hidden rounded-2xl bg-rancho-forest p-6 md:block"
                >
                    <span class="block text-3xl font-black italic">100%</span>
                    <span class="text-xs uppercase tracking-widest opacity-80"
                        >Натуральный цикл</span
                    >
                </div>
            </div>

            <div class="order-1 lg:order-2">
                <div
                    class="mb-6 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-rancho-paper text-rancho-olive"
                    aria-hidden="true"
                >
                    <Quote :size="24" fill="currentColor" />
                </div>

                <h2
                    id="philosophy-heading"
                    class="mb-8 text-4xl font-bold leading-tight text-rancho-forest lg:text-5xl"
                    v-html="about?.title"
                ></h2>

                <div class="space-y-6 text-lg leading-relaxed text-rancho-olive/80">
                    <p
                        v-for="(paragraph, index) in about?.content"
                        :key="index"
                        v-html="paragraph?.desc"
                    ></p>
                </div>

                <div class="mt-10">
                    <button
                        type="button"
                        class="text-white rounded-full bg-rancho-forest px-8 py-4 font-bold transition-all hover:bg-rancho-buttercup hover:text-rancho-forest focus:outline-none focus:ring-4 focus:ring-rancho-buttercup/40"
                        aria-label="Узнать подробную историю создания нашего хозяйства"
                    >
                        Наша история
                    </button>
                </div>
            </div>
        </div>
    </AppContainer>
</template>
