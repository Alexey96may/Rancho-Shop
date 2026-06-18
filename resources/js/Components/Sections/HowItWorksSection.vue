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
                    class="text-slate-900 mb-4 text-3xl font-bold md:text-4xl"
                >
                    {{ block.title }}
                </h2>
                <p v-if="block.subtitle" class="text-slate-600 mx-auto max-w-2xl text-lg">
                    {{ block.subtitle }}
                </p>
            </div>

            <ol class="relative grid grid-cols-1 gap-8 md:grid-cols-3" role="list">
                <div
                    class="bg-green-100 absolute left-0 top-12 -z-10 hidden h-0.5 w-full md:block"
                    aria-hidden="true"
                ></div>

                <li
                    v-for="item in block.content"
                    :key="item.step"
                    class="group flex flex-col items-center text-center"
                >
                    <div
                        class="bg-green-600 text-white shadow-lg shadow-green-200 mb-6 flex h-16 w-16 items-center justify-center rounded-full text-2xl font-bold transition-transform group-hover:scale-110"
                        aria-hidden="true"
                    >
                        {{ item.step }}
                    </div>

                    <h3 class="text-slate-800 mb-2 text-xl font-semibold">
                        {{ item.title }}
                    </h3>
                    <p class="text-slate-600 px-4 leading-relaxed">
                        {{ item.desc }}
                    </p>

                    <span class="sr-only">Шаг {{ item.step }}: {{ item.title }}</span>
                </li>
            </ol>
        </div>
    </AppContainer>
</template>
