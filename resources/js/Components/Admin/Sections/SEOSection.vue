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
    <div class="space-y-6 sm:space-y-8">
        <!-- TOP: SWITCH + CANONICAL -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between sm:gap-6">
            <BaseSwitch
                :model-value="!modelValue.is_noindex"
                @update:model-value="modelValue.is_noindex = !$event"
                :error="errors?.is_noindex"
                label="Индексация страницы"
                active-text="Доступно для всех"
                inactive-text="Скрыто из поиска"
                :disabled="disabled"
                class="w-full shrink-0 sm:w-auto"
                theme="dark"
            />

            <BaseInput
                v-model="modelValue.canonical"
                :error="errors?.canonical"
                type="url"
                label="Canonical URL (Основная ссылка)"
                placeholder="https://rancho.ru/..."
                theme="dark"
                class="w-full sm:flex-1"
            />
        </div>

        <!-- TITLE -->
        <BaseInput
            v-model="modelValue.title"
            :error="errors?.title"
            label="Meta Title"
            theme="dark"
        />

        <!-- DESCRIPTION -->
        <AdminBaseTextarea
            v-model="modelValue.description"
            id="seo-description"
            label="Meta Description"
            :error="errors?.description"
            :disabled="disabled"
            rows="6"
        />

        <!-- KEYWORDS -->
        <BaseInput
            v-model="modelValue.keywords"
            :error="errors?.keywords"
            label="Keywords"
            theme="dark"
        />

        <!-- GOOGLE PREVIEW -->
        <div
            class="border-rancho-ink-800 bg-rancho-ink-950/80 rounded-2xl border p-4 sm:rounded-3xl sm:p-6"
        >
            <p
                class="text-rancho-ink-500 mb-3 text-[9px] font-black uppercase tracking-[0.2em] sm:mb-4"
            >
                Предпросмотр в Google
                <span v-if="modelValue.is_noindex" class="text-rancho-ember">
                    · Страница скрыта от роботов
                </span>
            </p>

            <!-- Сниппет -->
            <div
                class="max-w-[600px] space-y-1 transition-all"
                :class="{ 'opacity-30 grayscale': modelValue.is_noindex }"
            >
                <p class="text-rancho-ink-400 truncate text-xs">
                    {{ modelValue.canonical || 'https://rancho.ru › animals › ...' }}
                </p>

                <h4
                    class="cursor-pointer truncate text-lg leading-tight text-rancho-sky hover:underline sm:text-xl"
                >
                    {{ modelValue.title || 'Заголовок появится здесь' }}
                </h4>

                <p class="text-rancho-ink-300 line-clamp-2 text-sm leading-snug">
                    {{ modelValue.description || 'Введите описание для сниппета...' }}
                </p>
            </div>
        </div>
    </div>
</template>
