<script setup lang="ts">
    import { type Component, computed } from 'vue';

    import { PencilSquareIcon } from '@heroicons/vue/24/outline';

    interface Props {
        title: string;
        icon?: Component;
        disabled?: boolean;
        areaLabel?: string;
    }

    const props = withDefaults(defineProps<Props>(), {
        icon: PencilSquareIcon,
        disabled: false,
        areaLabel: 'Редактировать',
    });

    const emit = defineEmits<{
        (e: 'click'): void;
    }>();

    const isDisabled = computed(() => props.disabled);

    const handleClick = () => {
        if (!isDisabled.value) {
            emit('click');
        }
    };
</script>

<template>
    <button
        type="button"
        :disabled="isDisabled"
        :aria-label="areaLabel"
        :title="title"
        @click="handleClick"
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
                class="h-5 w-5 transition-transform"
                :class="{ 'group-hover:scale-110': !isDisabled }"
                aria-hidden="true"
            />
        </div>

        <span class="sr-only">{{ title }}</span>
    </button>
</template>
