<script setup lang="ts">
    const content = defineModel<string>();
</script>

<template>
    <div class="rounded-[2.5rem] border border-slate-800 bg-slate-900/30 p-8">
        <div class="grid gap-2">
            <label class="ml-2 text-[10px] font-black uppercase text-slate-500"
                >Тело страницы (HTML/Markdown)</label
            >
            <textarea
                v-model="content"
                rows="20"
                class="w-full rounded-2xl border-slate-800 bg-slate-950 p-6 font-mono text-sm leading-relaxed text-slate-300 focus:border-orange-500 focus:ring-0"
            ></textarea>
        </div>
    </div>
</template>
