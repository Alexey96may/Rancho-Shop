<script setup lang="ts">
    import { Link } from '@inertiajs/vue3';

    defineProps<{
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
    }>();
</script>

<template>
    <nav
        v-if="links.length > 3"
        role="navigation"
        aria-label="Навигация по страницам"
        class="mt-12 flex justify-center"
    >
        <div
            class="flex items-center gap-1 rounded-2xl border border-slate-800 bg-slate-900/80 p-1.5 backdrop-blur-sm"
        >
            <template v-for="(link, k) in links" :key="k">
                <div
                    v-if="link.url === null"
                    class="flex h-10 min-w-[40px] items-center justify-center px-3 text-[10px] font-black uppercase tracking-widest text-slate-600 opacity-50"
                    v-html="link.label"
                />

                <Link
                    v-else
                    :href="link.url"
                    class="flex h-10 min-w-[40px] items-center justify-center rounded-xl px-4 text-[10px] font-black uppercase tracking-widest transition-all"
                    :class="[
                        link.active
                            ? 'bg-orange-600 text-white shadow-[0_0_20px_rgba(234,88,12,0.4)]'
                            : 'text-slate-400 hover:bg-slate-800 hover:text-white',
                    ]"
                    preserve-scroll
                    preserve-state
                    v-html="link.label"
                />
            </template>
        </div>
    </nav>
</template>

<style scoped>
    :deep(span) {
        font-family: sans-serif;
        font-size: 1.2rem;
    }
</style>
