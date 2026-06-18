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
    <AppContainer tag="section" class="bg-white py-20" aria-labelledby="features-section-title">
        <div class="mb-16 text-center lg:text-left">
            <h2
                id="features-section-title"
                class="text-3xl font-bold text-rancho-forest lg:text-4xl"
                v-html="values?.title"
            ></h2>
            <p class="mt-4 text-rancho-olive/60" v-html="values?.subtitle"></p>
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
                        'mb-6 flex h-16 w-16 items-center justify-center rounded-2xl transition-transform duration-500 group-hover:rotate-3 group-hover:scale-110',
                    ]"
                    aria-hidden="true"
                >
                    <component
                        :is="iconMap[feature.icon as keyof typeof iconMap]"
                        :size="32"
                        stroke-width="1.5"
                    />
                </div>

                <h3 class="mb-3 text-xl font-bold text-rancho-forest">
                    {{ feature?.title }}
                </h3>

                <p class="text-sm leading-relaxed text-rancho-olive/70 lg:text-base">
                    {{ feature?.desc }}
                </p>
            </div>
        </div>

        <div class="mt-20 border-t border-rancho-paper pt-10 text-center">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-rancho-olive/40">
                С гордостью из нашего хозяйства — к вашему столу
            </p>
        </div>
    </AppContainer>
</template>
