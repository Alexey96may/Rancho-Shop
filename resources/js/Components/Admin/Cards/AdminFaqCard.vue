<script setup lang="ts">
    import {
        Bars2Icon,
        ChevronDownIcon,
        EyeIcon,
        EyeSlashIcon,
        PencilSquareIcon,
        TrashIcon,
    } from '@heroicons/vue/24/outline';

    import { AdminFaq } from '@/types';
    import { formatDateTime } from '@/utils/format';

    defineProps<{
        faq: AdminFaq;
        isOpen: boolean;
        isDeleting: boolean;
    }>();

    const emit = defineEmits(['toggle', 'edit', 'delete', 'toggle-status']);
</script>

<template>
    <div
        class="group overflow-hidden rounded-3xl border border-slate-800 bg-slate-900/30 transition-all"
        :class="{
            'border-orange-500/50 bg-slate-900/60 ring-1 ring-orange-500/20': isOpen,
            'scale-[0.97] opacity-50': isDeleting,
        }"
    >
        <div class="flex items-center">
            <div
                class="drag-handle flex cursor-grab items-center justify-center p-5 text-slate-700 hover:text-slate-400 active:cursor-grabbing"
            >
                <Bars2Icon class="h-5 w-5" />
            </div>

            <button
                @click="emit('toggle', faq.id)"
                class="flex flex-1 items-center justify-between py-5 pr-5 text-left focus:outline-none sm:pr-8"
                :aria-expanded="isOpen"
            >
                <div class="flex items-center gap-4">
                    <h3 class="font-bold text-slate-200 transition-colors group-hover:text-white">
                        {{ faq.question }}
                    </h3>
                </div>

                <div class="flex items-center gap-4">
                    <span
                        v-if="!faq.is_published"
                        class="hidden rounded-full border border-slate-700 bg-slate-800 px-3 py-1 text-[9px] font-black uppercase tracking-widest text-slate-500 sm:block"
                    >
                        Черновик
                    </span>
                    <ChevronDownIcon
                        class="h-5 w-5 text-slate-500 transition-transform duration-300"
                        :class="{ 'rotate-180 text-orange-500': isOpen }"
                    />
                </div>
            </button>
        </div>

        <div
            class="grid transition-[grid-template-rows,opacity] duration-300 ease-in-out"
            :class="
                isOpen
                    ? 'grid-template-rows-[1fr] opacity-100'
                    : 'grid-template-rows-[0fr] opacity-0'
            "
        >
            <div class="overflow-hidden">
                <div class="border-t border-slate-800/50 p-5 sm:px-14 sm:pb-8">
                    <div
                        class="prose prose-orange prose-invert mb-6 max-w-none text-sm leading-relaxed text-slate-400"
                        v-html="faq.answer"
                    ></div>

                    <div
                        class="flex items-center justify-between gap-4 border-t border-slate-800/50 pt-6"
                    >
                        <div
                            class="text-[10px] font-black uppercase tracking-widest text-slate-600"
                        >
                            Порядок: {{ faq.sort_order }} •
                            {{ formatDateTime(faq.updated_at, 'dd.MM.yyyy HH:mm') }}
                        </div>

                        <div class="flex gap-2">
                            <button
                                @click="emit('toggle-status', faq.id)"
                                class="rounded-xl p-2.5 transition-all"
                                :class="
                                    faq.is_published
                                        ? 'bg-emerald-500/10 text-emerald-500 hover:bg-emerald-500/20'
                                        : 'bg-red-500/10 text-red-500/60 hover:text-red-500'
                                "
                            >
                                <component
                                    :is="faq.is_published ? EyeIcon : EyeSlashIcon"
                                    class="h-5 w-5"
                                />
                            </button>

                            <button
                                @click="emit('edit', faq)"
                                class="rounded-xl bg-slate-800 p-2.5 text-slate-400 transition-all hover:bg-slate-700 hover:text-orange-500"
                            >
                                <PencilSquareIcon class="h-5 w-5" />
                            </button>

                            <button
                                @click="emit('delete', faq.id)"
                                class="rounded-xl bg-red-500/10 p-2.5 text-red-500/60 transition-all hover:bg-red-500 hover:text-white"
                            >
                                <TrashIcon class="h-5 w-5" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
    .grid-template-rows-\[0fr\] {
        grid-template-rows: 0fr;
    }
    .grid-template-rows-\[1fr\] {
        grid-template-rows: 1fr;
    }
</style>
