<script setup lang="ts">
    import { onMounted, ref, useId, watch } from 'vue';

    const model = defineModel<string | null | undefined>({ default: '' });
    const error = defineModel<string | undefined>('error');

    const props = defineProps<{
        label?: string;
        placeholder?: string;
        disabled?: boolean;
        rows?: number;
        maxHeight?: number;
    }>();

    const inputId = useId();
    const textareaRef = ref<HTMLTextAreaElement | null>(null);
    const isFocused = ref(false);

    const resize = () => {
        if (!textareaRef.value) return;
        textareaRef.value.style.height = 'auto';
        const newHeight = textareaRef.value.scrollHeight;

        if (props.maxHeight && newHeight > props.maxHeight) {
            textareaRef.value.style.height = `${props.maxHeight}px`;
            textareaRef.value.style.overflowY = 'auto';
        } else {
            textareaRef.value.style.height = `${newHeight}px`;
            textareaRef.value.style.overflowY = 'hidden';
        }
    };

    const handleInput = (event: Event) => {
        const target = event.target as HTMLTextAreaElement;
        model.value = target.value;
        if (error.value) error.value = undefined;
        resize();
    };

    onMounted(() => {
        resize();
    });

    watch(
        () => model.value,
        () => {
            setTimeout(resize, 0);
        },
    );
</script>

<template>
    <div class="w-full" :class="{ 'pointer-events-none opacity-50': disabled }">
        <!-- Label -->
        <label
            v-if="label"
            :for="inputId"
            class="mb-1.5 block px-1 text-xs font-medium uppercase tracking-wider text-slate-500"
        >
            {{ label }}
        </label>

        <div
            class="group relative flex w-full overflow-hidden rounded-2xl border transition-all duration-200"
            :class="[
                error
                    ? 'border-rose-500/50 bg-rose-500/5'
                    : 'border-slate-800 bg-slate-950 focus-within:border-orange-500/50',
            ]"
        >
            <textarea
                :id="inputId"
                ref="textareaRef"
                :value="model"
                :placeholder="placeholder"
                :disabled="disabled"
                :rows="rows || 3"
                @focus="isFocused = true"
                @blur="isFocused = false"
                @input="handleInput"
                class="custom-scrollbar w-full border-none bg-transparent px-4 py-3 text-sm leading-relaxed text-white placeholder:text-slate-600 focus:ring-0 disabled:cursor-not-allowed"
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
            <p v-if="error" class="mt-1.5 px-1 text-xs text-rose-500">
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
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #1e293b;
        border-radius: 10px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #334155;
    }
    textarea {
        resize: none;
    }
</style>
