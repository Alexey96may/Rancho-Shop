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
        class="flex w-full items-center justify-between rounded-2xl border p-4 transition-all focus:outline-none focus:ring-2 focus:ring-emerald-500/50"
        :class="[
            modelValue ? 'border-emerald-500/30 bg-slate-950' : 'border-slate-800 bg-slate-950',
            disabled ? 'cursor-not-allowed opacity-50' : 'cursor-pointer hover:border-slate-700',
        ]"
    >
        <span
            class="text-[10px] font-black uppercase tracking-widest transition-colors"
            :class="modelValue ? 'text-slate-300' : 'text-slate-500'"
        >
            {{ label }}
        </span>

        <div
            class="h-2.5 w-2.5 rounded-full transition-all duration-300"
            :class="[modelValue ? 'bg-emerald-500 shadow-[0_0_10px_#10b981]' : 'bg-slate-700']"
        ></div>

        <span class="sr-only">{{ modelValue ? 'Активно' : 'Неактивно' }}</span>
    </button>
</template>
