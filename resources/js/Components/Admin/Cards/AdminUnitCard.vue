<script setup lang="ts">
    import { GripVerticalIcon, LinkIcon, PencilIcon, Trash2Icon } from 'lucide-vue-next';

    import { UnitAdmin } from '@/types';

    defineProps<{
        unit: UnitAdmin;
        disabled?: boolean;
    }>();

    const emit = defineEmits<{
        (e: 'edit', unit: UnitAdmin): void;
        (e: 'delete', unit: UnitAdmin): void;
    }>();
</script>

<template>
    <div
        role="listitem"
        class="group flex items-center gap-4 rounded-3xl border border-slate-800 bg-slate-900/40 p-4 transition-all hover:border-slate-600 active:border-orange-500/50"
        :class="[disabled ? 'opacity-50' : '']"
    >
        <div
            v-if="!disabled"
            class="absolute -right-8 -top-8 h-24 w-24 rounded-full bg-orange-500/5 blur-[50px] transition-all group-hover:bg-orange-500/10"
        ></div>
        <div
            class="drag-handle cursor-grab p-2 text-slate-700 transition-colors hover:text-slate-400 active:cursor-grabbing"
            aria-label="Перетащить для изменения порядка"
        >
            <GripVerticalIcon class="h-5 w-5" />
        </div>

        <div class="grid flex-1 grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="flex flex-col">
                <span class="text-[10px] font-black uppercase tracking-widest text-slate-500"
                    >Название</span
                >
                <span class="text-sm font-bold text-white">{{ unit.name }}</span>
            </div>
            <div class="flex flex-col">
                <span class="text-[10px] font-black uppercase tracking-widest text-slate-500"
                    >Сокращение</span
                >
                <span class="text-sm font-bold text-orange-500">{{ unit.short }}</span>
            </div>
            <div class="flex flex-col">
                <span class="text-[10px] font-black uppercase tracking-widest text-slate-500"
                    >Slug</span
                >
                <div class="flex items-center gap-1 text-slate-400">
                    <LinkIcon class="h-3 w-3" />
                    <span class="font-mono text-[11px]">{{ unit.slug }}</span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-1">
            <button
                @click="emit('edit', unit)"
                class="rounded-xl p-2.5 text-slate-500 transition-all hover:bg-slate-800 hover:text-white"
                :aria-label="`Редактировать ${unit.name}`"
            >
                <PencilIcon class="h-4 w-4" />
            </button>
            <button
                @click="emit('delete', unit)"
                :disabled="!unit.can_delete || disabled"
                class="rounded-xl p-2.5 text-slate-600 transition-all hover:bg-rose-500/10 hover:text-rose-500 disabled:opacity-20"
                :title="!unit.can_delete ? 'Нельзя удалить: используется в товарах' : ''"
                :aria-label="`Удалить ${unit.name}`"
            >
                <Trash2Icon class="h-4 w-4" />
            </button>
        </div>
    </div>
</template>
