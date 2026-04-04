<script setup lang="ts">
    import { MoveRight } from 'lucide-vue-next';
    import 'swiper/css';
    import 'swiper/css/effect-fade';
    import 'swiper/css/navigation';
    import { Autoplay, EffectFade, Navigation } from 'swiper/modules';
    import { Swiper, SwiperSlide } from 'swiper/vue';

    const modules = [Autoplay, EffectFade, Navigation];

    const slides = [
        {
            id: 1,
            title: 'Свежесть фермерского утра',
            description:
                'Натуральное молоко, крафтовые сыры и домашнее масло прямо с нашего пастбища в ваше лукошко.',
            buttonText: 'В магазин',
            link: '/shop',
            image: '/images/hero/slide1.jpg',
            color: 'text-rancho-forest',
        },
        {
            id: 2,
            title: 'Познакомьтесь с нашими жителями',
            description:
                'У каждой нашей коровы есть имя и своя история. Послушайте, как поет Зорька или резвится Лучик.',
            buttonText: 'К животным',
            link: '/animals',
            image: '/images/hero/slide2.jpg',
            color: 'text-rancho-forest',
        },
    ];
</script>

<template>
    <AppContainer>
        <section class="relative w-full overflow-hidden md:aspect-[213/117]">
            <swiper
                :modules="modules"
                :slides-per-view="1"
                :effect="'fade'"
                :speed="1500"
                :loop="true"
                :autoplay="{ delay: 5000, disableOnInteraction: false, pauseOnMouseEnter: true }"
                class="h-full w-full"
            >
                <swiper-slide v-for="slide in slides" :key="slide.id">
                    <div class="relative h-full w-full">
                        <div
                            class="aspect-[213/117] w-full bg-contain bg-center bg-no-repeat transition-transform duration-700 md:absolute md:inset-0"
                            :style="{ backgroundImage: `url(${slide.image})` }"
                        >
                            <div
                                class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/50 to-transparent"
                            ></div>
                        </div>

                        <div class="relative z-10 flex h-full items-center px-6 lg:px-20">
                            <div
                                class="animate-in bg-rancho-paper/10 fade-in slide-in-from-bottom-10 fill-mode-forwards border-rancho-olive max-w-full translate-y-[-10%] rounded-xl border p-4 opacity-0 shadow-2xl backdrop-blur-lg duration-1000 md:max-w-sm lg:p-8 2xl:max-w-lg"
                            >
                                <h1
                                    :class="[
                                        'mb-6 text-lg font-bold leading-tight lg:text-2xl',
                                        slide.color,
                                    ]"
                                >
                                    {{ slide.title }}
                                </h1>
                                <p class="mb-8 text-sm leading-relaxed text-white/90 lg:text-lg">
                                    {{ slide.description }}
                                </p>

                                <a
                                    :href="slide.link"
                                    class="bg-rancho-buttercup text-rancho-forest inline-flex items-center gap-3 rounded-full px-8 py-4 text-sm font-bold transition-all hover:bg-white hover:shadow-xl active:scale-95"
                                >
                                    {{ slide.buttonText }}
                                    <MoveRight :size="18" />
                                </a>
                            </div>
                        </div>
                    </div>
                </swiper-slide>
            </swiper>

            <div class="absolute bottom-10 right-10 z-20 hidden gap-4 lg:flex"></div>
        </section>
    </AppContainer>
</template>

<style scoped>
    /* Добавим мягкую анимацию появления для контента внутри слайда */
    .swiper-slide-active .animate-in {
        opacity: 1 !important;
        transform: translateY(0) !important;
    }

    .swiper-wrapper {
        transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
</style>
