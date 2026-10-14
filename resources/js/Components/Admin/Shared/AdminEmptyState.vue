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
        class="border-rancho-ink-700 bg-rancho-ink-900/10 flex flex-col items-center justify-center rounded-2xl border border-dashed px-4 py-16 text-center sm:rounded-3xl sm:py-24"
        role="alert"
    >
        <div class="bg-rancho-ink-900 text-rancho-ink-500 shadow-inner rounded-full p-5 sm:p-6">
            <slot name="icon">
                <InboxIcon class="h-10 w-10 sm:h-12 sm:w-12" aria-hidden="true" />
            </slot>
        </div>

        <h3 class="mt-6 text-lg font-bold text-white sm:text-xl">
            {{ title }}
        </h3>

        <p class="text-rancho-ink-400 mt-2 max-w-md text-sm">
            {{ description }}
        </p>

        <button
            v-if="showAction"
            type="button"
            @click="$emit('action')"
            class="border-rancho-ink-700 bg-rancho-ink-800 text-rancho-mint hover:border-rancho-mint/40 hover:bg-rancho-ink-700 focus-visible:ring-rancho-mint/40 mt-8 rounded-xl border px-4 py-2.5 text-[10px] font-black uppercase tracking-widest transition-all focus:outline-none focus-visible:ring-2 active:scale-95"
        >
            {{ actionText }}
        </button>
    </div>
</template>
