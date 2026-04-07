<script setup lang="ts">
    import { onMounted, onUnmounted, ref } from 'vue';

    import TheFooter from '@/Components/Sections/TheFooter.vue';
    import TheHeader from '@/Components/Sections/TheHeader.vue';

    const showScrollTop = ref(false);

    const handleScroll = () => {
        showScrollTop.value = window.scrollY > 500;
    };

    const scrollToTop = () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    onMounted(() => {
        window.addEventListener('scroll', handleScroll);
    });

    onUnmounted(() => {
        window.removeEventListener('scroll', handleScroll);
    });
</script>

<template>
    <div
        class="flex min-h-screen flex-col bg-rancho-paper selection:bg-rancho-buttercup selection:text-rancho-forest"
    >
        <TheHeader />

        <main class="flex-grow">
            <transition name="fade" mode="out-in">
                <slot />
            </transition>
        </main>

        <TheFooter class="mt-20" />

        <Transition name="slide-up">
            <button
                v-show="showScrollTop"
                @click="scrollToTop"
                class="border-white shadow-2xl fixed bottom-10 right-10 z-40 rounded-full border-2 bg-rancho-buttercup p-4 text-rancho-forest transition-all hover:scale-110 active:scale-95"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-6 w-6"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M5 10l7-7m0 0l7 7m-7-7v18"
                    />
                </svg>
            </button>
        </Transition>
    </div>
</template>

<style scoped>
    /* Фон всегда прибит к экрану и не двигается при скролле */
    .sky-wrapper {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100vh; /* Высота строго по размеру экрана */
        transition: background 3s ease-in-out;
        z-index: -1; /* Уходим на самый задний план */
        overflow: hidden;
    }

    /* Состояния неба */
    .day {
        background: linear-gradient(to bottom, #73c8ef 0%, #b3e0f2 100%);
    }
    .night {
        background: linear-gradient(to bottom, #131836 0%, #293462 100%);
    }

    /* Солнце и Луна */
    .sun,
    .moon {
        position: absolute;
        width: 80px;
        height: 80px;
        border-radius: 50%;
        top: 10%;
        left: 10%;
        transition: all 3s ease-in-out;
    }

    .sun {
        background: #fdfc97;
        box-shadow: 0 0 40px #fdfc97;
    }

    .night .sun {
        transform: translateY(110vh); /* Уходит за нижнюю границу экрана */
        opacity: 0;
    }

    .moon {
        background: #d9d9d9;
        box-shadow: 0 0 20px rgba(217, 217, 217, 0.5);
        transform: translateY(-110vh); /* Спрятана выше экрана */
        opacity: 0;
    }

    .night .moon {
        transform: translateY(0);
        opacity: 1;
    }

    /* Звезды */
    .stars {
        position: absolute;
        width: 100%;
        height: 100%;
        opacity: 0;
        transition: opacity 2s ease-in-out;
    }
    .night .stars {
        opacity: 1;
    }

    .star {
        position: absolute;
        background: white;
        border-radius: 50%;
        animation: twinkle 2s infinite;
    }

    /* Контейнер для твоего приложения (Layout) */
    /* Мы НЕ вешаем сюда fixed, чтобы страница могла скроллиться */
    .content-overlay {
        position: relative;
        z-index: 1;
        min-height: 100vh; /* Минимальная высота — экран, но может быть больше */
        width: 100%;
        overflow-y: auto; /* Разрешаем вертикальный скролл */
    }

    @keyframes twinkle {
        0%,
        100% {
            opacity: 0.3;
            transform: scale(0.8);
        }
        50% {
            opacity: 1;
            transform: scale(1.1);
        }
    }
</style>
