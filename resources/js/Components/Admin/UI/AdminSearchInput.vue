<script setup lang="ts">
    /**
     * AdminSearchInput Component
     *
     * @prop {string} modelValue - Reactive search string.
     * @prop {string} placeholder - Input placeholder text.
     * @prop {string} label - Accessible label for screen readers.
     */
    import { onUnmounted, ref, useId, watch } from 'vue';

    import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline';
    import { debounce } from 'lodash-es';

    const props = defineProps<{
        modelValue: string;
        placeholder?: string;
        label?: string;
    }>();

    const emit = defineEmits<{
        (e: 'update:modelValue', value: string): void;
        (e: 'search', value: string): void;
        (e: 'input-start'): void;
    }>();

    const inputId = useId();
    const innerValue = ref(props.modelValue);

    watch(
        () => props.modelValue,
        (newVal) => {
            innerValue.value = newVal;
            emit('input-start');
        },
    );

    // Debounce
    const debouncedSearch = debounce((val: string) => {
        emit('update:modelValue', val);
        emit('search', val);
    }, 300);

    watch(innerValue, (val) => {
        debouncedSearch(val);
    });

    onUnmounted(() => {
        debouncedSearch.cancel();
    });
</script>

<template>
    <div class="relative w-full">
        <!-- Label -->
        <label
            v-if="label"
            :for="inputId"
            class="mb-1.5 ml-2 block text-[10px] font-black uppercase tracking-[0.15em] text-rancho-ink-500"
        >
            {{ label }}
        </label>

        <div class="relative">
            <!-- Иконка -->
            <div
                class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 sm:pl-4"
                aria-hidden="true"
            >
                <MagnifyingGlassIcon class="h-4 w-4 text-rancho-ink-500" />
            </div>

            <!-- Input: без relative -->
            <input
                :id="inputId"
                v-model.trim="innerValue"
                type="search"
                role="searchbox"
                :placeholder="placeholder || 'Поиск...'"
                :aria-label="label || 'Найти'"
                class="w-full rounded-2xl border border-rancho-ink-800 bg-rancho-ink-950 py-3 pl-9 pr-4 text-xs text-rancho-ink-300 transition-all placeholder:text-rancho-ink-600 focus:border-rancho-buttercup focus:outline-none focus:ring-2 focus:ring-rancho-buttercup/20 disabled:cursor-not-allowed disabled:opacity-50 sm:pl-11 sm:text-sm"
            />
        </div>
    </div>
</template>

<style scoped>
    input[type='search']::-webkit-search-cancel-button,
    input[type='search']::-webkit-search-decoration {
        -webkit-appearance: none;
        appearance: none;
    }
</style>
