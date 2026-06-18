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
    }

    const props = withDefaults(defineProps<Props>(), {
        label: 'Создать',
        disabled: false,
        type: 'button',
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
        class="group flex h-[54px] items-center gap-3 rounded-2xl bg-orange-600 px-8 text-[10px] font-black uppercase tracking-[0.2em] text-white transition-all hover:bg-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500/50 disabled:pointer-events-none disabled:opacity-50"
    >
        <component
            v-if="icon"
            :is="icon"
            class="h-5 w-5 transition-transform group-hover:scale-110"
        ></component>
        <PlusIcon v-else class="h-5 w-5 transition-transform group-hover:scale-110" />

        <span>{{ label }}</span>
    </component>
</template>
