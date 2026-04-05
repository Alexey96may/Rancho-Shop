<script setup lang="ts">
    import { Heart, Leaf, ShieldCheck, Truck } from 'lucide-vue-next';

    // Описываем структуру преимущества
    interface Feature {
        id: number;
        title: string;
        description: string;
        icon: any;
        colorClass: string;
    }

    const features: Feature[] = [
        {
            id: 1,
            title: 'Утренний сбор',
            description:
                'Продукты попадают в доставку спустя 3-4 часа после сбора или удоя. Свежее не бывает.',
            icon: Leaf,
            colorClass: 'bg-emerald-50 text-emerald-600',
        },
        {
            id: 2,
            title: 'Честный состав',
            description:
                'Никакого сахара, антибиотиков или ГМО. Только то, что дала природа и наши пастбища.',
            icon: ShieldCheck,
            colorClass: 'bg-blue-50 text-blue-600',
        },
        {
            id: 3,
            title: 'Знаем каждого в лицо',
            description:
                'Вы можете прослушать голос коровы или прочитать биографию курицы, создавшей ваш завтрак.',
            icon: Heart,
            colorClass: 'bg-rose-50 text-rose-600',
        },
        {
            id: 4,
            title: 'Бережная доставка',
            description:
                'Специальные термо-боксы сохраняют идеальную температуру молока и хрупкость яиц до вашей двери.',
            icon: Truck,
            colorClass: 'bg-amber-50 text-amber-600',
        },
    ];
</script>

<template>
    <AppContainer tag="section" class="bg-white py-20" aria-labelledby="features-section-title">
        <div class="mb-16 text-center lg:text-left">
            <h2
                id="features-section-title"
                class="text-3xl font-bold text-rancho-forest lg:text-4xl"
            >
                Почему выбирают наше хозяйство
            </h2>
            <p class="mt-4 text-rancho-olive/60">
                Забота о качестве на каждом этапе — от пастбища до вашего стола.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-12 md:grid-cols-2 lg:grid-cols-4" role="list">
            <div
                v-for="feature in features"
                :key="feature.id"
                class="group flex flex-col items-center text-center lg:items-start lg:text-left"
                role="listitem"
            >
                <div
                    :class="[
                        'mb-6 flex h-16 w-16 items-center justify-center rounded-2xl transition-transform duration-500 group-hover:rotate-3 group-hover:scale-110',
                        feature.colorClass,
                    ]"
                    aria-hidden="true"
                >
                    <component :is="feature.icon" :size="32" stroke-width="1.5" />
                </div>

                <h3 class="mb-3 text-xl font-bold text-rancho-forest">
                    {{ feature.title }}
                </h3>

                <p class="text-sm leading-relaxed text-rancho-olive/70 lg:text-base">
                    {{ feature.description }}
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
