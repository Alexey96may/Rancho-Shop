<script setup lang="ts">
    import { Link, useForm } from '@inertiajs/vue3';

    import { ChevronLeftIcon, LayoutIcon, PlusIcon, SaveIcon } from 'lucide-vue-next';

    import ContentItemCard from '@/Components/Admin/Cards/AdminContentItemCard.vue';
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
        const isDeleted = await notifyWithUndo('Удаление карточки #' + (index + 1), 2000);

        if (isDeleted) {
            form.content.splice(index, 1);
        }
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
        <h1 class="flex items-center gap-2 text-xl font-black text-white">
            Редактирование блока "{{ block.data.label }}"
        </h1>
    </Teleport>

    <div class="mb-4 flex items-center justify-between gap-8">
        <Link
            :href="route('admin.features.index')"
            class="block rounded-xl bg-slate-800 p-2 text-slate-400 hover:text-white"
        >
            <ChevronLeftIcon class="h-5 w-5" />
        </Link>
        <p class="text-[12px] text-slate-400">*Здесь можно использовать html разметку</p>
    </div>

    <form @submit.prevent="submit" class="max-w-5xl space-y-8 pb-20">
        <section class="space-y-6 rounded-[2.5rem] border border-slate-800 bg-slate-900/40 p-8">
            <div class="mb-4 flex items-center gap-2 text-orange-500">
                <LayoutIcon class="h-5 w-5" />
                <h2 class="text-xs font-black uppercase tracking-widest">Общие параметры</h2>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <div class="space-y-1">
                    <label class="ml-2 text-[10px] font-black uppercase text-slate-500"
                        >Заголовок</label
                    >
                    <input
                        v-model="form.title"
                        type="text"
                        class="w-full rounded-2xl border-slate-800 bg-slate-950 p-4 text-white focus:border-orange-500"
                    />
                </div>
                <div class="space-y-1">
                    <label class="ml-2 text-[10px] font-black uppercase text-slate-500"
                        >Подзаголовок</label
                    >
                    <input
                        v-model="form.subtitle"
                        type="text"
                        class="w-full rounded-2xl border-slate-800 bg-slate-950 p-4 text-white focus:border-orange-500"
                    />
                </div>
            </div>

            <label class="flex cursor-pointer items-center gap-3">
                <input
                    type="checkbox"
                    v-model="form.is_visible"
                    class="h-5 w-5 rounded border-slate-800 bg-slate-950 text-orange-500 focus:ring-0"
                />
                <span class="text-xs font-bold uppercase tracking-widest text-slate-400"
                    >Отображать этот блок на сайте</span
                >
            </label>
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

            <div class="v grid gap-4" v-if="form.content.length" key="cards">
                <TransitionGroup name="list">
                    <ContentItemCard
                        v-for="(item, index) in form.content"
                        :key="item.id"
                        :item="item"
                        :index="Number(index)"
                        :is-first="index === 0"
                        :is-last="index === form.content.length - 1"
                        :is-final="form.content.length <= 1"
                        @move="moveItem"
                        @remove="removeContentItem"
                    />
                </TransitionGroup>
            </div>
        </section>

        <div class="sticky bottom-8 flex justify-end gap-4">
            <Link
                :href="route('admin.features.index')"
                class="rounded-2xl bg-slate-800 px-8 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 transition-all hover:text-white"
            >
                Отмена
            </Link>
            <button
                type="submit"
                :disabled="form.processing"
                class="shadow-2xl flex items-center gap-2 rounded-2xl bg-orange-600 px-10 py-4 text-[10px] font-black uppercase tracking-widest text-white shadow-orange-900/40 transition-all hover:bg-orange-500"
            >
                <SaveIcon class="h-4 w-4" /> Сохранить изменения
            </button>
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
