<script setup lang="ts">
    import { MoveRight } from 'lucide-vue-next';
    import 'swiper/css';
    import 'swiper/css/effect-fade';
    import 'swiper/css/navigation';
    import 'swiper/css/pagination';
    import { Autoplay, EffectFade, Navigation, Pagination } from 'swiper/modules';
    import { Swiper, SwiperSlide } from 'swiper/vue';

    import HeroSlideContent from '@/Components/Shared/HeroSlideContent.vue';

    const modules = [Autoplay, EffectFade, Navigation, Pagination];

    const slides = [
        {
            id: 1,
            title: 'Свежесть фермерского утра',
            description:
                'Натуральное молоко, крафтовые сыры и домашнее масло прямо с нашего пастбища в ваше лукошко.',
            buttonText: 'В магазин',
            route: 'catalog.index',
            image: '/images/hero/slide1.jpg',
            color: 'text-rancho-forest',
        },
        {
            id: 2,
            title: 'Познакомьтесь с нашими жителями',
            description:
                'У каждой нашей коровы есть имя и своя история. Послушайте, как поет Зорька или резвится Лучик.',
            buttonText: 'К животным',
            route: 'animals.index',
            image: '/images/hero/slide2.jpg',
            color: 'text-rancho-forest',
        },
    ];
</script>

<template>
    <AppContainer class="relative pb-14 lg:pb-8">
        <section
            class="pointer-events-none relative w-full overflow-hidden md:aspect-[213/117]"
            aria-label="Популярные разделы сайта"
        >
            <swiper
                :modules="modules"
                :slides-per-view="1"
                :effect="'fade'"
                :speed="1500"
                :loop="true"
                :pagination="{
                    el: '.custom-pagination',
                    clickable: true,
                }"
                role="region"
                :autoplay="{ delay: 5000, disableOnInteraction: false, pauseOnMouseEnter: true }"
                class="h-full w-full"
            >
                <swiper-slide
                    v-for="slide in slides"
                    :key="slide.id"
                    role="group"
                    aria-roledescription="slide"
                    :aria-label="`Слайд ${slide.id} из ${slides.length}`"
                >
                    <div class="relative h-full w-full">
                        <div
                            class="aspect-[213/117] w-full bg-contain bg-center bg-no-repeat transition-transform duration-700 md:absolute md:inset-0"
                            :style="{ backgroundImage: `url(${slide.image})` }"
                            aria-hidden="true"
                        ></div>

                        <HeroSlideContent
                            :title="slide.title"
                            :description="slide.description"
                            :button-text="slide.buttonText"
                            :routeName="slide.route"
                            :title-color="slide.color"
                        />
                    </div>
                </swiper-slide>
            </swiper>

            <div class="absolute bottom-10 right-10 z-20 hidden gap-4 lg:flex"></div>
        </section>

        <div class="custom-pagination z-10 rounded-full py-2 lg:gap-4"></div>
    </AppContainer>
</template>

<style scoped>
    .custom-pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 14px;
        padding: 30px 0;
        min-width: 80px;
        height: 32px;
    }

    .custom-pagination :deep(.swiper-pagination-bullet) {
        width: 10px;
        height: 10px;
        margin: 0; /* gap уже даёт отступ */
        background-color: #393939;
        opacity: 0.5;
        border-radius: 9999px;
        transition:
            transform 0.3s ease,
            opacity 0.3s ease,
            background-color 0.3s ease;
        cursor: pointer;
    }

    .custom-pagination :deep(.swiper-pagination-bullet-active) {
        background-color: #d4a373;
        opacity: 1;
        transform: scale(1.3);
    }

    .hero-slider :deep(.swiper-pagination-bullets-dynamic) {
        bottom: 40px !important;
        transform: translateX(-50%) scale(1.4);
    }

    .hero-slider :deep(.swiper-pagination) {
        bottom: 40px !important;
    }

    .swiper-slide-active .animate-in {
        opacity: 1 !important;
        transform: translateY(0) !important;
    }

    .swiper-wrapper {
        transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
</style>
