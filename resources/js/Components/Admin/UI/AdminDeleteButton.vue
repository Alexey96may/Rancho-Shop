<script setup lang="ts">
    import { type FunctionalComponent, type HTMLAttributes, type VNodeProps, computed } from 'vue';

    import { TrashIcon } from '@heroicons/vue/24/outline';

    interface Props {
        title: string;
        icon?: FunctionalComponent<HTMLAttributes & VNodeProps>;
        loading?: boolean;
        disabled?: boolean;
    }

    const props = withDefaults(defineProps<Props>(), {
        icon: TrashIcon,
        loading: false,
        disabled: false,
    });

    const emit = defineEmits<{
        (e: 'click'): void;
    }>();

    const isDisabled = computed(() => props.disabled || props.loading);
</script>

<template>
    <button
        type="button"
        @click="emit('click')"
        :disabled="isDisabled"
        :aria-label="title"
        :title="title"
        class="focus-visible:ring-offset-rancho-ink-950 group relative inline-flex items-center justify-center rounded-xl transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500/40 focus-visible:ring-offset-2"
        :class="[
            isDisabled
                ? 'bg-rancho-ink-800 text-rancho-ink-600 cursor-not-allowed opacity-40'
                : 'bg-rancho-ink-800 text-rancho-ink-400 hover:bg-rose-500 hover:text-white active:scale-95',
        ]"
    >
        <div class="relative flex items-center justify-center p-2.5">
            <!-- Спиннер -->
            <svg
                v-if="loading"
                class="h-5 w-5 animate-spin"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                aria-hidden="true"
            >
                <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                />
                <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                />
            </svg>

            <!-- Иконка -->
            <component
                v-else
                :is="icon"
                class="h-5 w-5 transition-transform group-hover:scale-110"
                aria-hidden="true"
            />
        </div>

        <span class="sr-only">{{ title }}</span>
    </button>
</template>
