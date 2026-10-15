<script setup lang="ts">
    import { computed } from 'vue';

    import type { Errors } from '@inertiajs/core';

    import AdminBaseTextarea from '@/Components/Admin/UI/AdminBaseTextarea.vue';
    import BaseInput from '@/Components/UI/BaseInput.vue';
    import BaseSwitch from '@/Components/UI/BaseSwitch.vue';

    interface SeoData {
        title: string;
        description: string;
        keywords: string;
        canonical: string;
        is_noindex: boolean;
    }

    interface Props {
        modelValue: SeoData;
        /** Имя сущности — животное, товар, страница */
        pageName?: string;
        /** Шаблон заголовка, если в проекте есть */
        titleTemplate?: string;
        errors?: Errors;
        disabled?: boolean;
    }

    const props = withDefaults(defineProps<Props>(), {
        pageName: '',
        titleTemplate: '{title} — {name}',
    });

    /**
     * Если title задан — берём его (но всё равно оборачиваем в шаблон).
     * Если title пуст — используем только name.
     */
    const snippetTitle = computed(() => {
        const title = props.modelValue.title?.trim();
        const name = props.pageName?.trim();

        if (title && name) {
            return props.titleTemplate.replace('{title}', title).replace('{name}', name);
        }

        if (title) return title;
        if (name) return name;

        return 'Заголовок появится здесь';
    });

    /**
     * Итоговый canonical.
     */
    const snippetCanonical = computed(() => {
        return props.modelValue.canonical || 'https://my-site.com › ...';
    });
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
                :placeholder="snippetCanonical"
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
            class="rounded-2xl border border-rancho-ink-800 bg-rancho-ink-950/80 p-4 sm:rounded-3xl sm:p-6"
        >
            <p
                class="mb-3 text-[9px] font-black uppercase tracking-[0.2em] text-rancho-ink-500 sm:mb-4"
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
                <p class="truncate text-xs text-rancho-ink-400">
                    {{ snippetCanonical }}
                </p>

                <h4
                    class="cursor-pointer truncate text-lg leading-tight text-rancho-sky hover:underline sm:text-xl"
                >
                    {{ snippetTitle }}
                </h4>

                <p class="line-clamp-2 text-sm leading-snug text-rancho-ink-300">
                    {{ modelValue.description || 'Введите описание для сниппета...' }}
                </p>
            </div>
        </div>
    </div>
</template>
