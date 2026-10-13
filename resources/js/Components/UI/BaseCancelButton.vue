<script setup lang="ts">
    import { computed } from 'vue';

    import { Link } from '@inertiajs/vue3';

    import { ChevronLeftIcon } from '@heroicons/vue/24/outline';
    import type { RouteParams } from 'ziggy-js';

    type Theme = 'light' | 'dark';

    interface Props {
        href?: string;
        label?: string;
        routeName?: string;
        routeParams?: RouteParams<string>;
        theme?: Theme;
    }

    const props = withDefaults(defineProps<Props>(), {
        href: undefined,
        label: 'Отмена',
        routeName: undefined,
        routeParams: undefined,
        theme: 'light',
    });

    const isDark = computed(() => props.theme === 'dark');

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

    const colorClasses = computed(() =>
        isDark.value
            ? 'text-rancho-ink-300 hover:bg-rancho-ink-800 hover:text-white focus-visible:ring-rancho-ink-600'
            : 'text-rancho-ink-500 hover:bg-rancho-ink-100 hover:text-rancho-forest focus-visible:ring-rancho-ink-300',
    );
</script>

<template>
    <component
        :is="componentType"
        v-bind="bindingProps"
        class="group inline-flex items-center gap-2 rounded-xl px-3 py-2 text-[10px] font-black uppercase tracking-[0.2em] transition-all focus:outline-none focus-visible:ring-2 active:scale-95 disabled:pointer-events-none disabled:opacity-50 sm:px-6 sm:py-4"
        :class="colorClasses"
    >
        <ChevronLeftIcon
            class="h-4 w-4 transition-transform group-hover:-translate-x-1"
            aria-hidden="true"
        />

        <span>{{ label }}</span>
    </component>
</template>
