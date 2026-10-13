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
        isDark.value ? 'border-rancho-ink-800 bg-rancho-ink-950' : 'border-rancho-ink-300 bg-white',
        props.disabled ? 'cursor-not-allowed opacity-50' : '',
    ]);

    const trackClasses = computed(() => [
        'relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors',
        'focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2',
        // Активное состояние — цвет
        props.modelValue
            ? isDark.value
                ? 'bg-rancho-buttercup'
                : 'bg-rancho-pine'
            : isDark.value
              ? 'bg-rancho-ink-700'
              : 'bg-rancho-ink-300',
        // Focus ring — только при клавиатурной навигации
        isDark.value
            ? 'focus-visible:ring-rancho-buttercup focus-visible:ring-offset-rancho-ink-950'
            : 'focus-visible:ring-rancho-pine focus-visible:ring-offset-white',
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
                :class="isDark ? 'text-rancho-ink-500' : 'text-rancho-ink-600'"
            >
                {{ label }}
            </span>
            <span
                class="truncate text-[10px] font-bold uppercase tracking-wider transition-colors"
                :class="
                    modelValue
                        ? isDark
                            ? 'text-rancho-buttercup'
                            : 'text-rancho-pine'
                        : isDark
                          ? 'text-rancho-ink-400'
                          : 'text-rancho-ink-600'
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
