<script setup lang="ts">
    import { type Component, computed } from 'vue';

    import { CheckIcon } from '@heroicons/vue/24/outline';

    interface Props {
        processing: boolean;
        label?: string;
        isEdit?: boolean;
        icon?: Component;
        withIcon?: boolean;
    }

    const props = withDefaults(defineProps<Props>(), {
        label: '',
        isEdit: false,
        icon: CheckIcon,
        withIcon: true,
    });

    const buttonText = computed(() => {
        if (props.label) return props.label;
        return props.isEdit ? 'Сохранить изменения' : 'Создать запись';
    });
</script>

<template>
    <button
        type="submit"
        :disabled="processing"
        class="group relative flex items-center justify-center gap-3 rounded-2xl bg-orange-600 px-12 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-white transition-all hover:bg-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500/50 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-70"
        :aria-busy="processing"
    >
        <div v-if="processing" class="flex items-center gap-3">
            <span
                class="h-4 w-4 animate-spin rounded-full border-2 border-white/20 border-t-white"
            ></span>
            <span>Обработка...</span>
        </div>

        <template v-else>
            <component
                v-if="withIcon"
                :is="icon"
                class="h-4 w-4 transition-transform group-hover:scale-110"
            />
            <span>{{ buttonText }}</span>
        </template>
    </button>
</template>
