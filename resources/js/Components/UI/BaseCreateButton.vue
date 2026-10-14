<script setup lang="ts">
    import { type Component, computed } from 'vue';

    import { Link } from '@inertiajs/vue3';

    import { PlusIcon } from '@heroicons/vue/24/outline';

    type Theme = 'light' | 'dark';
    type Variant = 'solid' | 'outline' | 'ghost';

    interface Props {
        href?: string;
        label?: string;
        disabled?: boolean;
        type?: 'button' | 'submit' | 'reset';
        icon?: Component;
        compactOnMobile?: boolean;
        theme?: Theme;
        variant?: Variant;
    }

    const props = withDefaults(defineProps<Props>(), {
        href: undefined,
        label: 'Создать',
        disabled: false,
        type: 'button',
        icon: undefined,
        compactOnMobile: true,
        theme: 'light',
        variant: 'solid',
    });

    const isDark = computed(() => props.theme === 'dark');

    const componentType = computed(() => (props.href ? Link : 'button'));

    const bindingProps = computed(() => {
        if (props.href) {
            return { href: props.href };
        }
        return { type: props.type, disabled: props.disabled };
    });

    const colorClasses = computed(() => {
        if (props.variant === 'outline') {
            return isDark.value
                ? 'border-2 border-rancho-ink-600 bg-transparent text-rancho-ink-200 hover:border-rancho-buttercup hover:text-rancho-buttercup focus-visible:ring-rancho-buttercup/40 disabled:border-rancho-ink-800 disabled:text-rancho-ink-600'
                : 'border-2 border-rancho-ink-300 bg-transparent text-rancho-forest hover:border-rancho-pine hover:text-rancho-pine focus-visible:ring-rancho-pine/30 disabled:border-rancho-ink-200 disabled:text-rancho-ink-400';
        }

        if (props.variant === 'ghost') {
            return isDark.value
                ? 'bg-transparent text-rancho-ink-300 hover:bg-rancho-ink-800 hover:text-rancho-buttercup focus-visible:ring-rancho-buttercup/40 disabled:text-rancho-ink-600'
                : 'bg-transparent text-rancho-ink-500 hover:bg-rancho-ink-100 hover:text-rancho-pine focus-visible:ring-rancho-pine/30 disabled:text-rancho-ink-400';
        }

        return isDark.value
            ? 'bg-rancho-buttercup text-rancho-ink-950 hover:bg-rancho-buttercup/90 focus-visible:ring-rancho-buttercup/40 disabled:bg-rancho-ink-200 disabled:text-rancho-ink-500'
            : 'bg-rancho-pine text-white hover:bg-rancho-forest focus-visible:ring-rancho-pine/40 disabled:bg-rancho-ink-200 disabled:text-rancho-ink-500';
    });

    const shadowClasses = computed(() =>
        props.variant === 'solid' ? 'shadow-md hover:shadow-lg disabled:shadow-none' : '',
    );
</script>

<template>
    <component
        :is="componentType"
        v-bind="bindingProps"
        class="group inline-flex items-center justify-center gap-2 rounded-2xl px-4 text-[10px] font-black uppercase tracking-[0.15em] transition-all focus:outline-none focus-visible:ring-4 active:scale-95 disabled:pointer-events-none sm:gap-3 sm:rounded-2xl sm:px-6 sm:tracking-[0.2em]"
        :class="[
            colorClasses,
            shadowClasses,
            /* Высота — как у полей ввода и селекта */
            'h-11 sm:h-12',
            /* На мобилке — компактная, только иконка */
            compactOnMobile ? 'w-11 px-0 sm:w-auto sm:px-6' : '',
        ]"
    >
        <component
            v-if="icon"
            :is="icon"
            class="h-4 w-4 shrink-0 transition-transform group-hover:scale-110 sm:h-5 sm:w-5"
            aria-hidden="true"
        />
        <PlusIcon
            v-else
            class="h-4 w-4 shrink-0 transition-transform group-hover:scale-110 sm:h-5 sm:w-5"
            aria-hidden="true"
        />

        <span :class="compactOnMobile ? 'hidden sm:inline' : ''">{{ label }}</span>
    </component>
</template>
