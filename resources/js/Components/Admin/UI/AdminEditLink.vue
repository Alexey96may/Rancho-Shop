<script setup lang="ts">
    import { type Component, computed } from 'vue';

    import { Link } from '@inertiajs/vue3';

    import { PencilSquareIcon } from '@heroicons/vue/24/outline';

    interface Props {
        href: string;
        title: string;
        icon?: Component;
        disabled?: boolean;
    }

    const props = withDefaults(defineProps<Props>(), {
        icon: PencilSquareIcon,
        disabled: false,
    });

    const isDisabled = computed(() => props.disabled);
</script>

<template>
    <Link
        :href="isDisabled ? '#' : href"
        :aria-label="title"
        :title="title"
        class="group relative inline-flex items-center justify-center rounded-xl transition-all focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-slate-900"
        :class="[
            isDisabled
                ? 'cursor-not-allowed bg-slate-800 text-slate-600 opacity-40'
                : 'bg-slate-800 text-slate-400 hover:bg-orange-600 hover:text-white active:scale-95',
        ]"
    >
        <div class="relative flex items-center justify-center p-2.5">
            <component
                :is="props.icon"
                class="h-5 w-5 transition-transform group-hover:scale-110"
                aria-hidden="true"
            />
        </div>

        <span class="sr-only">{{ title }}</span>
    </Link>
</template>
