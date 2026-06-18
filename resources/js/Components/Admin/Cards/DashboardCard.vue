<script setup lang="ts">
    import type { Component } from 'vue';

    import { Link } from '@inertiajs/vue3';

    const props = defineProps<{
        title: string;
        description: string;
        href: string;
        icon: Component;
        count?: number | string; // Добавим опционально счетчик (напр. кол-во новых заказов)
    }>();

    // Генерируем ID для связи заголовка и описания для скринридеров
    const descriptionId = `desc-${Math.random().toString(36).substr(2, 9)}`;
</script>

<template>
    <Link
        :href="href"
        class="group relative block overflow-hidden rounded-3xl border border-slate-800 bg-slate-900/50 p-6 transition-all duration-300 hover:border-orange-500/50 hover:bg-slate-800/50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-500"
        :aria-label="`${title}. ${description}`"
        :aria-describedby="descriptionId"
    >
        <div class="flex items-start justify-between">
            <div class="relative z-10">
                <div
                    class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-950 text-orange-500 ring-1 ring-slate-800 transition-transform duration-300 group-hover:scale-110 group-hover:bg-orange-600 group-hover:text-white group-hover:ring-orange-500"
                    aria-hidden="true"
                >
                    <component :is="icon" class="h-6 w-6" />
                </div>

                <h2
                    class="text-lg font-black uppercase tracking-tight text-white transition-colors group-hover:text-orange-500"
                >
                    {{ title }}
                </h2>

                <p
                    :id="descriptionId"
                    class="mt-1 text-sm font-medium text-slate-500 group-hover:text-slate-400"
                >
                    {{ description }}
                </p>
            </div>

            <div
                v-if="count !== undefined"
                class="text-4xl font-black text-slate-800 transition-colors group-hover:text-orange-900/30"
                aria-hidden="true"
            >
                {{ count }}
            </div>
        </div>

        <div
            class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-orange-600/5 opacity-0 blur-3xl transition-opacity group-hover:opacity-100"
        />
    </Link>
</template>
