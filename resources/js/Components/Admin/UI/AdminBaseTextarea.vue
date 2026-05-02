<script setup lang="ts">
    interface Props {
        modelValue: string | number | null;
        id?: string;
        label?: string;
        error?: string;
        rows?: number | string;
        placeholder?: string;
        disabled?: boolean;
    }

    defineProps<Props>();

    const emit = defineEmits<{
        (e: 'update:modelValue', value: string): void;
    }>();

    const handleInput = (event: Event) => {
        const target = event.target as HTMLTextAreaElement;
        emit('update:modelValue', target.value);
    };
</script>

<template>
    <div class="flex flex-col gap-2">
        <label
            v-if="label"
            :for="id"
            class="text-xs font-black uppercase tracking-widest text-slate-500 transition-colors"
        >
            {{ label }}
        </label>

        <textarea
            :id="id"
            :value="modelValue ?? ''"
            :rows="rows || 4"
            :disabled="disabled"
            :placeholder="placeholder"
            @input="handleInput"
            class="custom-scrollbar w-full rounded-2xl border-slate-800 bg-slate-950 p-4 font-mono text-sm text-orange-400 transition-all placeholder:text-slate-700 focus:border-orange-500 focus:ring-orange-500 disabled:cursor-not-allowed disabled:opacity-50"
            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': error }"
        ></textarea>

        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="transform -translate-y-2 opacity-0"
            enter-to-class="transform translate-y-0 opacity-100"
        >
            <p v-if="error" class="text-xs font-medium text-red-500">
                {{ error }}
            </p>
        </Transition>
    </div>
</template>

<style scoped>
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
        margin: 8px 0;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: theme('colors.slate.800');
        border-radius: 10px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: theme('colors.orange.600');
    }

    .custom-scrollbar {
        scrollbar-width: thin;
        scrollbar-color: theme('colors.slate.800') transparent;
    }
</style>
