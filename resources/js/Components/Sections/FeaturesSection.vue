<script setup lang="ts">
    import { Heart, Leaf, ShieldCheck, Truck } from 'lucide-vue-next';

    import type { LandingBlock } from '@/types';

    defineProps<{
        values: LandingBlock;
    }>();

    const iconMap = {
        Heart,
        Leaf,
        ShieldCheck,
        Truck,
    } as const;
</script>

<template>
    <AppContainer
        tag="section"
        class="bg-white py-12 sm:py-20"
        aria-labelledby="features-section-title"
    >
        <div class="mb-16 text-center lg:text-left">
            <h2
                id="features-section-title"
                class="text-xl font-bold text-rancho-forest sm:text-4xl"
                v-html="values?.title"
            ></h2>
            <p class="mt-4 text-sm text-rancho-olive/60 sm:text-lg" v-html="values?.subtitle"></p>
        </div>

        <div class="grid grid-cols-1 gap-12 md:grid-cols-2 lg:grid-cols-4" role="list">
            <div
                v-for="(feature, index) in values?.content"
                :key="index"
                class="group flex flex-col items-center text-center lg:items-start lg:text-left"
                role="listitem"
            >
                <div
                    :class="[
                        'mb-6 flex h-8 w-8 items-center justify-center rounded-2xl transition-transform duration-500 group-hover:rotate-3 group-hover:scale-110 sm:h-16 sm:w-16',
                    ]"
                    aria-hidden="true"
                >
                    <component
                        :is="iconMap[feature.icon as keyof typeof iconMap]"
                        :size="28"
                        stroke-width="1.5"
                    />
                </div>

                <h3 class="mb-3 text-sm font-bold text-rancho-forest sm:text-lg">
                    {{ feature?.title }}
                </h3>

                <p class="text-sm leading-relaxed text-rancho-olive/70 sm:text-base">
                    {{ feature?.desc }}
                </p>
            </div>
        </div>

        <div class="mt-6 border-t border-rancho-paper pt-10 text-center sm:mt-20">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-rancho-olive/40">
                С гордостью из нашего хозяйства — к вашему столу
            </p>
        </div>
    </AppContainer>
</template>
