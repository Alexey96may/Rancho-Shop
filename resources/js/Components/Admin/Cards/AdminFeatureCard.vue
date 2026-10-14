<script setup lang="ts">
    import { Link } from '@inertiajs/vue3';

    import { ChevronRightIcon, Settings2Icon } from 'lucide-vue-next';

    import BaseVisibilityToggle from '@/Components/UI/BaseVisibilityToggle.vue';
    import type { AdminLandingBlock } from '@/types';

    defineProps<{
        block: AdminLandingBlock;
    }>();

    const emit = defineEmits<{
        (e: 'toggle', id: number): void;
    }>();
</script>

<template>
    <article
        :aria-labelledby="`block-title-${block.id}`"
        class="border-rancho-ink-800 bg-rancho-ink-900/40 shadow-xl hover:border-rancho-mint/40 hover:bg-rancho-ink-900/60 group relative flex flex-col rounded-2xl border p-6 transition-all sm:rounded-3xl sm:p-8"
    >
        <div class="mb-6 flex items-center justify-between gap-5">
            <div class="space-y-4">
                <span
                    role="status"
                    class="border-rancho-ink-800 bg-rancho-ink-950 text-rancho-mint rounded-lg border px-3 py-1 text-[10px] font-black uppercase tracking-widest"
                >
                    {{ block.label }}
                </span>

                <h3
                    :id="`block-title-${block.id}`"
                    class="text-rancho-ink-100 text-sm font-bold sm:text-xl"
                    v-html="block.title"
                ></h3>
            </div>

            <BaseVisibilityToggle
                :active="block.is_visible"
                active-title="Блок виден на сайте"
                inactive-title="Блок скрыт"
                @toggle="emit('toggle', block.id)"
            />
        </div>

        <!-- CONTENT PREVIEW -->
        <div
            class="mb-8 grid grid-cols-1 gap-2 sm:grid-cols-2 sm:gap-3"
            role="list"
            :aria-label="`Предпросмотр элементов блока ${block.label}`"
        >
            <div
                v-for="(item, idx) in block.content.slice(0, 3)"
                :key="idx"
                role="listitem"
                class="border-rancho-ink-800/60 bg-rancho-ink-950/60 flex items-center gap-2 rounded-2xl border p-3"
            >
                <div
                    class="bg-rancho-mint h-1.5 w-1.5 shrink-0 rounded-full"
                    aria-hidden="true"
                ></div>

                <span class="text-rancho-ink-300 truncate text-[10px] font-bold">
                    {{ item.title || 'Элемент без названия' }}
                </span>
            </div>

            <div
                v-if="block.content.length === 0"
                class="text-rancho-ink-500 col-span-1 py-2 text-center text-[10px] italic sm:col-span-2"
            >
                Контент не заполнен
            </div>
        </div>

        <!-- CTA -->
        <Link
            :href="route('admin.features.edit', block.id)"
            :aria-label="`Настроить контент для блока ${block.label}`"
            class="bg-rancho-ink-800 hover:bg-rancho-mint hover:text-rancho-ink-950 focus-visible:ring-rancho-mint/40 group-hover:bg-rancho-ink-700 group-hover:hover:bg-rancho-mint mt-auto flex w-full items-center justify-center gap-2 rounded-2xl py-4 text-[10px] font-black tracking-[0.2em] text-white transition-all focus:outline-none focus-visible:ring-2"
        >
            <Settings2Icon class="h-4 w-4" aria-hidden="true" />
            <span class="text-xs">Настроить</span>
        </Link>
    </article>
</template>
