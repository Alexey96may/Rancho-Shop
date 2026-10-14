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
            'flex items-center gap-2 rounded-lg p-2 text-[10px] font-black uppercase outline-none transition-all focus-visible:ring-2 focus-visible:ring-rose-500/40',
            disabled
                ? 'text-rancho-ink-600 cursor-not-allowed opacity-30'
                : 'text-rancho-ember hover:text-rancho-ember/70',
        ]"
    >
        <slot>Удалить</slot>
    </button>
</template>
