<script setup lang="ts">
    import type { Errors } from '@inertiajs/core';

    import AdminBaseTextarea from '@/Components/Admin/UI/AdminBaseTextarea.vue';
    import BaseInput from '@/Components/UI/BaseInput.vue';
    import BaseSwitch from '@/Components/UI/BaseSwitch.vue';

    const props = defineProps<{
        modelValue: {
            title: string;
            description: string;
            keywords: string;
            canonical: string;
            is_noindex: boolean;
        };
        disabled: boolean;
        errors?: Errors;
    }>();
</script>

<template>
    <div class="animate-in fade-in zoom-in-95 space-y-8">
        <div class="flex items-center justify-between gap-6">
            <BaseSwitch
                v-bind:model-value="!modelValue.is_noindex"
                @update:model-value="modelValue.is_noindex = !$event"
                :error="errors?.is_noindex"
                label="Индексация страницы"
                active-text="Доступно для всех"
                inactive-text="Скрыто из поиска"
                :disabled="disabled"
                class="flex-shrink-0"
            />

            <BaseInput
                v-model="modelValue.canonical"
                :error="errors?.canonical"
                type="url"
                label="Canonical URL (Основная ссылка)"
                placeholder="https://rancho.ru/..."
            />
        </div>

        <BaseInput v-model="modelValue.title" :error="errors?.title" label="Meta Title" />

        <AdminBaseTextarea
            v-model="modelValue.description"
            id="seoDescr"
            label="Meta Description"
            :error="errors?.description"
            :disabled="disabled"
            rows="6"
        />

        <BaseInput v-model="modelValue.keywords" :error="errors?.keywords" label="Keywords" />

        <div class="mt-10 rounded-[2rem] border border-slate-800/50 bg-slate-950/80 p-6">
            <p class="mb-4 text-[9px] font-black uppercase tracking-[0.2em] text-slate-600">
                Предпросмотр в Google
                {{ modelValue.is_noindex ? '(Страница скрыта от роботов)' : '' }}
            </p>
            <div
                class="max-w-[600px] space-y-1 transition-opacity"
                :class="{ 'opacity-30 grayscale': modelValue.is_noindex }"
            >
                <p class="text-[12px] text-[#bdc1c6]">
                    {{ modelValue.canonical || 'https://rancho.ru › animals › ...' }}
                </p>
                <h4
                    class="cursor-pointer truncate text-[20px] leading-tight text-[#8ab4f8] hover:underline"
                >
                    {{ modelValue.title || 'Заголовок появится здесь' }}
                </h4>
                <p class="line-clamp-2 text-[14px] leading-snug text-[#bdc1c6]">
                    {{ modelValue.description || 'Введите описание для сниппета...' }}
                </p>
            </div>
        </div>
    </div>
</template>
