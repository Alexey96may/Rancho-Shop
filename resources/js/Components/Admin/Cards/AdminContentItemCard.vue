<script setup lang="ts">
    import { MoveDownIcon, MoveUpIcon, Trash2Icon } from 'lucide-vue-next';

    import AdminNumberInput from '@/Components/Admin/UI/AdminNumberInput.vue';
    import { LandingContentItem } from '@/types';

    const props = defineProps<{
        item: LandingContentItem;
        index: number;
        isFirst: boolean;
        isLast: boolean;
        isFinal?: boolean;
    }>();

    const emit = defineEmits<{
        (e: 'move', index: number, direction: 'up' | 'down'): void;
        (e: 'remove', index: number): void;
    }>();

    const fieldId = (name: string) => `item-${props.index}-${name}`;
</script>

<template>
    <section
        :aria-labelledby="fieldId('title-display')"
        class="group relative rounded-[2rem] border border-slate-800 bg-slate-900/20 p-6 transition-all hover:border-slate-700"
    >
        <div
            class="absolute -right-0 top-[50%] flex -translate-y-[50%] translate-x-[50%] flex-col gap-2 opacity-0 transition-all group-focus-within:opacity-100 group-hover:opacity-100"
            role="toolbar"
            :aria-label="`Управление элементом №${index + 1}`"
        >
            <button
                v-show="!isFirst"
                type="button"
                @click="emit('move', index, 'up')"
                :aria-label="`Переместить элемент ${index + 1} выше`"
                class="shadow-xl rounded-lg bg-slate-800 p-2 text-slate-400 outline-none hover:text-white focus:ring-2 focus:ring-orange-500"
            >
                <MoveUpIcon class="h-4 w-4" aria-hidden="true" />
            </button>

            <button
                v-show="!isLast"
                type="button"
                @click="emit('move', index, 'down')"
                :aria-label="`Переместить элемент ${index + 1} ниже`"
                class="shadow-xl rounded-lg bg-slate-800 p-2 text-slate-400 outline-none hover:text-white focus:ring-2 focus:ring-orange-500"
            >
                <MoveDownIcon class="h-4 w-4" aria-hidden="true" />
            </button>

            <button
                v-show="!isFinal"
                type="button"
                @click="emit('remove', index)"
                :aria-label="`Удалить элемент ${index + 1}`"
                class="shadow-xl rounded-lg bg-orange-800/50 p-2 text-red-400 outline-none transition-all hover:text-red-200 focus:ring-2 focus:ring-red-500"
            >
                <Trash2Icon class="h-4 w-4" aria-hidden="true" />
            </button>
        </div>

        <div class="grid gap-6 md:grid-cols-12">
            <div class="grid gap-4 md:col-span-11 md:grid-cols-3">
                <div class="space-y-1">
                    <label
                        :for="fieldId('icon')"
                        class="ml-1 text-[9px] font-black uppercase text-slate-600"
                        >Иконка Lucide</label
                    >
                    <input
                        :id="fieldId('icon')"
                        v-model="item.icon"
                        type="text"
                        placeholder="Circle, Box, etc."
                        class="w-full rounded-xl border-slate-800 bg-slate-950 p-3 text-xs text-white outline-none focus:border-orange-500/50 focus:ring-0"
                    />
                </div>

                <div class="space-y-1">
                    <label
                        :for="fieldId('title')"
                        class="ml-1 text-[9px] font-black uppercase text-slate-600"
                        >Заголовок</label
                    >
                    <input
                        :id="fieldId('title')"
                        v-model="item.title"
                        type="text"
                        class="w-full rounded-xl border-slate-800 bg-slate-950 p-3 text-xs text-white outline-none focus:border-orange-500/50 focus:ring-0"
                    />
                </div>

                <div class="space-y-1">
                    <AdminNumberInput
                        v-model.number="item.step"
                        :min="0"
                        :max="9999"
                        label="Шаг (если нужно)"
                    />
                </div>

                <div class="space-y-1 md:col-span-3">
                    <label
                        :for="fieldId('desc')"
                        class="ml-1 text-[9px] font-black uppercase text-slate-600"
                        >Описание</label
                    >
                    <textarea
                        :id="fieldId('desc')"
                        v-model="item.desc"
                        rows="2"
                        class="w-full rounded-xl border-slate-800 bg-slate-950 p-3 text-xs leading-relaxed text-white outline-none focus:border-orange-500/50 focus:ring-0"
                    ></textarea>
                </div>
            </div>

            <div
                class="flex flex-col items-center justify-center border-l border-slate-800/50 md:col-span-1"
                aria-hidden="true"
            >
                <span class="text-2xl font-black text-slate-800">
                    {{ index + 1 }}
                </span>
            </div>
        </div>
    </section>
</template>
