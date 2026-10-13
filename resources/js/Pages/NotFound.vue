<script setup lang="ts">
    import { Head, Link } from '@inertiajs/vue3';

    import { ArrowLeftIcon, HomeIcon, MilkIcon } from 'lucide-vue-next';

    const goBack = () => {
        if (typeof window !== 'undefined') {
            window.history.back();
        }
    };
</script>

<template>
    <AppContainer class="min-h-screen bg-rancho-paper">
        <Head title="Страница не найдена — 404" />

        <div class="flex min-h-screen items-center justify-center px-4 py-10 sm:px-6" role="main">
            <div class="w-full max-w-2xl space-y-8 text-center sm:space-y-12">
                <!-- 404 + бидон -->
                <div class="group relative inline-block">
                    <div
                        v-scroll-reveal
                        class="text-rancho-ink-200 select-none text-[7rem] font-black leading-none transition-transform duration-700 group-hover:scale-105 sm:text-[12rem] md:text-[18rem]"
                        aria-hidden="true"
                    >
                        404
                    </div>

                    <div
                        v-scroll-reveal
                        class="absolute inset-0 flex items-center justify-center"
                        aria-hidden="true"
                    >
                        <div
                            class="animate-spill shadow-2xl flex h-24 w-24 -rotate-12 items-center justify-center rounded-[2rem] bg-rancho-forest shadow-rancho-forest/30 transition-transform duration-500 group-hover:rotate-0 sm:h-32 sm:w-32 sm:rounded-[2.5rem] md:h-48 md:w-48 md:rounded-[3rem]"
                        >
                            <MilkIcon
                                class="h-12 w-12 text-rancho-paper sm:h-16 sm:w-16 md:h-24 md:w-24"
                            />
                        </div>
                    </div>
                </div>

                <!-- Текст -->
                <div v-scroll-reveal class="relative z-10 space-y-4">
                    <h1
                        class="text-3xl font-black uppercase italic tracking-tighter text-rancho-forest sm:text-4xl md:text-6xl"
                    >
                        Бидон пуст!
                    </h1>
                    <p class="text-rancho-ink-500 mx-auto max-w-sm text-base font-bold sm:text-lg">
                        Похоже, эта страница «скисла» или её никогда не было в нашем ассортименте.
                    </p>
                </div>

                <!-- Кнопки -->
                <div
                    v-scroll-reveal
                    class="flex flex-col items-center justify-center gap-3 sm:flex-row sm:gap-4"
                >
                    <button
                        type="button"
                        @click="goBack"
                        class="border-rancho-ink-300 hover:bg-rancho-ink-50 flex w-full items-center justify-center gap-2 rounded-2xl border-2 bg-white px-6 py-4 text-xs font-black uppercase tracking-widest text-rancho-forest transition-all hover:border-rancho-forest focus:outline-none focus-visible:ring-4 focus-visible:ring-rancho-pine/30 active:scale-95 sm:w-auto sm:rounded-3xl sm:px-8 sm:py-5"
                    >
                        <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                        Назад
                    </button>

                    <Link
                        :href="route('home')"
                        class="shadow-lg hover:shadow-xl flex w-full items-center justify-center gap-2 rounded-2xl bg-rancho-pine px-6 py-4 text-xs font-black uppercase tracking-widest text-white shadow-rancho-pine/20 transition-all hover:bg-rancho-forest focus:outline-none focus-visible:ring-4 focus-visible:ring-rancho-pine/40 active:scale-95 sm:w-auto sm:rounded-3xl sm:px-8 sm:py-5"
                    >
                        <HomeIcon class="h-4 w-4" aria-hidden="true" />
                        На ферму
                    </Link>
                </div>

                <!-- Footer -->
                <footer
                    class="text-rancho-ink-400 pt-8 text-[10px] font-black uppercase tracking-[0.4em] sm:pt-12"
                >
                    Rancho Dairy Product — 2026
                </footer>
            </div>
        </div>
    </AppContainer>
</template>

<style scoped>
    @keyframes spill {
        0%,
        100% {
            transform: translateY(0) rotate(-12deg);
        }
        50% {
            transform: translateY(-15px) rotate(-8deg);
        }
    }

    .animate-spill {
        animation: spill 5s ease-in-out infinite;
    }

    @media (prefers-reduced-motion: reduce) {
        .animate-spill {
            animation: none;
        }
    }
</style>
