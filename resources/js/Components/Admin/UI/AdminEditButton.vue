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
        class="focus-visible:ring-offset-rancho-ink-950 group relative inline-flex items-center justify-center rounded-xl transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-rancho-buttercup/40 focus-visible:ring-offset-2"
        :class="[
            isDisabled
                ? 'bg-rancho-ink-800 text-rancho-ink-600 cursor-not-allowed opacity-40'
                : 'bg-rancho-ink-800 text-rancho-ink-400 hover:text-rancho-ink-950 hover:bg-rancho-buttercup active:scale-95',
        ]"
    >
        <div class="relative flex items-center justify-center p-2.5">
            <component
                :is="icon"
                class="h-5 w-5 transition-transform"
                :class="{ 'group-hover:scale-110': !isDisabled }"
                aria-hidden="true"
            />
        </div>

        <span class="sr-only">{{ title }}</span>
    </button>
</template>
