<script setup lang="ts">
    import type { LandingBlock } from '@/types';

    interface Props {
        block: LandingBlock;
    }

    const props = defineProps<Props>();
</script>

<template>
    <section class="bg-white py-16 sm:py-24" aria-labelledby="how-it-works-title">
        <AppContainer>
            <div class="container mx-auto px-4">
                <div class="mb-12 text-center">
                    <h2
                        id="how-it-works-title"
                        class="text-xl font-bold text-rancho-forest sm:text-4xl"
                    >
                        {{ block.title }}
                    </h2>
                    <p
                        v-if="block.subtitle"
                        class="mx-auto max-w-2xl text-sm text-slate-600 sm:text-lg"
                    >
                        {{ block.subtitle }}
                    </p>
                </div>

                <ol class="relative grid grid-cols-1 gap-16 sm:gap-8 md:grid-cols-3" role="list">
                    <div
                        class="absolute left-0 top-12 -z-10 hidden h-0.5 w-full bg-green-100 md:block"
                        aria-hidden="true"
                    ></div>

                    <li
                        v-for="item in block.content"
                        :key="item.step || undefined"
                        class="group flex flex-col items-center text-center"
                    >
                        <div
                            class="shadow-lg text:xs sm:text:base mb-6 flex h-14 w-14 items-center justify-center rounded-full bg-green-600 text-2xl font-bold text-white shadow-green-200 transition-transform group-hover:scale-110 sm:h-16 sm:w-16"
                            aria-hidden="true"
                        >
                            {{ item.step }}
                        </div>

                        <h3 class="mb-2 text-base font-semibold text-slate-800 sm:text-xl">
                            {{ item.title }}
                        </h3>
                        <p class="px-4 text-xs leading-relaxed text-slate-600 sm:text-base">
                            {{ item.desc }}
                        </p>

                        <span class="sr-only">Шаг {{ item.step }}: {{ item.title }}</span>
                    </li>
                </ol>
            </div></AppContainer
        >
    </section>
</template>
