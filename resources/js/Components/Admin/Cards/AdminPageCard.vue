<script setup lang="ts">
    import { DocumentIcon, PencilIcon } from '@heroicons/vue/24/outline';

    import AdminDeleteButton from '@/Components/Admin/UI/AdminDeleteButton.vue';
    import AdminEditButton from '@/Components/Admin/UI/AdminEditButton.vue';
    import type { AdminPage } from '@/types';

    const props = defineProps<{
        page: AdminPage;
        index: number;
        isDeleting: boolean;
    }>();

    defineEmits<{
        (e: 'delete', page: AdminPage): void;
        (e: 'edit', id: number): void;
    }>();
</script>

<template>
    <div
        role="listitem"
        :style="{ '--i': index }"
        class="border-rancho-ink-800 bg-rancho-ink-900/30 hover:bg-rancho-ink-800/40 group grid grid-cols-12 items-center gap-x-3 gap-y-6 rounded-2xl border px-4 py-6 transition-all duration-300 md:gap-x-4 md:gap-y-0 md:rounded-3xl md:p-4 md:px-6"
        :class="{ 'scale-[0.97] opacity-50': isDeleting }"
    >
        <!-- 1. Иконка + Title + Slug -->
        <div class="col-span-10 min-w-0 md:order-1 md:col-span-5">
            <div class="flex items-center gap-3 md:gap-4">
                <div
                    class="bg-rancho-ink-800 text-rancho-ink-500 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl md:h-10 md:w-10"
                    aria-hidden="true"
                >
                    <DocumentIcon class="h-4 w-4 md:h-5 md:w-5" />
                </div>

                <div class="flex min-w-0 flex-col gap-1">
                    <span class="truncate text-sm font-bold text-white md:text-base">
                        {{ page.title }}
                    </span>
                    <span class="text-rancho-ink-500 truncate font-mono text-xs">
                        {{ page.slug }}
                    </span>
                </div>
            </div>
        </div>

        <!-- 2. Статус-точка -->
        <div class="col-span-2 flex justify-end md:order-3 md:col-span-2 md:justify-center">
            <div
                :aria-label="page.is_active ? 'Активна' : 'Неактивна'"
                :class="
                    page.is_active
                        ? 'bg-rancho-mint shadow-[0_0_10px_rgba(122,184,154,0.7)]'
                        : 'bg-rancho-ink-700'
                "
                class="h-2.5 w-2.5 rounded-full md:h-3 md:w-3"
                role="status"
            ></div>
        </div>

        <!-- 3. Бейдж типа + template -->
        <div
            class="col-span-12 flex items-center gap-2 md:order-2 md:col-span-3 md:flex-col md:items-center md:gap-1"
        >
            <span
                :class="page.type_color"
                class="inline-block truncate rounded-lg px-2 py-1 text-xs md:px-3"
            >
                {{ page.type_label }}
            </span>

            <span
                class="text-rancho-ink-500 truncate text-[10px] font-black uppercase tracking-wider"
            >
                {{ page.template }}
            </span>
        </div>

        <!-- 4. Кнопки -->
        <div
            class="col-span-12 flex justify-center gap-2 md:order-4 md:col-span-2 md:justify-end md:gap-2"
        >
            <AdminEditButton
                @click="$emit('edit', page.id)"
                :title="`Редактировать ${page.title}`"
                :disabled="isDeleting"
                :icon="PencilIcon"
            />

            <AdminDeleteButton
                @click="$emit('delete', page)"
                :title="page.can_delete ? 'Удалить страницу' : 'Нельзя удалить системную страницу'"
                :disabled="!page.can_delete || isDeleting"
            />
        </div>
    </div>
</template>
