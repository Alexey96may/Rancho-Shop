<script setup lang="ts">
    import { ref } from 'vue';

    import { Howl } from 'howler';
    import { ShoppingBasket, Volume2 } from 'lucide-vue-next';

    import HeroSection from '@/Components/Sections/HeroSection.vue';
    import MainLayout from '@/Layouts/MainLayout.vue';
    import { Cow, Product } from '@/types';

    defineOptions({ layout: MainLayout });

    interface Props {
        cows: Cow[];
        products: Product[];
    }

    const { cows = [], products = [] } = defineProps<Props>();

    // Логика звука через Howler
    const playMoo = (audioPath: string | null | undefined): void | undefined => {
        if (!audioPath) return;

        const sound = new Howl({
            src: [audioPath],
            html5: true,
            volume: 0.5,
        });
        sound.play();
    };

    const addToCart = (product: Product): void => {
        console.log('В лукошке:', product.name);
    };
</script>

<template>
    <HeroSection></HeroSection>
    <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4">
        <div
            v-for="product in products"
            :key="product.id"
            class="group rounded-3xl bg-white p-4 shadow-sm transition-all hover:shadow-md"
        >
            <div class="mt-4 flex items-center justify-between">
                <span class="text-rancho-pine text-xl font-bold">{{ product.price }} ₽</span>
                <button
                    @click="addToCart(product)"
                    class="bg-rancho-paper hover:bg-rancho-buttercup text-rancho-forest rounded-full p-3 transition-colors"
                >
                    <ShoppingBasket :size="24" :stroke-width="1.5" />
                </button>
            </div>
        </div>
    </div>

    <div v-for="cow in cows" :key="cow.id" class="text-center">
        <button
            v-if="cow.voice_url"
            @click="playMoo(cow.voice_url)"
            class="text-rancho-buttercup mx-auto mt-4 flex items-center gap-2 text-xs uppercase tracking-widest transition-opacity hover:opacity-80"
        >
            <Volume2 :size="16" />
            Послушать (Мууу)
        </button>
    </div>
</template>
