<script setup lang="ts">
    import {
        DocumentTextIcon,
        EyeIcon,
        GlobeAltIcon,
        KeyIcon,
        LinkIcon,
        NoSymbolIcon,
    } from '@heroicons/vue/24/outline';

    const props = defineProps<{
        modelValue: {
            title: string;
            description: string;
            keywords: string;
            canonical: string;
            is_noindex: boolean;
        };
    }>();

    const limits = {
        title: 60,
        description: 160,
    };
</script>

<template>
    <div class="animate-in fade-in zoom-in-95 space-y-8">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div
                class="flex items-center justify-between rounded-3xl border border-slate-800 bg-slate-950/50 p-4 transition-colors"
                :class="{ 'border-red-500/30 bg-red-500/5': modelValue.is_noindex }"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-2xl"
                        :class="
                            modelValue.is_noindex
                                ? 'bg-red-500/20 text-red-500'
                                : 'bg-emerald-500/20 text-emerald-500'
                        "
                    >
                        <NoSymbolIcon v-if="modelValue.is_noindex" class="h-6 w-6" />
                        <EyeIcon v-else class="h-6 w-6" />
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-500">
                            Индексация
                        </p>
                        <p class="text-xs font-bold text-white">
                            {{ modelValue.is_noindex ? 'Скрыто из поиска' : 'Доступно для всех' }}
                        </p>
                    </div>
                </div>

                <button
                    @click="modelValue.is_noindex = !modelValue.is_noindex"
                    type="button"
                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                    :class="modelValue.is_noindex ? 'bg-red-500' : 'bg-slate-800'"
                >
                    <span
                        aria-hidden="true"
                        class="shadow pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white ring-0 transition duration-200 ease-in-out"
                        :class="modelValue.is_noindex ? 'translate-x-5' : 'translate-x-0'"
                    />
                </button>
            </div>

            <div class="space-y-3">
                <label
                    class="ml-2 flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-slate-500"
                >
                    <LinkIcon class="h-3 w-3 text-orange-500" />
                    Canonical URL (Основная ссылка)
                </label>
                <input
                    v-model="modelValue.canonical"
                    type="text"
                    placeholder="https://rancho.ru/..."
                    class="w-full rounded-2xl border-slate-800 bg-slate-950 p-4 text-sm text-white focus:border-orange-500 focus:ring-orange-500/20"
                />
            </div>
        </div>

        <div class="space-y-3">
            <div class="flex items-center justify-between px-2">
                <label
                    class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-slate-500"
                >
                    <GlobeAltIcon class="h-3 w-3 text-orange-500" />
                    Meta Title
                </label>
                <span
                    :class="[
                        'text-[10px] font-bold uppercase',
                        modelValue.title.length > limits.title ? 'text-red-500' : 'text-slate-600',
                    ]"
                >
                    {{ modelValue.title.length }} / {{ limits.title }}
                </span>
            </div>
            <input
                v-model="modelValue.title"
                type="text"
                class="w-full rounded-2xl border-slate-800 bg-slate-950 p-4 text-sm text-white focus:border-orange-500 focus:ring-orange-500/20"
            />
        </div>

        <div class="space-y-3">
            <div class="flex items-center justify-between px-2">
                <label
                    class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-slate-500"
                >
                    <DocumentTextIcon class="h-3 w-3 text-orange-500" />
                    Meta Description
                </label>
                <span
                    :class="[
                        'text-[10px] font-bold uppercase',
                        modelValue.description.length > limits.description
                            ? 'text-red-500'
                            : 'text-slate-600',
                    ]"
                >
                    {{ modelValue.description.length }} / {{ limits.description }}
                </span>
            </div>
            <textarea
                v-model="modelValue.description"
                rows="3"
                class="w-full rounded-[2rem] border-slate-800 bg-slate-950 p-5 text-sm text-white focus:border-orange-500 focus:ring-orange-500/20"
            ></textarea>
        </div>

        <div class="space-y-3">
            <label
                class="ml-2 flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-slate-500"
            >
                <KeyIcon class="h-3 w-3 text-orange-500" />
                Keywords
            </label>
            <input
                v-model="modelValue.keywords"
                type="text"
                class="w-full rounded-2xl border-slate-800 bg-slate-950 p-4 text-sm text-white focus:border-orange-500 focus:ring-orange-500/20"
            />
        </div>

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
