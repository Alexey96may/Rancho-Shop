<script setup lang="ts">
    import type { LandingBlock } from '@/types';

    interface Props {
        block: LandingBlock;
    }

    const props = defineProps<Props>();
</script>

<template>
    <AppContainer tag="section" class="bg-white py-20" aria-labelledby="how-it-works-title">
        <div class="container mx-auto px-4">
            <div class="mb-12 text-center">
                <h2
                    id="how-it-works-title"
                    class="mb-4 text-3xl font-bold text-slate-900 md:text-4xl"
                >
                    {{ block.title }}
                </h2>
                <p v-if="block.subtitle" class="mx-auto max-w-2xl text-lg text-slate-600">
                    {{ block.subtitle }}
                </p>
            </div>

            <ol class="relative grid grid-cols-1 gap-8 md:grid-cols-3" role="list">
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
                        class="shadow-lg mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-green-600 text-2xl font-bold text-white shadow-green-200 transition-transform group-hover:scale-110"
                        aria-hidden="true"
                    >
                        {{ item.step }}
                    </div>

                    <h3 class="mb-2 text-xl font-semibold text-slate-800">
                        {{ item.title }}
                    </h3>
                    <p class="px-4 leading-relaxed text-slate-600">
                        {{ item.desc }}
                    </p>

                    <span class="sr-only">Шаг {{ item.step }}: {{ item.title }}</span>
                </li>
            </ol>
        </div>
    </AppContainer>
</template>
