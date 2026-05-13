<script setup lang="ts">
    import { PencilSquareIcon, TagIcon, TrashIcon } from '@heroicons/vue/24/outline';

    import LucideIcon from '@/Components/UI/LucideIcon.vue';
    import { AdminCategory } from '@/types';

    defineProps<{
        category: AdminCategory;
    }>();

    defineEmits(['edit', 'delete']);
</script>

<template>
    <div
        role="listitem"
        class="group relative flex flex-col justify-between overflow-hidden rounded-3xl border border-slate-900 bg-slate-900/30 p-6 transition-all hover:border-orange-500/50 hover:bg-slate-900/50"
    >
        <div class="flex items-start justify-between">
            <div
                class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-800 text-orange-300 transition-colors group-hover:bg-orange-600 group-hover:text-white"
                aria-hidden="true"
            >
                <LucideIcon
                    v-if="category.icon"
                    :name="category.icon"
                    :size="24"
                    class="text-orange-300"
                />
                <TagIcon v-else class="h-6 w-6" />
            </div>
            <div class="flex items-center gap-2">
                <span
                    class="rounded-full px-2.5 py-1 text-[9px] font-black uppercase tracking-tighter"
                    :class="
                        category.type === 'product'
                            ? 'bg-blue-500/10 text-blue-400'
                            : 'bg-purple-500/10 text-purple-400'
                    "
                >
                    {{ category.type === 'product' ? 'Товар' : 'Животное' }}
                </span>
                <div
                    class="h-2 w-2 rounded-full"
                    :class="
                        category.is_active
                            ? 'bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]'
                            : 'bg-slate-700'
                    "
                    :aria-label="category.is_active ? 'Активна' : 'Неактивна'"
                ></div>
            </div>
        </div>

        <div class="mt-6">
            <h4 class="line-clamp-1 text-lg font-black uppercase tracking-tight text-white">
                {{ category.name }}
            </h4>
            <p class="mt-1 font-mono text-xs italic text-slate-500">/{{ category.slug }}</p>
            <p v-if="category.description" class="mt-1 font-mono text-xs text-slate-300">
                «{{ category.description }}»
            </p>
        </div>

        <div class="mt-8 flex items-center justify-between border-t border-slate-800/50 pt-4">
            <span class="text-[10px] font-black uppercase tracking-widest text-slate-600">
                Порядок: {{ category.sort_order }}
            </span>
            <div class="flex gap-1">
                <button
                    @click="$emit('edit', category)"
                    class="rounded-xl p-2 text-slate-500 transition-all hover:bg-slate-800 hover:text-white"
                    :aria-label="'Редактировать ' + category.name"
                >
                    <PencilSquareIcon class="h-5 w-5" />
                </button>
                <button
                    @click="$emit('delete', category.id)"
                    class="rounded-xl p-2 text-slate-500 transition-all hover:bg-red-500/10 hover:text-red-500"
                    :aria-label="'Удалить ' + category.name"
                >
                    <TrashIcon class="h-5 w-5" />
                </button>
            </div>
        </div>
    </div>
</template>
