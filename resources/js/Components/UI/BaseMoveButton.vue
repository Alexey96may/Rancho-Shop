<script setup lang="ts">
    import { computed } from 'vue';

    import {
        ArrowDownIcon,
        ArrowUpIcon,
        ChevronDownIcon,
        ChevronUpIcon,
    } from '@heroicons/vue/24/outline';

    type Direction = 'up' | 'down';

    interface Props {
        direction: Direction;
        index?: number;
        disabled?: boolean;
        hidden?: boolean;
        variant?: 'chevron' | 'arrow';
    }

    const props = withDefaults(defineProps<Props>(), {
        index: 0,
        disabled: false,
        hidden: false,
        variant: 'chevron',
    });

    const emit = defineEmits<{
        (e: 'move', direction: Direction): void;
    }>();

    const currentIcon = computed(() => {
        if (props.variant === 'chevron') {
            return props.direction === 'up' ? ChevronUpIcon : ChevronDownIcon;
        }
        return props.direction === 'up' ? ArrowUpIcon : ArrowDownIcon;
    });

    const label = computed(() => {
        const action = props.direction === 'up' ? 'выше' : 'ниже';
        return `Переместить элемент ${props.index + 1} ${action}`;
    });

    const handleClick = () => {
        if (!props.disabled) {
            emit('move', props.direction);
        }
    };
</script>

<template>
    <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="scale-95 opacity-0"
        enter-to-class="scale-100 opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="scale-100 opacity-100"
        leave-to-class="scale-95 opacity-0"
    >
        <button
            v-show="!hidden"
            type="button"
            @click="handleClick"
            :disabled="disabled"
            :aria-label="label"
            :title="label"
            class="shadow-xl group relative flex items-center justify-center rounded-lg bg-slate-800 p-2 text-slate-400 outline-none transition-all hover:bg-slate-700 hover:text-white focus:ring-2 focus:ring-orange-500 active:scale-90 disabled:cursor-not-allowed disabled:opacity-30"
        >
            <component
                :is="currentIcon"
                class="h-4 w-4 transition-transform group-hover:-translate-y-0.5 group-active:translate-y-0"
                :class="{ 'group-hover:translate-y-0.5': direction === 'down' }"
                aria-hidden="true"
            />

            <span class="sr-only">{{ label }}</span>
        </button>
    </Transition>
</template>
