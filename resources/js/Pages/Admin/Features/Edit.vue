<script setup lang="ts">
    import { Head, Link, useForm } from '@inertiajs/vue3';

    import {
        ChevronLeftIcon,
        LayoutIcon,
        MoveDownIcon,
        MoveUpIcon,
        PlusIcon,
        SaveIcon,
        Trash2Icon,
    } from 'lucide-vue-next';

    import AdminLayout from '@/Layouts/AdminLayout.vue';

    const props = defineProps<{
        block: {
            data: {
                id: number; // Явно указываем, что тут число
                key: string;
                title: string;
                subtitle: string | null;
                is_visible: boolean;
                content: any[];
            };
        };
    }>();

    const form = useForm({
        title: props.block.data.title,
        subtitle: props.block.data.subtitle,
        is_visible: props.block.data.is_visible,
        content: JSON.parse(JSON.stringify(props.block.data.content)),
    });

    const addContentItem = () => {
        form.content.push({ title: '', desc: '', icon: 'Circle', step: form.content.length + 1 });
    };

    const removeContentItem = (index: number) => {
        form.content.splice(index, 1);
    };

    const moveItem = (index: number, direction: 'up' | 'down') => {
        const target = direction === 'up' ? index - 1 : index + 1;
        if (target >= 0 && target < form.content.length) {
            const currentItem = form.content[index];
            const targetItem = form.content[target];

            form.content[index] = targetItem;
            form.content[target] = currentItem;
        }
    };

    const submit = () => {
        form.patch(route('admin.features.update', Number(props.block.data.id)), {
            preserveScroll: true,
            onSuccess: () => {
                // Можно добавить уведомление
            },
        });
    };
</script>

<template>
    <Head :title="`Редактирование: ${block.data.key}`" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link
                    :href="route('admin.features.index')"
                    class="rounded-xl bg-slate-800 p-2 text-slate-400 hover:text-white"
                >
                    <ChevronLeftIcon class="h-5 w-5" />
                </Link>
                <div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-orange-500"
                        >Настройка блока</span
                    >
                    <h1 class="text-2xl font-black uppercase tracking-tighter text-white">
                        {{ block.data.key }}
                    </h1>
                </div>
            </div>
        </template>

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

                <div class="grid gap-4">
                    <TransitionGroup name="list">
                        <div
                            v-for="(item, index) in form.content"
                            :key="index"
                            class="group relative rounded-[2rem] border border-slate-800 bg-slate-900/20 p-6 transition-all hover:border-slate-700"
                        >
                            <div
                                class="absolute -right-3 top-6 flex flex-col gap-2 opacity-0 transition-all group-hover:opacity-100"
                            >
                                <button
                                    type="button"
                                    @click="moveItem(Number(index), 'up')"
                                    class="shadow-xl rounded-lg bg-slate-800 p-2 text-slate-400 hover:text-white"
                                >
                                    <MoveUpIcon class="h-4 w-4" />
                                </button>
                                <button
                                    type="button"
                                    @click="moveItem(Number(index), 'down')"
                                    class="shadow-xl rounded-lg bg-slate-800 p-2 text-slate-400 hover:text-white"
                                >
                                    <MoveDownIcon class="h-4 w-4" />
                                </button>
                                <button
                                    type="button"
                                    @click="removeContentItem(Number(index))"
                                    class="shadow-xl rounded-lg bg-red-500/10 p-2 text-red-500/60 hover:text-red-500"
                                >
                                    <Trash2Icon class="h-4 w-4" />
                                </button>
                            </div>

                            <div class="grid gap-6 md:grid-cols-12">
                                <div
                                    class="flex flex-col items-center justify-center border-r border-slate-800/50 md:col-span-1"
                                >
                                    <span class="text-2xl font-black text-slate-800">{{
                                        Number(index) + 1
                                    }}</span>
                                </div>

                                <div class="grid gap-4 md:col-span-11 md:grid-cols-3">
                                    <div class="space-y-1">
                                        <label
                                            class="ml-1 text-[9px] font-black uppercase text-slate-600"
                                            >Иконка Lucide</label
                                        >
                                        <input
                                            v-model="item.icon"
                                            type="text"
                                            class="w-full rounded-xl border-slate-800 bg-slate-950 p-3 text-xs text-white"
                                        />
                                    </div>
                                    <div class="space-y-1">
                                        <label
                                            class="ml-1 text-[9px] font-black uppercase text-slate-600"
                                            >Заголовок</label
                                        >
                                        <input
                                            v-model="item.title"
                                            type="text"
                                            class="w-full rounded-xl border-slate-800 bg-slate-950 p-3 text-xs text-white"
                                        />
                                    </div>
                                    <div class="space-y-1">
                                        <label
                                            class="ml-1 text-[9px] font-black uppercase text-slate-600"
                                            >Шаг (если нужно)</label
                                        >
                                        <input
                                            v-model="item.step"
                                            type="number"
                                            class="w-full rounded-xl border-slate-800 bg-slate-950 p-3 text-xs text-white"
                                        />
                                    </div>
                                    <div class="space-y-1 md:col-span-3">
                                        <label
                                            class="ml-1 text-[9px] font-black uppercase text-slate-600"
                                            >Описание</label
                                        >
                                        <textarea
                                            v-model="item.desc"
                                            rows="2"
                                            class="w-full rounded-xl border-slate-800 bg-slate-950 p-3 text-xs leading-relaxed text-white"
                                        ></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
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
    </AdminLayout>
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
