<script setup lang="ts">
    import { computed, useId } from 'vue';

    type Theme = 'light' | 'dark';

    interface Props {
        modelValue: boolean;
        label?: string;
        activeText?: string;
        inactiveText?: string;
        disabled?: boolean;
        theme?: Theme;
    }

    const props = withDefaults(defineProps<Props>(), {
        label: '',
        activeText: 'Включено',
        inactiveText: 'Выключено',
        disabled: false,
        theme: 'dark',
    });

    const emit = defineEmits<{
        (e: 'update:modelValue', value: boolean): void;
    }>();

    const isDark = computed(() => props.theme === 'dark');

    const inputId = useId();

    const toggle = () => {
        if (!props.disabled) {
            emit('update:modelValue', !props.modelValue);
        }
    };

    const wrapperClasses = computed(() => [
        'flex items-center justify-between gap-4 rounded-2xl border p-4 transition-all',
        isDark.value ? 'border-slate-800 bg-slate-950' : 'border-slate-300 bg-white',
        props.disabled ? 'cursor-not-allowed opacity-50' : '',
    ]);

    const trackClasses = computed(() => [
        'relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2',
        // Активное состояние — цвет
        props.modelValue
            ? isDark.value
                ? 'bg-orange-600'
                : 'bg-rancho-pine'
            : isDark.value
              ? 'bg-slate-700'
              : 'bg-slate-300',
        // Focus ring
        isDark.value
            ? 'focus:ring-orange-500 focus:ring-offset-slate-950'
            : 'focus:ring-rancho-pine focus:ring-offset-white',
        props.disabled ? 'cursor-not-allowed' : 'cursor-pointer',
    ]);
</script>

<template>
    <div :class="wrapperClasses">
        <div class="flex min-w-0 flex-col gap-1">
            <span
                v-if="label"
                :id="`${inputId}-label`"
                class="text-[10px] font-black uppercase tracking-widest transition-colors"
                :class="isDark ? 'text-slate-500' : 'text-rancho-olive'"
            >
                {{ label }}
            </span>
            <span
                class="truncate text-[10px] font-bold uppercase tracking-wider transition-colors"
                :class="
                    modelValue
                        ? isDark
                            ? 'text-orange-400'
                            : 'text-rancho-pine'
                        : isDark
                          ? 'text-slate-400'
                          : 'text-slate-500'
                "
            >
                {{ modelValue ? activeText : inactiveText }}
            </span>
        </div>

        <button
            type="button"
            role="switch"
            :aria-checked="modelValue"
            :aria-labelledby="label ? `${inputId}-label` : undefined"
            :disabled="disabled"
            :class="trackClasses"
            @click="toggle"
        >
            <span class="sr-only">{{ label || 'Переключатель' }}</span>

            <span
                :class="modelValue ? 'translate-x-6' : 'translate-x-1'"
                class="shadow-sm pointer-events-none h-4 w-4 transform rounded-full bg-white transition-transform duration-200 ease-in-out"
            />
        </button>
    </div>
</template>
