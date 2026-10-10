<script setup lang="ts">
    import { DocumentIcon, PencilIcon } from '@heroicons/vue/24/outline';

    import AdminDeleteButton from '@/Components/Admin/UI/AdminDeleteButton.vue';
    import AdminEditButton from '@/Components/Admin/UI/AdminEditButton.vue';
    import { AdminPage } from '@/types';

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
        class="group grid grid-cols-12 items-center gap-x-3 gap-y-3 rounded-2xl border border-slate-800 bg-slate-900/30 p-3 transition-all duration-300 hover:bg-slate-800/40 md:gap-x-4 md:gap-y-0 md:rounded-[2rem] md:p-4 md:px-8"
        :class="{ 'scale-[0.97] opacity-50': isDeleting }"
    >
        <!-- 1. Иконка + Title + Slug -->
        <div class="col-span-10 min-w-0 md:order-1 md:col-span-5">
            <div class="flex items-center gap-3 md:gap-4">
                <div
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-800 text-slate-500 md:h-10 md:w-10"
                >
                    <DocumentIcon class="h-4 w-4 md:h-5 md:w-5" />
                </div>
                <div class="flex min-w-0 flex-col">
                    <span class="truncate text-sm font-bold text-white md:text-base">
                        {{ page.title }}
                    </span>
                    <span class="truncate font-mono text-[10px] text-slate-500">
                        {{ page.slug }}
                    </span>
                </div>
            </div>
        </div>

        <!-- 2. Статус-точка (мобилка: справа от заголовка; десктоп: между бейджем и кнопками) -->
        <div class="col-span-2 flex justify-end md:order-3 md:col-span-2 md:justify-center">
            <div
                :aria-label="page.is_active ? 'Активна' : 'Неактивна'"
                :class="
                    page.is_active ? 'bg-emerald-500 shadow-[0_0_10px_#10b981]' : 'bg-slate-700'
                "
                class="h-2.5 w-2.5 rounded-full md:h-3 md:w-3"
            ></div>
        </div>

        <!-- 3. Бейдж типа + template (мобилка: ряд 2 слева; десктоп: колонка 2) -->
        <div
            class="col-span-7 flex items-center gap-2 md:order-2 md:col-span-3 md:flex-col md:items-center md:gap-1"
        >
            <span
                :class="page.type_color"
                class="inline-block truncate rounded-lg px-2 py-0.5 text-[9px] font-black uppercase md:px-3"
            >
                {{ page.type_label }}
            </span>
            <span class="truncate text-[8px] font-black uppercase tracking-wider text-slate-500">
                {{ page.template }}
            </span>
        </div>

        <!-- 4. Кнопки (мобилка: ряд 2 справа; десктоп: колонка 4) -->
        <div class="col-span-5 flex justify-end gap-1.5 md:order-4 md:col-span-2 md:gap-2">
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
