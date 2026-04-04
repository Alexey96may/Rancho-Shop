<template>
    <div
        class="bg-rancho-paper selection:bg-rancho-buttercup selection:text-rancho-forest flex min-h-screen flex-col"
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
                class="bg-rancho-buttercup text-rancho-forest fixed bottom-10 right-10 z-40 rounded-full border-2 border-white p-4 shadow-2xl transition-all hover:scale-110 active:scale-95"
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

<script setup>
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

<style>
    /* Плавный переход между страницами */
    .fade-enter-active,
    .fade-leave-active {
        transition: opacity 0.3s ease;
    }

    .fade-enter-from,
    .fade-leave-to {
        opacity: 0;
    }

    /* Анимация кнопки наверх */
    .slide-up-enter-active,
    .slide-up-leave-active {
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .slide-up-enter-from,
    .slide-up-leave-to {
        transform: translateY(100px);
        opacity: 0;
    }
</style>
