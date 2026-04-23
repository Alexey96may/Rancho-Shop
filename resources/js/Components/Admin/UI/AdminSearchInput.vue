<script setup lang="ts">
    /**
     * AdminSearchInput Component
     * * @prop {string} modelValue - Reactive search string.
     * @prop {string} placeholder - Input placeholder text.
     * @prop {string} label - Accessible label for screen readers.
     */
    import { onUnmounted, ref, watch } from 'vue';

    import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline';
    import { debounce } from 'lodash';

    const props = defineProps<{
        modelValue: string;
        placeholder?: string;
        label?: string;
    }>();

    const emit = defineEmits(['update:modelValue', 'search']);

    const innerValue = ref(props.modelValue);

    watch(
        () => props.modelValue,
        (newVal) => {
            innerValue.value = newVal;
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
    <div class="relative w-full max-w-sm">
        <label for="admin-search" class="sr-only">{{ label || 'Поиск' }}</label>

        <div
            class="pointer-events-none absolute inset-y-0 left-0 flex cursor-pointer items-center pl-4"
            aria-hidden="true"
        >
            <MagnifyingGlassIcon class="h-4 w-4 text-slate-500" />
        </div>

        <input
            id="admin-search"
            v-model="innerValue"
            type="search"
            role="searchbox"
            :placeholder="placeholder || 'Поиск...'"
            class="w-full rounded-2xl border-slate-800 bg-slate-950 py-3 pl-11 pr-4 text-lg text-white transition-all placeholder:text-slate-600 focus:border-orange-500 focus:ring-orange-500/20"
            :aria-label="label || 'Найти'"
        />
    </div>
</template>
