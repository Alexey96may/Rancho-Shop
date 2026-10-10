<script setup lang="ts">
    import { type Component, computed } from 'vue';

    import { Link } from '@inertiajs/vue3';

    import { PlusIcon } from '@heroicons/vue/24/outline';

    interface Props {
        href?: string;
        label?: string;
        disabled?: boolean;
        type?: 'button' | 'submit' | 'reset';
        icon?: Component;
        compactOnMobile?: boolean;
    }

    const props = withDefaults(defineProps<Props>(), {
        label: 'Создать',
        disabled: false,
        type: 'button',
        compactOnMobile: true,
    });

    const componentType = computed(() => (props.href ? Link : 'button'));

    const bindingProps = computed(() => {
        if (props.href) {
            return { href: props.href };
        }
        return { type: props.type, disabled: props.disabled };
    });
</script>

<template>
    <component
        :is="componentType"
        v-bind="bindingProps"
        class="group flex h-10 items-center justify-center gap-2 rounded-xl bg-orange-600 px-4 text-[10px] font-black uppercase tracking-[0.15em] text-white transition-all hover:bg-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500/50 active:scale-95 disabled:pointer-events-none disabled:opacity-50 sm:h-12 sm:gap-3 sm:rounded-2xl sm:px-6 sm:tracking-[0.2em] lg:h-[54px] lg:px-8"
        :class="compactOnMobile ? 'w-10 px-0 sm:w-auto sm:px-6 lg:px-8' : ''"
    >
        <component
            v-if="icon"
            :is="icon"
            class="h-4 w-4 shrink-0 transition-transform group-hover:scale-110 sm:h-5 sm:w-5"
        />
        <PlusIcon
            v-else
            class="h-4 w-4 shrink-0 transition-transform group-hover:scale-110 sm:h-5 sm:w-5"
        />

        <span :class="compactOnMobile ? 'hidden sm:inline' : ''">{{ label }}</span>
    </component>
</template>
