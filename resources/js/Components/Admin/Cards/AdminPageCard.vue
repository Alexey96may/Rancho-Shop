<script setup lang="ts">
    import { router } from '@inertiajs/vue3';

    import { DocumentIcon, PencilIcon } from '@heroicons/vue/24/outline';

    import AdminDeleteButton from '@/Components/Admin/UI/AdminDeleteButton.vue';
    import AdminEditButton from '@/Components/Admin/UI/AdminEditButton.vue';
    import { useNavigation } from '@/composables/useNavigation';
    import { AdminPage } from '@/types';

    const props = defineProps<{
        page: AdminPage;
        index: number;
        isDeleting: boolean;
    }>();

    defineEmits<{
        (e: 'delete', page: AdminPage): void;
    }>();

    const { currentQuery } = useNavigation();

    const editPage = () => {
        router.visit(route('admin.pages.edit', props.page.id), {
            method: 'get',
            data: {
                back: currentQuery.value,
            },
            preserveState: true,
        });
    };
</script>

<template>
    <div
        role="listitem"
        :style="{ '--i': index }"
        class="group grid grid-cols-1 items-center gap-4 rounded-[2rem] border border-slate-800 bg-slate-900/30 p-4 transition-all duration-300 hover:bg-slate-800/40 md:grid-cols-12 md:px-8"
        :class="{ 'scale-[0.97] opacity-50': isDeleting }"
    >
        <div class="col-span-5">
            <div class="flex items-center gap-4">
                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-800 text-slate-500"
                >
                    <DocumentIcon class="h-5 w-5" />
                </div>
                <div class="flex min-w-0 flex-col">
                    <span class="truncate font-bold text-white">{{ page.title }}</span>
                    <span class="truncate font-mono text-[10px] text-slate-500">{{
                        page.slug
                    }}</span>
                </div>
            </div>
        </div>

        <div class="col-span-3 flex flex-col items-center gap-1">
            <span
                :class="page.type_color"
                class="rounded-lg px-3 py-0.5 text-[9px] font-black uppercase"
            >
                {{ page.type_label }}
            </span>
            <span class="text-[8px] font-black uppercase text-slate-500">
                {{ page.template }}
            </span>
        </div>

        <div class="col-span-2 flex justify-center">
            <div
                :aria-label="page.is_active ? 'Активна' : 'Неактивна'"
                :class="
                    page.is_active ? 'bg-emerald-500 shadow-[0_0_10px_#10b981]' : 'bg-slate-700'
                "
                class="h-2.5 w-2.5 rounded-full"
            ></div>
        </div>

        <div class="col-span-2 flex justify-end gap-2">
            <AdminEditButton
                @click="editPage"
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
