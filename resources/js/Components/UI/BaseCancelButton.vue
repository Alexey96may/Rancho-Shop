<script setup lang="ts">
    import { computed } from 'vue';

    import { Link } from '@inertiajs/vue3';

    import { ChevronLeftIcon } from '@heroicons/vue/24/outline';
    import { RouteParams } from 'ziggy-js';

    interface Props {
        href?: string;
        label?: string;
        routeName?: string;
        routeParams?: RouteParams<string>;
    }

    const props = withDefaults(defineProps<Props>(), {
        label: 'Отмена',
        routeParams: undefined,
    });

    const destination = computed(() => {
        if (props.href) return props.href;

        if (props.routeName) {
            try {
                return route(props.routeName, props.routeParams) as unknown as string;
            } catch (e) {
                console.error('Ziggy error:', e);
                return '#';
            }
        }
        return null;
    });

    const componentType = computed(() => (destination.value ? Link : 'button'));

    const bindingProps = computed(() => {
        if (destination.value) {
            return {
                href: destination.value,
                prefetch: true,
            };
        }
        return { type: 'button' as const };
    });
</script>

<template>
    <component
        :is="componentType"
        v-bind="bindingProps"
        class="group inline-flex items-center gap-2 rounded-xl px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 transition-all hover:bg-slate-800/50 hover:text-white focus:outline-none focus:ring-2 focus:ring-slate-700/50 focus:ring-offset-2 focus:ring-offset-slate-950 active:scale-95 disabled:pointer-events-none disabled:opacity-50"
    >
        <ChevronLeftIcon class="h-4 w-4 transition-transform group-hover:-translate-x-1" />

        <span>{{ label }}</span>
    </component>
</template>
