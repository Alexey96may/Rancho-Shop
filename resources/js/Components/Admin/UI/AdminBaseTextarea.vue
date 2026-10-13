<script setup lang="ts">
    import { computed, useId } from 'vue';

    type Theme = 'light' | 'dark';

    interface Props {
        modelValue: string | number | null;
        id?: string;
        label?: string;
        error?: string;
        rows?: number | string;
        placeholder?: string;
        disabled?: boolean;
        theme?: Theme;
    }

    const props = withDefaults(defineProps<Props>(), {
        id: undefined,
        label: undefined,
        error: undefined,
        rows: 4,
        placeholder: undefined,
        disabled: false,
        theme: 'dark',
    });

    const emit = defineEmits<{
        (e: 'update:modelValue', value: string): void;
    }>();

    const inputId = useId();
    const resolvedId = computed(() => props.id ?? inputId);

    const isDark = computed(() => props.theme === 'dark');

    const handleInput = (event: Event) => {
        const target = event.target as HTMLTextAreaElement;
        emit('update:modelValue', target.value);
    };

    const wrapperClasses = computed(() => [
        'w-full rounded-2xl border transition-all',
        props.error
            ? isDark.value
                ? 'border-rose-500/50 focus-within:border-rose-500'
                : 'border-rose-300 focus-within:border-rose-500'
            : isDark.value
              ? 'border-rancho-ink-800 focus-within:border-rancho-buttercup'
              : 'border-rancho-ink-300 focus-within:border-rancho-pine',
        isDark.value ? 'bg-rancho-ink-950' : 'bg-white',
        props.disabled ? 'cursor-not-allowed opacity-50' : '',
    ]);

    const textareaClasses = computed(() => [
        'w-full resize-none border-none bg-transparent p-4 font-mono text-sm outline-none focus:ring-0',
        isDark.value
            ? 'text-rancho-buttercup placeholder:text-rancho-ink-600'
            : 'text-rancho-forest placeholder:text-rancho-ink-500',
    ]);
</script>

<template>
    <div class="flex flex-col gap-2">
        <label
            v-if="label"
            :for="resolvedId"
            class="text-xs font-black uppercase tracking-widest transition-colors"
            :class="
                error ? 'text-rose-500' : isDark ? 'text-rancho-ink-400' : 'text-rancho-ink-600'
            "
        >
            {{ label }}
        </label>

        <div :class="wrapperClasses">
            <textarea
                :id="resolvedId"
                :value="modelValue ?? ''"
                :rows="rows"
                :disabled="disabled"
                :placeholder="placeholder"
                :aria-invalid="!!error"
                @input="handleInput"
                :class="textareaClasses"
            ></textarea>
        </div>

        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="transform -translate-y-2 opacity-0"
            enter-to-class="transform translate-y-0 opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <p v-if="error" class="text-xs font-medium text-rose-500" role="alert">
                {{ error }}
            </p>
        </Transition>
    </div>
</template>
