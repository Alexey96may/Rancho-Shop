<script setup lang="ts">
    interface Props {
        disabled?: boolean;
        show?: boolean;
        title?: string;
        areaLabel?: string;
    }

    const props = withDefaults(defineProps<Props>(), {
        disabled: false,
        show: true,
        title: '',
        areaLabel: 'Удалить',
    });

    const emit = defineEmits<{
        (e: 'confirm'): void;
    }>();

    const handleClick = () => {
        if (!props.disabled) {
            emit('confirm');
        }
    };
</script>

<template>
    <button
        v-if="show"
        type="button"
        @click="handleClick"
        :disabled="disabled"
        :aria-label="areaLabel"
        :title="title || undefined"
        :class="[
            'flex items-center gap-2 p-2 text-[10px] font-black uppercase transition-all',
            disabled
                ? 'cursor-not-allowed text-slate-600 opacity-30'
                : 'text-orange-700 hover:text-orange-500',
        ]"
    >
        <slot>Удалить</slot>
    </button>
</template>
