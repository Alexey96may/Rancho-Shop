<script setup lang="ts">
    const seo = defineModel<{
        title: string;
        description: string;
        keywords: string;
    }>({ required: true });
</script>

<template>
    <div class="space-y-6 rounded-[2.5rem] border border-slate-800 bg-slate-900/30 p-8">
        <div class="grid gap-2">
            <label class="ml-2 text-[10px] font-black uppercase text-slate-500">Meta Title</label>
            <input
                v-model="seo.title"
                type="text"
                class="w-full rounded-2xl border-slate-800 bg-slate-950 p-4 text-white focus:border-orange-500"
            />
        </div>
        <div class="grid gap-2">
            <label class="ml-2 text-[10px] font-black uppercase text-slate-500"
                >Meta Description</label
            >
            <textarea
                v-model="seo.description"
                rows="4"
                class="w-full rounded-2xl border-slate-800 bg-slate-950 p-4 text-white focus:border-orange-500"
            ></textarea>
        </div>
        <div class="grid gap-2">
            <label class="ml-2 text-[10px] font-black uppercase text-slate-500"
                >Ключевые слова</label
            >
            <input
                v-model="seo.keywords"
                type="text"
                class="w-full rounded-2xl border-slate-800 bg-slate-950 p-4 text-white focus:border-orange-500"
            />
        </div>
    </div>
</template>
