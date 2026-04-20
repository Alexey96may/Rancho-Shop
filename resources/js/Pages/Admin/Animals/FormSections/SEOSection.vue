<script setup lang="ts">
    import { DocumentTextIcon, GlobeAltIcon, KeyIcon } from '@heroicons/vue/24/outline';

    const props = defineProps<{
        modelValue: {
            title: string;
            description: string;
            keywords: string;
        };
    }>();

    // Рекомендуемые лимиты для SEO
    const limits = {
        title: 60,
        description: 160,
    };
</script>

<template>
    <div class="animate-in fade-in zoom-in-95 space-y-8">
        <div class="space-y-3">
            <div class="flex items-center justify-between px-2">
                <label
                    class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-slate-500"
                >
                    <GlobeAltIcon class="h-3 w-3 text-orange-500" />
                    Meta Title (Заголовок страницы)
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
                placeholder="Например: Купить корову голштинской породы | Rancho"
                class="w-full rounded-2xl border-slate-800 bg-slate-950 p-4 text-sm text-white focus:border-orange-500 focus:ring-orange-500/20"
            />
        </div>

        <div class="space-y-3">
            <div class="flex items-center justify-between px-2">
                <label
                    class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-slate-500"
                >
                    <DocumentTextIcon class="h-3 w-3 text-orange-500" />
                    Meta Description (Описание)
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
                rows="4"
                placeholder="Краткое описание для поисковой выдачи..."
                class="w-full rounded-[2rem] border-slate-800 bg-slate-950 p-5 text-sm text-white focus:border-orange-500 focus:ring-orange-500/20"
            ></textarea>
        </div>

        <div class="space-y-3">
            <label
                class="ml-2 flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-slate-500"
            >
                <KeyIcon class="h-3 w-3 text-orange-500" />
                Keywords (Ключевые слова через запятую)
            </label>
            <input
                v-model="modelValue.keywords"
                type="text"
                placeholder="корова, ферма, молоко, купить"
                class="w-full rounded-2xl border-slate-800 bg-slate-950 p-4 text-sm text-white focus:border-orange-500 focus:ring-orange-500/20"
            />
        </div>

        <div class="mt-10 rounded-[2rem] border border-slate-800/50 bg-slate-950/80 p-6">
            <p class="mb-4 text-[9px] font-black uppercase tracking-[0.2em] text-slate-600">
                Предпросмотр в Google
            </p>
            <div class="max-w-[600px] space-y-1">
                <p class="text-[12px] text-[#bdc1c6]">https://rancho.ru › animals › ...</p>
                <h4
                    class="cursor-pointer truncate text-[20px] leading-tight text-[#8ab4f8] hover:underline"
                >
                    {{ modelValue.title || 'Заголовок появится здесь' }}
                </h4>
                <p class="line-clamp-2 text-[14px] leading-snug text-[#bdc1c6]">
                    {{
                        modelValue.description ||
                        'Введите описание, чтобы увидеть, как ваша страница будет выглядеть в результатах поиска поисковых систем.'
                    }}
                </p>
            </div>
        </div>
    </div>
</template>
