<script setup lang="ts">
    import { ref } from 'vue';

    import { Head, router, useForm } from '@inertiajs/vue3';

    // Импорт библиотеки
    import {
        AlertCircleIcon,
        GripVerticalIcon,
        Loader2Icon,
        PlusIcon,
        RulerIcon,
        Trash2Icon,
    } from 'lucide-vue-next';
    // Добавили router
    import draggable from 'vuedraggable';

    import AdminLayout from '@/Layouts/AdminLayout.vue';

    const props = defineProps<{
        units: { data: any[] };
    }>();

    const isDragging = ref(false);
    const isSavingOrder = ref(false);

    const form = useForm({
        name: '',
        short: '',
        position: 0,
    });

    // Сохранение новой единицы
    const submit = () => {
        form.post(route('admin.units.store'), {
            onSuccess: () => form.reset(),
        });
    };

    // Удаление
    const deleteUnit = (unit: any) => {
        if (confirm(`Вы уверены, что хотите удалить "${unit.name}"?`)) {
            useForm({}).delete(route('admin.units.destroy', unit.id));
        }
    };

    const onDragStart = () => {
        isDragging.value = true;

        // Вибрируем 10 миллисекунд (легкий щелчок)
        if ('vibrate' in navigator) {
            navigator.vibrate(10);
        }
    };

    // Сохранение нового порядка в БД
    const onDragEnd = () => {
        isDragging.value = false;
        isSavingOrder.value = true;

        // Собираем массив ID в текущем порядке (после перетаскивания)
        const ids = props.units.data.map((u) => u.id);

        router.patch(
            route('admin.units.reorder'),
            { ids },
            {
                preserveScroll: true,
                onSuccess: () => {
                    if ('vibrate' in navigator) {
                        navigator.vibrate([10, 30, 10]);
                    }
                },
                onFinish: () => (isSavingOrder.value = false),
            },
        );
    };
</script>

<template>
    <Head title="Номенклатура: Единицы измерения" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-500/10 text-orange-500"
                        aria-hidden="true"
                    >
                        <RulerIcon class="h-6 w-6" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-black uppercase tracking-tighter text-white">
                            Единицы измерения
                        </h1>
                        <p
                            v-if="isSavingOrder"
                            class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-orange-500"
                        >
                            <Loader2Icon class="h-3 w-3 animate-spin" /> Обновление порядка...
                        </p>
                    </div>
                </div>
            </div>
        </template>

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
            <section class="space-y-4 lg:col-span-2">
                <h2 class="sr-only">Список единиц измерения</h2>

                <draggable
                    v-model="units.data"
                    item-key="id"
                    handle=".drag-handle"
                    @start="isDragging = true"
                    @end="onDragEnd"
                    ghost-class="opacity-0"
                    drag-class="scale-105"
                    class="space-y-4"
                >
                    <template #item="{ element: unit }">
                        <div
                            role="listitem"
                            class="group flex items-center gap-4 rounded-3xl border border-slate-800 bg-slate-900/40 p-4 transition-all hover:border-slate-600 active:border-orange-500/50"
                        >
                            <div
                                class="drag-handle cursor-grab p-2 text-slate-700 transition-colors hover:text-slate-400 active:cursor-grabbing"
                                title="Перетащить для изменения порядка"
                            >
                                <GripVerticalIcon class="h-5 w-5" />
                            </div>

                            <div class="grid flex-1 grid-cols-2 gap-4">
                                <div class="flex flex-col">
                                    <span
                                        class="text-[10px] font-black uppercase tracking-widest text-slate-500"
                                        >Название</span
                                    >
                                    <span class="text-sm font-bold uppercase text-white">{{
                                        unit.name
                                    }}</span>
                                </div>
                                <div class="flex flex-col">
                                    <span
                                        class="text-[10px] font-black uppercase tracking-widest text-slate-500"
                                        >Сокращение</span
                                    >
                                    <span class="text-sm font-bold text-orange-500">{{
                                        unit.short
                                    }}</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <button
                                    @click="deleteUnit(unit)"
                                    class="rounded-xl p-3 text-slate-600 transition-all hover:bg-rose-500/10 hover:text-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 focus:ring-offset-slate-900"
                                    :aria-label="`Удалить ${unit.name}`"
                                >
                                    <Trash2Icon class="h-5 w-5" aria-hidden="true" />
                                </button>
                            </div>
                        </div>
                    </template>
                </draggable>

                <div
                    v-if="units.data.length === 0"
                    class="rounded-[2.5rem] border-2 border-dashed border-slate-800 p-12 text-center"
                >
                    <AlertCircleIcon
                        class="mx-auto mb-2 h-8 w-8 text-slate-700"
                        aria-hidden="true"
                    />
                    <p class="text-sm font-bold uppercase text-slate-500">Список пуст</p>
                </div>
            </section>

            <aside aria-labelledby="form-title">
                <div
                    class="shadow-2xl sticky top-6 rounded-[2.5rem] border border-slate-800 bg-slate-900/40 p-8"
                >
                    <h2
                        id="form-title"
                        class="mb-6 text-sm font-black uppercase tracking-[0.2em] text-white"
                    >
                        Новая единица
                    </h2>
                    <form @submit.prevent="submit" class="space-y-5">
                        <div class="space-y-2">
                            <label
                                for="unit-name"
                                class="ml-2 text-[10px] font-black uppercase tracking-widest text-slate-500"
                                >Полное название <span class="text-orange-500">*</span></label
                            >
                            <input
                                id="unit-name"
                                v-model="form.name"
                                type="text"
                                required
                                class="w-full rounded-2xl border-slate-800 bg-slate-950 px-4 py-3 text-sm text-white focus:border-orange-500 focus:ring-0"
                            />
                        </div>
                        <div class="space-y-2">
                            <label
                                for="unit-short"
                                class="ml-2 text-[10px] font-black uppercase tracking-widest text-slate-500"
                                >Сокращение <span class="text-orange-500">*</span></label
                            >
                            <input
                                id="unit-short"
                                v-model="form.short"
                                type="text"
                                required
                                class="w-full rounded-2xl border-slate-800 bg-slate-950 px-4 py-3 text-sm text-white focus:border-orange-500 focus:ring-0"
                            />
                        </div>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="group relative flex w-full items-center justify-center gap-2 rounded-2xl bg-orange-600 py-4 text-xs font-black uppercase tracking-[0.2em] text-white transition-all hover:bg-orange-500 disabled:opacity-50"
                        >
                            <PlusIcon v-if="!form.processing" class="h-4 w-4" />
                            {{ form.processing ? 'Сохранение...' : 'Добавить в список' }}
                        </button>
                    </form>
                </div>
            </aside>
        </div>
    </AdminLayout>
</template>
