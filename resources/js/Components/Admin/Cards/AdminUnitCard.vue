<script setup lang="ts">
    import { LinkIcon, PencilIcon, Trash2Icon } from 'lucide-vue-next';

    import AdminEditButton from '@/Components/Admin/UI/AdminEditButton.vue';
    import BaseDeleteButton from '@/Components/UI/BaseDeleteButton.vue';
    import BaseDragHandle from '@/Components/UI/BaseDragHandle.vue';
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

        <BaseDragHandle />

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
            <AdminEditButton
                @click="emit('edit', unit)"
                :title="`Редактировать ${unit.name}`"
                :disabled="disabled"
                :aria-label="`Редактировать ${unit.name}`"
                :icon="PencilIcon"
            />

            <BaseDeleteButton
                :disabled="!unit.can_delete || disabled"
                :title="!unit.can_delete ? 'Нельзя удалить: используется в товарах' : ''"
                @confirm="emit('delete', unit)"
                :aria-label="`Удалить ${unit.name}`"
            >
                <Trash2Icon class="h-4 w-4" />
            </BaseDeleteButton>
        </div>
    </div>
</template>
