<script setup lang="ts">
    import { GripVerticalIcon } from 'lucide-vue-next';

    interface Props {
        title?: string;
        disabled?: boolean;
    }

    withDefaults(defineProps<Props>(), {
        title: 'Перетащить для изменения порядка',
        disabled: false,
    });
</script>

<template>
    <div
        :aria-label="title"
        :title="title"
        class="drag-handle flex shrink-0 items-center justify-center transition-all"
        :class="[
            disabled
                ? 'cursor-not-allowed text-slate-800 opacity-20'
                : 'cursor-grab text-slate-700 hover:text-slate-400 active:scale-95 active:cursor-grabbing',
        ]"
    >
        <div class="p-2">
            <GripVerticalIcon class="h-5 w-5" aria-hidden="true" />
        </div>

        <span class="sr-only">{{ title }}</span>
    </div>
</template>
