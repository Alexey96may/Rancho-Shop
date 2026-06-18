<script setup lang="ts">
    interface Props {
        modelValue: boolean;
        label?: string;
        activeText?: string;
        inactiveText?: string;
        disabled?: boolean;
    }

    const props = withDefaults(defineProps<Props>(), {
        label: '',
        activeText: 'Включено',
        inactiveText: 'Выключено',
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
    <div
        class="flex items-center justify-between rounded-2xl border border-slate-800 bg-slate-950 p-4 transition-opacity"
        :class="{ 'cursor-not-allowed opacity-50': disabled }"
    >
        <div class="flex flex-col gap-1">
            <span
                v-if="label"
                class="text-[10px] font-bold uppercase tracking-wider text-slate-400"
            >
                {{ label }}
            </span>
            <span class="text-[10px] font-black uppercase text-slate-200">
                {{ modelValue ? activeText : inactiveText }}
            </span>
        </div>

        <button
            type="button"
            role="switch"
            :aria-checked="modelValue"
            :disabled="disabled"
            @click="toggle"
            class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 focus:ring-offset-slate-950"
            :class="[
                modelValue ? 'bg-emerald-600' : 'bg-slate-700',
                disabled ? 'cursor-not-allowed' : 'cursor-pointer',
            ]"
        >
            <span class="sr-only">{{ label || 'Переключатель' }}</span>

            <span
                :class="modelValue ? 'translate-x-6' : 'translate-x-1'"
                class="shadow-lg pointer-events-none h-4 w-4 transform rounded-full bg-white transition-transform duration-200 ease-in-out"
            />
        </button>
    </div>
</template>
