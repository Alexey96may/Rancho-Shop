<script setup lang="ts">
    /**
     * LucideIcon Component
     * * A dynamic wrapper for Lucide icons that allows rendering by string name.
     * Useful for CMS-driven icons or when icon names are stored in a database.
     * * @example
     * <LucideIcon name="cow" :size="24" color="orange" />
     * * @prop {string} name - The kebab-case icon name (e.g., 'shopping-cart').
     * @prop {number|string} [size=18] - Icon width and height.
     * @prop {string} [color='currentColor'] - Stroke color.
     * @prop {number|string} [strokeWidth=2] - Thickness of the icon lines.
     */
    import { computed } from 'vue';

    import * as icons from 'lucide-vue-next';

    const props = defineProps<{
        name: string;
        size?: number | string;
        color?: string;
        strokeWidth?: number | string;
    }>();

    const iconComponent = computed(() => {
        const iconName = props.name
            .split('-')
            .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
            .join('');

        return (icons[iconName as keyof typeof icons] || icons.HelpCircle) as any;
    });

    const iconAttributes = computed(() => ({
        size: Number(props.size) || 18,
        color: props.color || 'currentColor',
        'stroke-width': Number(props.strokeWidth) || 2,
    }));
</script>

<template>
    <component :is="iconComponent" v-bind="iconAttributes" />
</template>
