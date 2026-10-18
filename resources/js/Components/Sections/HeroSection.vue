<script setup lang="ts">
    import { onMounted, reactive } from 'vue';

    import 'swiper/css';
    import 'swiper/css/effect-fade';
    import 'swiper/css/navigation';
    import 'swiper/css/pagination';
    import { Autoplay, EffectFade, Navigation, Pagination } from 'swiper/modules';
    import { Swiper, SwiperSlide } from 'swiper/vue';

    import HeroSlideContent from '@/Components/Shared/HeroSlideContent.vue';

    const modules = [Autoplay, EffectFade, Navigation, Pagination];

    interface Slide {
        id: number;
        title: string;
        description: string;
        buttonText: string;
        route: string;
        image: string;
        color: string;
    }

    const slides: Slide[] = [
        {
            id: 1,
            title: 'Свежесть фермерского утра',
            description:
                'Натуральное молоко, крафтовые сыры и домашнее масло с нашего пастбища — к вашему столу.',
            buttonText: 'В магазин',
            route: 'catalog.index',
            image: '/images/hero/slide1.jpg',
            color: 'text-rancho-forest',
        },
        {
            id: 2,
            title: 'Познакомьтесь с нашими жителями',
            description:
                'У каждого животного здесь есть имя, характер и своя история. Давайте знакомиться!',
            buttonText: 'К животным',
            route: 'animals.index',
            image: '/images/hero/slide2.jpg',
            color: 'text-rancho-forest',
        },
    ];

    // ─────────────── Состояние загрузки картинок ───────────────
    const loaded = reactive<Record<number, boolean>>({});

    function markLoaded(id: number) {
        if (loaded[id]) return;
        // два rAF — гарантия, что opacity-0 отрисован до смены на opacity-100
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                loaded[id] = true;
            });
        });
    }

    onMounted(() => {
        if (typeof window === 'undefined') return;

        slides.forEach((slide) => {
            const img = new Image();
            img.onload = () => markLoaded(slide.id);
            img.onerror = () => {
                // при ошибке убираем скелетон, чтобы не висел вечно
                loaded[slide.id] = true;
            };
            img.src = slide.image;

            // если картинка уже в кэше, onload мог не выстрелить
            if (img.complete && img.naturalWidth > 0) {
                markLoaded(slide.id);
            }
        });
    });
</script>

<template>
    <AppContainer class="relative mt-4 py-4 sm:mt-0 lg:pb-8">
        <section
            class="relative w-full overflow-hidden md:aspect-[213/117]"
            aria-label="Популярные разделы сайта"
        >
            <swiper
                :modules="modules"
                :slides-per-view="1"
                :effect="'fade'"
                :speed="1500"
                :loop="true"
                :pagination="{ el: '.custom-pagination', clickable: true }"
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
                        <!-- Контейнер картинки -->
                        <div
                            class="relative mb-6 aspect-[213/117] w-full overflow-hidden sm:mb-0 md:absolute md:inset-0"
                        >
                            <!-- Скелетон под фоном -->
                            <div
                                v-if="!loaded[slide.id]"
                                class="hero-skeleton absolute inset-0"
                                aria-hidden="true"
                            />

                            <!-- Фон: тот же bg-cover, что был, + плавное проявление -->
                            <div
                                class="absolute inset-0 bg-cover bg-center bg-no-repeat transition-opacity duration-700 ease-out"
                                :class="loaded[slide.id] ? 'opacity-100' : 'opacity-0'"
                                :style="{ backgroundImage: `url(${slide.image})` }"
                                aria-hidden="true"
                            ></div>
                        </div>

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
        margin: 0;
        background-color: #393939;
        opacity: 0.5;
        border-radius: 9999px;
        transition:
            transform 0.3s ease,
            opacity 0.3s ease,
            background-color 0.3s ease;
        cursor: pointer;
    }

    @media (max-width: 640px) {
        .custom-pagination :deep(.swiper-pagination-bullet) {
            width: 6px;
            height: 6px;
        }
    }

    .custom-pagination :deep(.swiper-pagination-bullet-active) {
        background-color: #d4a373;
        opacity: 1;
        transform: scale(1.3);
    }

    /* Слайды в fade-режиме: накладываются друг на друга */
    :deep(.swiper-fade .swiper-slide) {
        opacity: 0 !important;
        transition: opacity 0.8s ease;
        pointer-events: none;
    }

    :deep(.swiper-fade .swiper-slide-active) {
        opacity: 1 !important;
        pointer-events: auto;
    }

    .swiper-wrapper {
        transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1) !important;
    }

    .hero-skeleton {
        background-color: #fcfaf5; /* rancho-paper */
        background-image: linear-gradient(
            110deg,
            #fcfaf5 0%,
            #fcfaf5 35%,
            #f2ede0 50%,
            #fcfaf5 65%,
            #fcfaf5 100%
        );
        background-size: 200% 100%;
        border-radius: 0.75rem;
        background-position: 100% 0;
        animation: hero-shimmer 1.8s ease-in-out infinite;
    }

    @keyframes hero-shimmer {
        0% {
            background-position: 100% 0;
        }
        100% {
            background-position: -100% 0;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .hero-skeleton {
            animation: none;
            background-image: none;
        }
    }
</style>
