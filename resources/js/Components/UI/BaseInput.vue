<script setup lang="ts">
    import { computed, useId, watch } from 'vue';

    import { MagnifyingGlassIcon, XMarkIcon } from '@heroicons/vue/24/outline';

    type Theme = 'light' | 'dark';
    type Variant = 'default' | 'search';

    const model = defineModel<string | number | null | undefined>({ default: '' });
    const error = defineModel<string | undefined>('error');

    defineOptions({
        inheritAttrs: true,
    });

    const emit = defineEmits<{
        (e: 'input-start'): void;
    }>();

    watch(
        () => model.value,
        () => {
            emit('input-start');
        },
    );

    interface Props {
        label?: string;
        type?: string;
        placeholder?: string;
        disabled?: boolean;
        autofocus?: boolean;
        uppercase?: boolean;
        required?: boolean;
        autocomplete?: string;
        theme?: Theme;
        variant?: Variant;
        /** Показать крестик очистки, когда есть значение */
        clearable?: boolean;
    }

    const props = withDefaults(defineProps<Props>(), {
        type: 'text',
        disabled: false,
        uppercase: false,
        required: false,
        theme: 'dark',
        variant: 'default',
        clearable: false,
    });

    const isDark = computed(() => props.theme === 'dark');
    const isSearch = computed(() => props.variant === 'search');

    const inputId = useId();
    const errorId = useId();

    const hasValue = computed(() => {
        const v = model.value;
        return v !== null && v !== undefined && String(v).length > 0;
    });

    function clear() {
        model.value = '';
    }

    const wrapperClasses = computed(() => [
        'relative w-full',
        isSearch.value ? 'rounded-full' : 'rounded-2xl',
    ]);

    const fieldClasses = computed(() => [
        'w-full border text-sm font-medium transition-all duration-200 outline-none focus:ring-0 disabled:cursor-not-allowed',
        // Скругление
        isSearch.value ? 'rounded-full' : 'rounded-2xl',
        // Padding: для search — слева и справа больше (под иконки)
        isSearch.value
            ? ['py-2.5 pr-4', hasValue.value && props.clearable ? 'pl-10 pr-10' : 'pl-10']
            : 'px-4 py-3',
        // Фон — на paper белое поле читается как отдельный слой
        isDark.value ? 'bg-rancho-ink-950' : 'bg-white',
        // Границы
        error.value
            ? 'border-rose-500/60 focus-visible:border-rose-500'
            : isDark.value
              ? 'border-rancho-ink-800 focus-visible:border-rancho-buttercup'
              : 'border-rancho-ink-300 focus-visible:border-rancho-pine',
        // Текст и placeholder
        isDark.value
            ? 'text-white placeholder:text-rancho-ink-600'
            : 'text-rancho-forest placeholder:text-rancho-ink-500',
        props.uppercase ? 'uppercase' : '',
    ]);

    const iconClasses = computed(() => [
        'pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 transition-colors',
        isSearch.value ? 'h-4 w-4' : 'h-5 w-5',
        isDark.value ? 'text-rancho-ink-500' : 'text-rancho-olive',
    ]);
</script>

<template>
    <div class="w-full" v-bind="$attrs" :class="{ 'pointer-events-none opacity-50': disabled }">
        <label
            v-if="label"
            :for="inputId"
            class="mb-1.5 block px-1 text-[10px] font-black uppercase tracking-widest transition-colors"
            :class="
                error ? 'text-rose-500' : isDark ? 'text-rancho-ink-500' : 'text-rancho-ink-600'
            "
        >
            {{ label }}{{ required ? ' *' : '' }}
        </label>

        <div :class="wrapperClasses">
            <!-- Иконка поиска -->
            <MagnifyingGlassIcon v-if="isSearch" :class="iconClasses" aria-hidden="true" />

            <input
                v-bind="$attrs"
                :id="inputId"
                v-model="model"
                :type="isSearch ? 'search' : type"
                :placeholder="placeholder"
                :autocomplete="autocomplete"
                :required="required"
                :disabled="disabled"
                :autofocus="autofocus"
                :class="[fieldClasses, isDark ? 'dark-autofill' : 'light-autofill']"
                :aria-invalid="!!error"
                :aria-describedby="error ? errorId : undefined"
            />

            <!-- Кнопка очистки -->
            <button
                v-if="isSearch && clearable && hasValue && !disabled"
                type="button"
                class="absolute right-2.5 top-1/2 flex h-6 w-6 -translate-y-1/2 items-center justify-center rounded-full transition-colors"
                :class="
                    isDark
                        ? 'text-rancho-ink-500 hover:bg-rancho-ink-800 hover:text-white'
                        : 'text-rancho-ink-500 hover:bg-rancho-ink-100 hover:text-rancho-forest'
                "
                @click="clear"
                aria-label="Очистить"
            >
                <XMarkIcon class="h-3.5 w-3.5" />
            </button>
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
                :id="errorId"
                class="mt-1.5 px-1 text-[10px] font-bold uppercase tracking-wider text-rose-500"
            >
                {{ error }}
            </p>
        </Transition>
    </div>
</template>

<style scoped>
    /* Убираем нативный крестик у <input type="search"> в WebKit/Chrome */
    input[type='search']::-webkit-search-cancel-button,
    input[type='search']::-webkit-search-decoration {
        -webkit-appearance: none;
        appearance: none;
    }

    /* Dark autofill */
    .dark-autofill:-webkit-autofill,
    .dark-autofill:-webkit-autofill:hover,
    .dark-autofill:-webkit-autofill:focus {
        -webkit-text-fill-color: #ffffff;
        -webkit-box-shadow: 0 0 0 1000px #0b0b0b inset;
        box-shadow: 0 0 0 1000px #0b0b0b inset;
        caret-color: #ffffff;
        transition: background-color 5000s ease-in-out 0s;
    }

    /* Light autofill */
    .light-autofill:-webkit-autofill,
    .light-autofill:-webkit-autofill:hover,
    .light-autofill:-webkit-autofill:focus {
        -webkit-text-fill-color: #1c3f34;
        -webkit-box-shadow: 0 0 0 1000px #ffffff inset;
        box-shadow: 0 0 0 1000px #ffffff inset;
        caret-color: #1c3f34;
        transition: background-color 5000s ease-in-out 0s;
    }
</style>
