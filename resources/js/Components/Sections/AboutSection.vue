<script setup lang="ts">
    import { Quote } from 'lucide-vue-next';
</script>

<template>
    <AppContainer tag="section" class="bg-white py-24" aria-labelledby="philosophy-heading">
        <div class="grid grid-cols-1 items-center gap-16 lg:grid-cols-2">
            <div class="relative order-2 lg:order-1" aria-hidden="true">
                <div
                    class="bg-rancho-buttercup/20 absolute -bottom-6 -right-6 -z-10 h-64 w-64 animate-pulse rounded-3xl"
                ></div>

                <div class="overflow-hidden rounded-[2rem] shadow-2xl">
                    <img
                        src="/images/philosophy-farm.jpg"
                        alt="Панорамный вид на наши пастбища, где животные находятся на свободном выпасе"
                        class="h-full w-full object-cover transition-transform duration-700 hover:scale-105"
                    />
                </div>

                <div
                    class="bg-rancho-forest absolute -left-6 -top-10 hidden rounded-2xl p-6 text-white shadow-xl md:block"
                >
                    <span class="block text-3xl font-black italic">100%</span>
                    <span class="text-xs uppercase tracking-widest opacity-80"
                        >Натуральный цикл</span
                    >
                </div>
            </div>

            <div class="order-1 lg:order-2">
                <div
                    class="bg-rancho-paper text-rancho-olive mb-6 inline-flex h-12 w-12 items-center justify-center rounded-xl"
                    aria-hidden="true"
                >
                    <Quote :size="24" fill="currentColor" />
                </div>

                <h2
                    id="philosophy-heading"
                    class="text-rancho-forest mb-8 text-4xl font-bold leading-tight lg:text-5xl"
                >
                    Больше, чем просто <span class="text-rancho-buttercup italic">ферма</span>
                </h2>

                <div class="text-rancho-olive/80 space-y-6 text-lg leading-relaxed">
                    <p>
                        Мы верим, что вкус продукта напрямую зависит от счастья того, кто его дал.
                        На «Ранчо» коровы не просто «единицы производства», а личности со своими
                        именами и даже отпусками.
                    </p>
                    <p>
                        Наш путь — это
                        <strong class="text-rancho-forest font-semibold"
                            >Socrates without Plato</strong
                        >
                        в мире сельского хозяйства: мы ищем истину в чистоте продукта, не
                        приукрашивая его химией или маркетинговыми уловками. Только честный удой и
                        свежий сбор.
                    </p>
                </div>

                <div class="mt-10">
                    <button
                        type="button"
                        class="bg-rancho-forest hover:bg-rancho-buttercup hover:text-rancho-forest focus:ring-rancho-buttercup/40 rounded-full px-8 py-4 font-bold text-white transition-all focus:outline-none focus:ring-4"
                        aria-label="Узнать подробную историю создания нашего хозяйства"
                    >
                        Наша история
                    </button>
                </div>
            </div>
        </div>
    </AppContainer>
</template>
