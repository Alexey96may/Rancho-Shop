<script setup lang="ts">
    import { InboxIcon } from '@heroicons/vue/24/outline';

    interface Props {
        title?: string;
        description?: string;
        showAction?: boolean;
        actionText?: string;
    }

    withDefaults(defineProps<Props>(), {
        title: 'Ничего не найдено',
        description: 'По вашему запросу нет данных в реестре',
        showAction: false,
        actionText: 'Сбросить фильтры',
    });

    defineEmits(['action']);
</script>

<template>
    <div
        class="flex flex-col items-center justify-center rounded-[3rem] border border-dashed border-slate-800 bg-slate-900/10 py-24 text-center"
        role="alert"
    >
        <div class="shadow-inner rounded-full bg-slate-900 p-6 text-slate-700">
            <slot name="icon">
                <InboxIcon class="h-12 w-12" />
            </slot>
        </div>

        <h3 class="mt-6 text-xl font-bold text-white">{{ title }}</h3>

        <p class="mt-2 text-sm text-slate-500">
            {{ description }}
        </p>

        <button
            v-if="showAction"
            type="button"
            @click="$emit('action')"
            class="mt-8 text-xs font-black uppercase tracking-widest text-orange-500 transition-colors hover:text-orange-400"
        >
            {{ actionText }}
        </button>
    </div>
</template>
