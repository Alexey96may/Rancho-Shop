<script setup lang="ts">
    import { ref } from 'vue';

    import { useForm } from '@inertiajs/vue3';

    import { LayoutIcon, PlusIcon, SaveIcon } from 'lucide-vue-next';

    import ContentItemCard from '@/Components/Admin/Cards/AdminContentItemCard.vue';
    import AdminPageHeader from '@/Components/Admin/Shared/AdminPageHeader.vue';
    import BaseCancelButton from '@/Components/UI/BaseCancelButton.vue';
    import BaseInput from '@/Components/UI/BaseInput.vue';
    import BaseSubmitButton from '@/Components/UI/BaseSubmitButton.vue';
    import BaseSwitch from '@/Components/UI/BaseSwitch.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useFlash } from '@/composables/useFlash';
    import { AdminLandingBlock, ResourceSingle } from '@/types';

    defineOptions({ layout: AdminLayout });

    const props = defineProps<{
        block: ResourceSingle<AdminLandingBlock>;
    }>();

    const generateId = () => {
        return typeof window !== 'undefined' ? crypto.randomUUID() : Math.random().toString();
    };

    const form = useForm({
        title: props.block.data.title,
        subtitle: props.block.data.subtitle,
        is_visible: props.block.data.is_visible,
        content: JSON.parse(JSON.stringify(props.block.data.content)).map((item: any) => ({
            ...item,
            id: item.id || generateId(),
        })),
    });

    const { notifyWithUndo } = useFlash();

    const deletingIds = ref(new Set<number>());

    const addContentItem = () => {
        form.content.push({
            id: crypto.randomUUID(),
            title: '',
            desc: '',
            icon: 'Circle',
            step: form.content.length + 1,
        });
    };

    const removeContentItem = async (index: number) => {
        if (form.content.length <= 1) return;
        if (deletingIds.value.has(index)) return;
        deletingIds.value.add(index);

        const isDeleted = await notifyWithUndo('Удаление карточки #' + (index + 1), 3000);

        if (isDeleted) {
            form.content.splice(index, 1);
        }

        deletingIds.value.delete(index);
    };

    const moveItem = (index: number, direction: 'up' | 'down') => {
        const target = direction === 'up' ? index - 1 : index + 1;

        if (target >= 0 && target < form.content.length) {
            const [movedItem] = form.content.splice(index, 1);
            form.content.splice(target, 0, movedItem);
        }
    };

    const submit = () => {
        form.patch(route('admin.features.update', Number(props.block.data.id)), {
            preserveScroll: true,
        });
    };
</script>

<template>
    <Teleport to="#admin-header-content">
        <AdminPageHeader title="Редактирование блока " :subtitle="block.data.label" />
    </Teleport>

    <div class="mb-4 flex items-center justify-between gap-8">
        <BaseCancelButton :route-name="'admin.features.index'" label="Назад" />

        <p class="text-[12px] text-slate-400">*Здесь можно использовать html разметку</p>
    </div>

    <form @submit.prevent="submit" class="max-w-5xl space-y-8 pb-20">
        <section class="space-y-6 rounded-[2.5rem] border border-slate-800 bg-slate-900/40 p-8">
            <div class="mb-4 flex items-center gap-2 text-orange-500">
                <LayoutIcon class="h-5 w-5" />
                <h2 class="text-xs font-black uppercase tracking-widest">Общие параметры</h2>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <BaseInput
                    v-model="form.title"
                    v-model:error="form.errors.title"
                    label="Заголовок"
                    placeholder="Напишите заголовок..."
                    :disabled="form.processing"
                />

                <BaseInput
                    v-model="form.subtitle"
                    v-model:error="form.errors.subtitle"
                    label="Подзаголовок"
                    placeholder="Напишите подзаголовок, если он предусмотрен..."
                    :disabled="form.processing"
                />
            </div>

            <BaseSwitch
                v-model="form.is_visible"
                label="Отображать этот блок на сайте"
                active-text="Будет виден на сайте"
                inactive-text="В черновик"
                :disabled="form.processing"
            />
        </section>

        <section class="space-y-6">
            <div class="flex items-center justify-between px-4">
                <h2 class="text-xs font-black uppercase tracking-widest text-orange-500">
                    Состав контента
                </h2>
                <button
                    type="button"
                    @click="addContentItem"
                    class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-emerald-500 transition-all hover:text-emerald-400"
                >
                    <PlusIcon class="h-4 w-4" /> Добавить элемент
                </button>
            </div>

            <div class="grid gap-4" v-if="form.content.length" key="cards">
                <TransitionGroup name="list">
                    <ContentItemCard
                        v-for="(item, index) in form.content"
                        :key="item.id"
                        :item="item"
                        :index="Number(index)"
                        :is-first="index === 0"
                        :is-last="index === form.content.length - 1"
                        :is-final="form.content.length <= 1"
                        :disabled="deletingIds.has(Number(index))"
                        :errors="form.errors.content"
                        @move="moveItem"
                        @remove="removeContentItem"
                    />
                </TransitionGroup>
            </div>
        </section>

        <div class="sticky bottom-8 flex justify-end gap-4 rounded-[1rem] bg-slate-900/80">
            <BaseCancelButton :route-name="'admin.features.index'" />

            <BaseSubmitButton
                :processing="form.processing"
                label="Сохранить изменения"
                :icon="SaveIcon"
            />
        </div>
    </form>
</template>

<style scoped>
    .list-move,
    .list-enter-active,
    .list-leave-active {
        transition: all 0.5s ease;
    }
    .list-enter-from,
    .list-leave-to {
        opacity: 0;
        transform: translateY(20px);
    }
</style>
