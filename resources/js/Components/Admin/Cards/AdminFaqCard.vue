<script setup lang="ts">
    import { computed } from 'vue';

    import { Bars2Icon, ChevronDownIcon, PencilSquareIcon } from '@heroicons/vue/24/outline';

    import AdminDeleteButton from '@/Components/Admin/UI/AdminDeleteButton.vue';
    import AdminEditButton from '@/Components/Admin/UI/AdminEditButton.vue';
    import BaseVisibilityToggle from '@/Components/UI/BaseVisibilityToggle.vue';
    import type { AdminFaq } from '@/types';
    import { formatDateTime } from '@/utils/format';

    const props = defineProps<{
        faq: AdminFaq;
        isOpen: boolean;
        isDeleting: boolean;
    }>();

    const emit = defineEmits(['toggle', 'edit', 'delete', 'toggle-status']);

    const computedUpdatedAt = computed(() =>
        formatDateTime(props.faq.updated_at, 'dd.MM.yyyy HH:mm'),
    );
</script>

<template>
    <div
        class="border-rancho-ink-800 bg-rancho-ink-900/30 group overflow-hidden rounded-3xl border transition-all"
        :class="{
            'border-rancho-mint/40 bg-rancho-ink-900/60 ring-rancho-mint/20 ring-1': isOpen,
            'scale-[0.97] opacity-50': isDeleting,
        }"
    >
        <!-- HEADER -->
        <div class="flex items-center">
            <div
                class="drag-handle text-rancho-ink-600 hover:text-rancho-ink-400 flex cursor-grab items-center justify-center p-3 active:cursor-grabbing sm:p-5"
                aria-hidden="true"
            >
                <Bars2Icon class="h-5 w-5" />
            </div>

            <button
                @click="emit('toggle', faq.id)"
                class="focus-visible:ring-rancho-mint/40 flex flex-1 items-center justify-between gap-2 py-5 pr-5 text-left focus:outline-none focus-visible:ring-2 sm:pr-8"
                :aria-expanded="isOpen"
            >
                <h3
                    class="text-rancho-ink-200 text-xs font-bold transition-colors group-hover:text-white sm:text-sm"
                >
                    {{ faq.question }}
                </h3>

                <div class="flex items-center gap-4">
                    <span
                        v-if="!faq.is_published"
                        class="border-rancho-ink-700 bg-rancho-ink-800 text-rancho-ink-500 hidden rounded-full border px-3 py-1 text-[9px] font-black uppercase tracking-widest sm:block"
                    >
                        Черновик
                    </span>

                    <ChevronDownIcon
                        class="text-rancho-ink-500 h-5 w-5 transition-transform duration-300"
                        :class="{ 'text-rancho-mint rotate-180': isOpen }"
                        aria-hidden="true"
                    />
                </div>
            </button>
        </div>

        <!-- BODY -->
        <div
            class="grid transition-[grid-template-rows,opacity] duration-300 ease-in-out"
            :class="
                isOpen
                    ? 'grid-template-rows-[1fr] opacity-100'
                    : 'grid-template-rows-[0fr] opacity-0'
            "
        >
            <div class="overflow-hidden">
                <div class="border-rancho-ink-800/60 border-t p-5 sm:px-14 sm:pb-8">
                    <div
                        class="text-rancho-ink-300 prose-a:text-rancho-mint prose prose-sm prose-invert mb-6 max-w-none text-xs leading-relaxed prose-headings:text-white prose-a:no-underline hover:prose-a:underline prose-strong:text-white sm:text-sm"
                        v-html="faq.answer"
                    ></div>

                    <div
                        class="border-rancho-ink-800/60 flex flex-col items-start justify-between gap-6 border-t pt-6 sm:flex-row sm:items-center"
                    >
                        <div
                            class="text-rancho-ink-500 flex flex-col gap-2 text-[10px] font-black uppercase tracking-widest"
                        >
                            <span> Порядок: {{ faq.sort_order }}</span>
                            <span> {{ computedUpdatedAt }}</span>
                        </div>

                        <div class="flex gap-2 self-end">
                            <BaseVisibilityToggle
                                :active="faq.is_published"
                                active-title="Виден на сайте"
                                :disabled="isDeleting"
                                inactive-title="Скрыт"
                                @toggle="emit('toggle-status', faq.id)"
                            />

                            <AdminEditButton
                                @click="emit('edit', faq)"
                                :title="'Редактировать ' + faq.question"
                                :disabled="isDeleting"
                                :icon="PencilSquareIcon"
                            />

                            <AdminDeleteButton
                                @click="emit('delete', faq.id)"
                                :title="'Удалить ' + faq.question"
                                :disabled="isDeleting"
                            />
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
