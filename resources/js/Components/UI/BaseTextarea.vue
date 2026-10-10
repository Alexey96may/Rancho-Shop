<script setup lang="ts">
    import { computed, onMounted, ref, useId, watch } from 'vue';

    import { debounce } from 'lodash-es';

    type Theme = 'light' | 'dark';

    const model = defineModel<string | null | undefined>({ default: '' });
    const error = defineModel<string | undefined>('error');
    const emit = defineEmits<{
        (e: 'blur'): void;
    }>();

    interface Props {
        label?: string;
        placeholder?: string;
        disabled?: boolean;
        rows?: number;
        maxHeight?: number;
        required?: boolean;
        theme?: Theme;
    }

    const props = withDefaults(defineProps<Props>(), {
        rows: 3,
        required: false,
        theme: 'dark',
    });

    const isDark = computed(() => props.theme === 'dark');

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

    const debouncedEmit = debounce(() => {
        emit('blur');
    }, 300);

    // Общие классы поля
    const fieldClasses = computed(() => [
        'custom-scrollbar w-full border-none bg-transparent px-4 py-3 text-sm font-medium leading-relaxed transition-all focus:ring-0 disabled:cursor-not-allowed',
        isDark.value
            ? 'text-white placeholder:text-slate-600'
            : 'text-rancho-forest placeholder:text-slate-400',
    ]);

    const wrapperClasses = computed(() => [
        'group relative flex w-full overflow-hidden rounded-2xl border transition-all duration-200',
        error.value
            ? 'border-rose-500/50 focus-within:border-rose-500'
            : isDark.value
              ? 'border-slate-800 bg-slate-950 focus-within:border-orange-500'
              : 'border-slate-300 bg-white focus-within:border-rancho-pine',
    ]);
</script>

<template>
    <div class="w-full" :class="{ 'pointer-events-none opacity-50': disabled }">
        <label
            v-if="label"
            :for="inputId"
            class="mb-1.5 block px-1 text-[10px] font-black uppercase tracking-widest transition-colors"
            :class="error ? 'text-rose-500' : isDark ? 'text-slate-500' : 'text-rancho-olive'"
        >
            {{ label }}{{ required ? ' *' : '' }}
        </label>

        <div :class="wrapperClasses">
            <textarea
                :id="inputId"
                ref="textareaRef"
                :value="model"
                :placeholder="placeholder"
                :disabled="disabled"
                :required="required"
                :rows="rows"
                :class="fieldClasses"
                @focus="isFocused = true"
                @blur="
                    isFocused = false;
                    debouncedEmit();
                "
                @input="handleInput"
            />
        </div>

        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="transform -translate-y-1 opacity-0"
            enter-to-class="transform translate-y-0 opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <p
                v-if="error"
                class="mt-1.5 px-1 text-[10px] font-bold uppercase tracking-wider text-rose-500"
            >
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
