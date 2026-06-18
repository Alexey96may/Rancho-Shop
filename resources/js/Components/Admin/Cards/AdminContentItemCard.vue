<script setup lang="ts">
    import AdminDeleteButton from '@/Components/Admin/UI/AdminDeleteButton.vue';
    import AdminNumberInput from '@/Components/Admin/UI/AdminNumberInput.vue';
    import BaseInput from '@/Components/UI/BaseInput.vue';
    import BaseMoveButton from '@/Components/UI/BaseMoveButton.vue';
    import BaseTextarea from '@/Components/UI/BaseTextarea.vue';
    import { LandingContentItem } from '@/types';

    const props = defineProps<{
        item: LandingContentItem;
        index: number;
        isFirst: boolean;
        isLast: boolean;
        isFinal?: boolean;
        disabled?: boolean;
        errors?: string;
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
        :class="{ 'opacity-50': disabled }"
    >
        <div
            class="absolute -right-0 top-[50%] flex -translate-y-[50%] translate-x-[50%] flex-col gap-2 opacity-0 transition-all group-focus-within:opacity-100 group-hover:opacity-100"
            role="toolbar"
            :aria-label="`Управление элементом №${index + 1}`"
        >
            <BaseMoveButton
                direction="up"
                :index="index"
                :hidden="isFirst"
                @move="emit('move', index, 'up')"
            />

            <BaseMoveButton
                direction="down"
                :index="index"
                :hidden="isLast"
                @move="emit('move', index, 'down')"
            />

            <AdminDeleteButton
                v-show="!isFinal"
                @click="emit('remove', index)"
                :title="`Удалить элемент ${index + 1}`"
                :disabled="disabled"
            />
        </div>

        <div class="grid gap-6 md:grid-cols-12">
            <div class="grid gap-4 md:col-span-11 md:grid-cols-3">
                <BaseInput
                    v-model="item.icon"
                    label="Иконка Lucide"
                    placeholder="Circle, Box, etc."
                    :disabled="disabled"
                />

                <BaseInput
                    v-model="item.title"
                    label="Заголовок"
                    placeholder="Напишите заголовок, если он предусмотрен..."
                    :disabled="disabled"
                />

                <AdminNumberInput
                    v-model.number="item.step"
                    :min="0"
                    :max="9999"
                    label="Шаг (если нужно)"
                />

                <BaseTextarea
                    v-model="item.desc"
                    label="Описание"
                    placeholder="Введите описание, если оно предусмотрено..."
                    :max-height="400"
                />

                <div v-show="errors" class="pl-4 text-sm text-orange-700">{{ errors }}</div>
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
