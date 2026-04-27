<script setup lang="ts">
    import { Link } from '@inertiajs/vue3';

    import { ChevronRightIcon, EyeIcon, EyeOffIcon, Settings2Icon } from 'lucide-vue-next';

    import { AdminLandingBlock } from '@/types';

    const props = defineProps<{
        block: AdminLandingBlock;
    }>();

    const emit = defineEmits<{
        (e: 'toggle', id: number): void;
    }>();
</script>

<template>
    <article
        :aria-labelledby="`block-title-${block.id}`"
        class="shadow-xl group relative flex flex-col rounded-[2.5rem] border border-slate-800 bg-slate-900/40 p-8 transition-all hover:border-orange-500/30 hover:bg-slate-900/60"
    >
        <div class="mb-6 flex items-start justify-between">
            <div class="space-y-2">
                <span
                    role="status"
                    class="rounded-lg border border-slate-800 bg-slate-950 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-orange-500"
                >
                    {{ block.label }}
                </span>
                <h3
                    :id="`block-title-${block.id}`"
                    class="text-xl font-bold leading-tight text-slate-100"
                    v-html="block.title"
                ></h3>
            </div>

            <button
                @click="emit('toggle', block.id)"
                type="button"
                :aria-pressed="block.is_visible"
                :aria-label="
                    block.is_visible ? `Скрыть блок ${block.label}` : `Показать блок ${block.label}`
                "
                class="rounded-2xl border p-3 transition-all focus:outline-none focus:ring-2 focus:ring-orange-500/50 active:scale-95"
                :class="
                    block.is_visible
                        ? 'border-emerald-500/20 bg-emerald-500/10 text-emerald-500'
                        : 'border-slate-700 bg-slate-800 text-slate-600'
                "
            >
                <EyeIcon v-if="block.is_visible" class="h-5 w-5" aria-hidden="true" />
                <EyeOffIcon v-else class="h-5 w-5" aria-hidden="true" />
            </button>
        </div>

        <div
            class="mb-8 grid grid-cols-2 gap-3"
            role="list"
            :aria-label="`Предпросмотр элементов блока ${block.label}`"
        >
            <div
                v-for="(item, idx) in block.content.slice(0, 4)"
                :key="idx"
                role="listitem"
                class="flex items-center gap-2 rounded-2xl border border-slate-800/50 bg-slate-950/60 p-3"
            >
                <div class="h-1.5 w-1.5 rounded-full bg-orange-500" aria-hidden="true"></div>
                <span class="truncate text-[10px] font-bold text-slate-400">
                    {{ item.title || 'Элемент без названия' }}
                </span>
            </div>

            <div
                v-if="block.content.length === 0"
                class="col-span-2 py-2 text-center text-[10px] italic text-slate-600"
            >
                Контент не заполнен
            </div>
        </div>

        <Link
            :href="route('admin.features.edit', block.id)"
            :aria-label="`Настроить контент для блока ${block.label}`"
            class="mt-auto flex w-full items-center justify-center gap-2 rounded-2xl bg-slate-800 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-white transition-all hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-500 group-hover:bg-slate-700"
        >
            <Settings2Icon class="h-4 w-4" aria-hidden="true" />
            <span>Настроить контент</span>
            <ChevronRightIcon
                class="ml-1 h-4 w-4 -translate-x-2 opacity-0 transition-all group-hover:translate-x-0 group-hover:opacity-100"
                aria-hidden="true"
            />
        </Link>
    </article>
</template>
