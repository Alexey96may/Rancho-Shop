<script setup lang="ts">
    import BaseSelect from '@/Components/UI/BaseSelect.vue';

    const props = defineProps<{
        page_types: Array<any>;
        templates: Array<any>;
    }>();

    const title = defineModel('title');
    const slug = defineModel('slug');
    const type = defineModel('type');
    const template = defineModel('template');
</script>

<template>
    <div class="space-y-6 rounded-[2.5rem] border border-slate-800 bg-slate-900/30 p-8">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div class="grid gap-2">
                <label class="ml-2 text-[10px] font-black uppercase text-slate-500"
                    >Заголовок страницы</label
                >
                <input
                    v-model="title"
                    type="text"
                    required
                    class="w-full rounded-2xl border-slate-800 bg-slate-950 p-4 text-white focus:border-orange-500"
                />
            </div>
            <div class="grid gap-2">
                <label class="ml-2 text-[10px] font-black uppercase text-slate-500"
                    >URL Путь (Slug)</label
                >
                <input
                    v-model="slug"
                    type="text"
                    class="w-full rounded-2xl border-slate-800 bg-slate-950 p-4 font-mono text-sm text-orange-500 focus:border-orange-500"
                />
            </div>
        </div>
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div class="grid gap-2">
                <BaseSelect
                    v-model="type"
                    :options="page_types"
                    label="Тип страницы"
                    variant="admin"
                />
            </div>
            <div class="grid gap-2">
                <BaseSelect
                    v-model="template"
                    :options="templates"
                    label="Системный шаблон"
                    variant="admin"
                />
            </div>
        </div>
    </div>
</template>
