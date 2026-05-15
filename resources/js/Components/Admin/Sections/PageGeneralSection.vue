<script setup lang="ts">
    import type { Errors } from '@inertiajs/core';

    import BaseInput from '@/Components/UI/BaseInput.vue';
    import BaseSelect from '@/Components/UI/BaseSelect.vue';

    const props = defineProps<{
        page_types: Array<any>;
        templates: Array<any>;
        errors: Errors;
        disabled: boolean;
    }>();

    const title = defineModel<string>('title');
    const slug = defineModel<string>('slug');
    const type = defineModel('type');
    const template = defineModel('template');
</script>

<template>
    <div class="space-y-6 rounded-[2.5rem] border border-slate-800 bg-slate-900/30 p-8">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <BaseInput
                v-model="title"
                v-model:error="errors.title"
                label="Заголовок страницы"
                placeholder="Например: О нас"
                :disabled="disabled"
            />

            <BaseInput
                v-model="slug"
                v-model:error="errors.slug"
                label="URL Путь (Slug)"
                placeholder="Например: about_us"
                :disabled="disabled"
            />
        </div>
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div class="grid gap-2">
                <BaseSelect
                    v-model="type"
                    v-model:error="errors.type"
                    :options="page_types"
                    label="Тип страницы"
                    :disabled="disabled"
                    variant="admin"
                />
            </div>
            <div class="grid gap-2">
                <BaseSelect
                    v-model="template"
                    v-model:error="errors.type"
                    :options="templates"
                    label="Системный шаблон"
                    :disabled="disabled"
                    variant="admin"
                />
            </div>
        </div>
    </div>
</template>
