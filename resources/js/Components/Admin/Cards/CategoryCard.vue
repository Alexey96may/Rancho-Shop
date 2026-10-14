<script setup lang="ts">
    import { PencilSquareIcon, TagIcon } from '@heroicons/vue/24/outline';

    import AdminDeleteButton from '@/Components/Admin/UI/AdminDeleteButton.vue';
    import AdminEditButton from '@/Components/Admin/UI/AdminEditButton.vue';
    import LucideIcon from '@/Components/UI/LucideIcon.vue';
    import type { AdminCategory } from '@/types';

    defineProps<{
        category: AdminCategory;
        disabled: boolean;
    }>();

    defineEmits(['edit', 'delete']);
</script>

<template>
    <div
        role="listitem"
        class="border-rancho-ink-800 bg-rancho-ink-900/30 hover:border-rancho-mint/40 hover:bg-rancho-ink-900/50 group relative flex flex-col justify-between overflow-hidden rounded-3xl border p-6 transition-all"
        :class="[disabled ? 'scale-[0.97] opacity-50' : '']"
    >
        <div class="flex items-start justify-between">
            <div
                class="bg-rancho-ink-800 text-rancho-mint group-hover:bg-rancho-mint group-hover:text-rancho-ink-950 flex h-12 w-12 items-center justify-center rounded-2xl transition-colors"
                aria-hidden="true"
            >
                <LucideIcon
                    v-if="category.icon"
                    :name="category.icon"
                    :size="24"
                    class="text-rancho-mint group-hover:text-rancho-ink-950 transition-colors"
                />
                <TagIcon v-else class="h-6 w-6" />
            </div>

            <div class="flex items-center gap-2">
                <span
                    class="rounded-full px-2.5 py-1 text-[9px] font-black uppercase tracking-tighter"
                    :class="
                        category.type === 'product'
                            ? 'bg-rancho-sky/15 text-rancho-sky'
                            : 'bg-rancho-ember/15 text-rancho-ember'
                    "
                >
                    {{ category.type === 'product' ? 'Товар' : 'Животное' }}
                </span>

                <div
                    class="h-2 w-2 rounded-full"
                    :class="
                        category.is_active
                            ? 'bg-rancho-mint shadow-[0_0_8px_rgba(122,184,154,0.5)]'
                            : 'bg-rancho-ink-700'
                    "
                    :aria-label="category.is_active ? 'Активна' : 'Неактивна'"
                ></div>
            </div>
        </div>

        <div class="mt-8">
            <h4 class="line-clamp-1 text-lg font-black uppercase tracking-tight text-white">
                {{ category.name }}
            </h4>
            <p class="text-rancho-ink-500 mt-1 font-mono text-xs italic">/{{ category.slug }}</p>
            <p
                v-if="category.description"
                class="text-rancho-ink-300 mt-4 line-clamp-3 font-mono text-xs"
            >
                «{{ category.description }}»
            </p>
        </div>

        <div
            class="border-rancho-ink-800 mt-8 flex items-center justify-between gap-2 border-t pt-4"
        >
            <span
                class="text-rancho-ink-500 line-clamp-1 text-[10px] font-black uppercase tracking-widest"
            >
                Порядок: {{ category.sort_order }}
            </span>

            <div class="flex gap-2">
                <AdminEditButton
                    @click="$emit('edit', category)"
                    :title="'Редактировать ' + category.name"
                    :disabled="disabled"
                    :icon="PencilSquareIcon"
                />

                <AdminDeleteButton
                    @click="$emit('delete', category.id)"
                    :title="'Удалить ' + category.name"
                    :disabled="disabled"
                />
            </div>
        </div>
    </div>
</template>
