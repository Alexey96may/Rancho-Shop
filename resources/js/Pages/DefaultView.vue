<script setup lang="ts">
    import { Head } from '@inertiajs/vue3';

    import AppLayout from '@/Layouts/MainLayout.vue';
    import type { Page, ResourceSingle } from '@/types';

    defineProps<{
        page: ResourceSingle<Page>;
    }>();
</script>

<template>
    <Head :title="page.data.title" />
    <AppLayout>
        <article class="mx-auto max-w-4xl px-6 py-20">
            <header class="mb-12 text-center">
                <h1 class="text-6xl font-black uppercase italic tracking-tighter text-slate-900">
                    {{ page.data.title }}
                </h1>
                <div class="mx-auto mt-6 h-2 w-24 rounded-full bg-slate-900"></div>
            </header>

            <div
                class="prose prose-slate prose-xl prose-headings:uppercase prose-headings:font-black prose-strong:font-black prose-p:leading-relaxed max-w-none"
                v-html="page.data.content"
            ></div>
        </article>
    </AppLayout>
</template>
