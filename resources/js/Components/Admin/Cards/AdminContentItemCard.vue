<script setup lang="ts">
    import AdminDeleteButton from '@/Components/Admin/UI/AdminDeleteButton.vue';
    import AdminNumberInput from '@/Components/Admin/UI/AdminNumberInput.vue';
    import BaseInput from '@/Components/UI/BaseInput.vue';
    import BaseMoveButton from '@/Components/UI/BaseMoveButton.vue';
    import BaseTextarea from '@/Components/UI/BaseTextarea.vue';
    import type { LandingContentItem } from '@/types';

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
        class="border-rancho-ink-800 bg-rancho-ink-900/20 hover:border-rancho-ink-700 group relative rounded-2xl border p-4 transition-all sm:rounded-3xl sm:p-6"
        :class="{ 'opacity-50': disabled }"
    >
        <!-- TOOLBAR: перемещение + удаление -->
        <div
            class="mb-6 flex justify-center gap-2 transition-all group-focus-within:opacity-100 md:absolute md:-right-0 md:top-[50%] md:mb-0 md:-translate-y-[50%] md:translate-x-[50%] md:flex-col md:justify-start md:opacity-0 md:group-hover:opacity-100"
            role="toolbar"
            :aria-label="`Управление элементом №${index + 1}`"
        >
            <BaseMoveButton
                direction="up"
                :index="index"
                :hidden="isFirst"
                @move="emit('move', index, 'up')"
                class="order-3 md:-order-none"
            />

            <BaseMoveButton
                direction="down"
                :index="index"
                :hidden="isLast"
                @move="emit('move', index, 'down')"
                class="order-1 md:-order-none"
            />

            <AdminDeleteButton
                v-show="!isFinal"
                @click="emit('remove', index)"
                :title="`Удалить элемент ${index + 1}`"
                :disabled="disabled"
                class="order-2 md:-order-none"
            />
        </div>

        <!-- CONTENT -->
        <div class="grid gap-4 sm:gap-6 md:grid-cols-12">
            <div class="grid gap-4 md:col-span-11 md:grid-cols-3">
                <BaseInput
                    v-model="item.icon"
                    label="Иконка Lucide"
                    placeholder="Circle, Box, etc."
                    :disabled="disabled"
                    theme="dark"
                />

                <BaseInput
                    v-model="item.title"
                    label="Заголовок"
                    placeholder="Напишите заголовок, если он предусмотрен..."
                    :disabled="disabled"
                    theme="dark"
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
                    theme="dark"
                />

                <p v-if="errors" class="pl-4 text-xs font-medium text-rose-500" role="alert">
                    {{ errors }}
                </p>
            </div>

            <!-- Номер элемента -->
            <div
                class="border-rancho-ink-800/60 flex items-center justify-center border-t pt-3 md:col-span-1 md:border-l md:border-t-0 md:pt-0"
                aria-hidden="true"
            >
                <span class="text-rancho-ink-800 text-2xl font-black sm:text-3xl">
                    {{ index + 1 }}
                </span>
            </div>
        </div>
    </section>
</template>
