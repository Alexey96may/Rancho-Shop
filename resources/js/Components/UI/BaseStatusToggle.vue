<script setup lang="ts">
    interface Props {
        modelValue: boolean;
        label: string;
        disabled?: boolean;
    }

    const props = withDefaults(defineProps<Props>(), {
        disabled: false,
    });

    const emit = defineEmits<{
        (e: 'update:modelValue', value: boolean): void;
    }>();

    const toggle = () => {
        if (!props.disabled) {
            emit('update:modelValue', !props.modelValue);
        }
    };
</script>

<template>
    <button
        type="button"
        role="switch"
        :aria-checked="modelValue"
        :disabled="disabled"
        @click="toggle"
        class="flex w-full items-center justify-between gap-2 rounded-2xl border p-4 transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-rancho-mint/40"
        :class="[
            modelValue
                ? 'border-rancho-mint/40 bg-rancho-ink-950'
                : 'border-rancho-ink-800 bg-rancho-ink-950',
            disabled
                ? 'cursor-not-allowed opacity-50'
                : 'cursor-pointer hover:border-rancho-ink-700',
        ]"
    >
        <span
            class="text-[10px] font-black uppercase tracking-widest transition-colors"
            :class="modelValue ? 'text-rancho-ink-200' : 'text-rancho-ink-500'"
        >
            {{ label }}
        </span>

        <div
            class="h-2.5 w-2.5 shrink-0 rounded-full transition-all duration-300"
            :class="modelValue ? 'bg-rancho-mint shadow-[0_0_10px_#7AB89A]' : 'bg-rancho-ink-700'"
        ></div>

        <span class="sr-only">{{ modelValue ? 'Активно' : 'Неактивно' }}</span>
    </button>
</template>
