<script setup lang="ts">
    import { type Component, computed } from 'vue';

    import { CheckIcon } from '@heroicons/vue/24/outline';

    interface Props {
        processing: boolean;
        disabled?: boolean;
        label?: string;
        isEdit?: boolean;
        icon?: Component;
        withIcon?: boolean;
    }

    const props = withDefaults(defineProps<Props>(), {
        disabled: false,
        label: '',
        isEdit: false,
        icon: CheckIcon,
        withIcon: true,
    });

    const isDisabled = computed(() => props.processing || props.disabled);

    const buttonText = computed(() => {
        if (props.label) return props.label;
        return props.isEdit ? 'Сохранить изменения' : 'Создать запись';
    });
</script>

<template>
    <button
        type="submit"
        :disabled="isDisabled"
        :aria-busy="processing"
        :aria-disabled="isDisabled"
        class="shadow-md hover:shadow-lg disabled:shadow-none disabled:hover:shadow-none group relative flex items-center justify-center gap-3 rounded-xl bg-rancho-pine px-4 py-2 text-[10px] font-black uppercase tracking-[0.2em] text-white transition-all hover:bg-rancho-forest focus:outline-none focus-visible:ring-4 focus-visible:ring-rancho-pine/30 active:scale-[0.98] disabled:cursor-not-allowed disabled:bg-rancho-ink-200 disabled:text-rancho-ink-500 disabled:hover:bg-rancho-ink-200 disabled:active:scale-100 sm:rounded-2xl sm:px-6 sm:px-8 sm:py-4"
    >
        <div v-if="processing" class="flex items-center gap-3">
            <span
                class="h-5 w-5 animate-spin rounded-full border-2 border-white/20 border-t-white"
                aria-hidden="true"
            ></span>
            <span>Обработка...</span>
        </div>

        <template v-else>
            <component
                v-if="withIcon"
                :is="icon"
                class="h-5 w-5 transition-transform group-hover:scale-110"
                aria-hidden="true"
            />
            <span class="hidden sm:inline">{{ buttonText }}</span>
        </template>
    </button>
</template>
