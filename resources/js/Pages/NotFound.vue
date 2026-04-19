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
    <Head title="Страница не найдена — 404" />

    <div
        class="flex min-h-screen items-center justify-center bg-white p-6 selection:bg-slate-900 selection:text-white"
    >
        <main class="w-full max-w-2xl space-y-12 text-center" role="main">
            <div class="group relative inline-block">
                <div
                    class="select-none text-[12rem] font-black leading-none text-slate-100 transition-transform duration-700 group-hover:scale-105 md:text-[20rem]"
                >
                    404
                </div>

                <div class="absolute inset-0 flex items-center justify-center">
                    <div
                        class="shadow-2xl flex h-32 w-32 -rotate-12 items-center justify-center rounded-[3rem] bg-slate-900 shadow-slate-900/40 transition-transform duration-500 group-hover:rotate-0 md:h-48 md:w-48"
                    >
                        <MilkIcon class="h-16 w-16 text-white md:h-24 md:w-24" aria-hidden="true" />
                    </div>
                </div>
            </div>

            <div class="relative z-10 space-y-4">
                <h1
                    class="text-4xl font-black uppercase italic tracking-tighter text-slate-900 md:text-6xl"
                >
                    Бидон пуст!
                </h1>
                <p class="mx-auto max-w-sm text-lg font-bold text-slate-400">
                    Похоже, эта страница «скисла» или её никогда не было в нашем ассортименте.
                </p>
            </div>

            <div class="flex flex-col items-center justify-center gap-4 sm:flex-row">
                <button
                    @click="goBack"
                    class="flex w-full items-center justify-center gap-2 rounded-3xl border-2 border-slate-200 bg-white px-8 py-5 text-xs font-black uppercase tracking-widest text-slate-900 transition-all hover:border-slate-900 active:scale-95 sm:w-auto"
                >
                    <ArrowLeftIcon class="h-4 w-4" />
                    Назад
                </button>

                <Link
                    :href="route('home')"
                    class="shadow-xl flex w-full items-center justify-center gap-2 rounded-3xl bg-slate-900 px-10 py-5 text-xs font-black uppercase tracking-widest text-white shadow-slate-900/20 transition-all hover:bg-orange-500 active:scale-95 sm:w-auto"
                >
                    <HomeIcon class="h-4 w-4" />
                    На ферму
                </Link>
            </div>

            <footer class="pt-12 text-[10px] font-black uppercase tracking-[0.4em] text-slate-300">
                Rancho Dairy Product — 2026
            </footer>
        </main>
    </div>
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

    .bg-slate-900 {
        animation: spill 5s ease-in-out infinite;
    }
</style>
