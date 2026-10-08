<script setup lang="ts">
    import { type PropType } from 'vue';

    import { Head } from '@inertiajs/vue3';

    import AppLayout from '@/Layouts/MainLayout.vue';
    import type { Page, ResourceSingle } from '@/types';

    const props = defineProps({
        page: {
            type: Object as PropType<ResourceSingle<Page>>,
            required: true,
            validator: (value: ResourceSingle<Page>) => {
                return Boolean(value && typeof value === 'object' && value.data);
            },
        },
    });
</script>

<template>
    <Head :title="props.page.data.title" />
    <AppLayout>
        <article class="mx-auto max-w-4xl px-6 py-20">
            <header class="mb-12 text-center">
                <h1 class="text-6xl font-black uppercase italic tracking-tighter text-slate-900">
                    {{ props.page.data.title }}
                </h1>
                <div class="mx-auto mt-6 h-2 w-24 rounded-full bg-slate-900"></div>
            </header>

            <div
                class="prose prose-xl prose-slate max-w-none prose-headings:font-black prose-headings:uppercase prose-p:leading-relaxed prose-strong:font-black"
                v-html="props.page.data.content"
            ></div>
        </article>
    </AppLayout>
</template>
