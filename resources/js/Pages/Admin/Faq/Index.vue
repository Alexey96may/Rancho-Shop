<script setup lang="ts">
    import { ref, watch } from 'vue';

    import { Head, router, useForm } from '@inertiajs/vue3';

    import {
        Bars2Icon,
        ChevronDownIcon,
        EyeIcon,
        EyeSlashIcon,
        MagnifyingGlassIcon,
        PencilSquareIcon,
        PlusIcon,
        TrashIcon,
    } from '@heroicons/vue/24/outline';
    import debounce from 'lodash/debounce';

    import Modal from '@/Components/UI/BaseModal.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';

    const props = defineProps<{
        faqs: { data: any[] };
        filters: any;
    }>();

    const openId = ref<number | null>(null);
    const isModalOpen = ref(false);
    const editMode = ref(false);
    const currentId = ref<number | null>(null);

    const form = useForm({
        question: '',
        answer: '',
        sort_order: 0,
        is_published: true,
    });

    // Фильтрация
    const search = ref(props.filters.search || '');
    watch(
        search,
        debounce(() => {
            router.get(
                route('admin.faq.index'),
                { search: search.value },
                { preserveState: true, replace: true },
            );
        }, 300),
    );

    const toggleAccordion = (id: number) => {
        openId.value = openId.value === id ? null : id;
    };

    const openModal = (faq: any = null) => {
        editMode.value = !!faq;
        if (faq) {
            currentId.value = faq.id;
            form.question = faq.question;
            form.answer = faq.answer;
            form.sort_order = faq.sort_order;
            form.is_published = faq.is_published;
        } else {
            form.reset();
            form.sort_order = props.faqs.data.length + 1;
        }
        isModalOpen.value = true;
    };

    const submit = () => {
        const action = editMode.value
            ? route('admin.faq.update', currentId.value!)
            : route('admin.faq.store');

        form.post(action, {
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            },
        });
    };
</script>

<template>
    <Head title="FAQ - База знаний" />

    <AdminLayout>
        <template #header>
            <h1 class="text-2xl font-black uppercase tracking-tighter text-white">
                Вопросы и ответы
            </h1>
        </template>

        <div class="animate-in fade-in max-w-5xl space-y-6 duration-500">
            <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                <div class="relative w-full max-w-md">
                    <MagnifyingGlassIcon
                        class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-500"
                    />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Поиск вопроса..."
                        class="w-full rounded-2xl border-slate-800 bg-slate-900/50 py-3.5 pl-12 text-white transition-all focus:border-orange-500"
                    />
                </div>
                <button
                    @click="openModal()"
                    class="shadow-lg flex h-[54px] w-full items-center justify-center gap-2 rounded-2xl bg-orange-600 px-8 text-xs font-black uppercase tracking-widest text-white shadow-orange-900/20 transition-all hover:bg-orange-500 sm:w-auto"
                >
                    <PlusIcon class="h-5 w-5" /> Добавить вопрос
                </button>
            </div>

            <div class="space-y-3">
                <div
                    v-for="faq in faqs.data"
                    :key="faq.id"
                    class="overflow-hidden rounded-3xl border border-slate-800 bg-slate-900/30 transition-all hover:border-slate-700"
                    :class="{
                        'border-orange-500/50 bg-slate-900/60 ring-1 ring-orange-500/20':
                            openId === faq.id,
                    }"
                >
                    <div
                        @click="toggleAccordion(faq.id)"
                        class="flex cursor-pointer items-center justify-between p-5 sm:px-8"
                        role="button"
                        :aria-expanded="openId === faq.id"
                    >
                        <div class="flex items-center gap-4">
                            <div
                                class="hidden h-8 w-8 items-center justify-center rounded-lg bg-slate-800 text-[10px] font-black text-slate-500 sm:flex"
                            >
                                {{ faq.sort_order }}
                            </div>
                            <h3
                                class="font-bold text-slate-200 transition-colors group-hover:text-white"
                            >
                                {{ faq.question }}
                            </h3>
                        </div>

                        <div class="flex items-center gap-4">
                            <div v-if="!faq.is_published" class="hidden sm:block">
                                <span
                                    class="rounded-full border border-slate-700 bg-slate-800 px-3 py-1 text-[9px] font-black uppercase tracking-widest text-slate-500"
                                    >Черновик</span
                                >
                            </div>
                            <ChevronDownIcon
                                class="h-5 w-5 text-slate-500 transition-transform duration-300"
                                :class="{ 'rotate-180 text-orange-500': openId === faq.id }"
                            />
                        </div>
                    </div>

                    <Transition
                        enter-active-class="transition-[max-height,opacity] duration-300 ease-out"
                        enter-from-class="max-h-0 opacity-0"
                        enter-to-class="max-h-[1000px] opacity-100"
                        leave-active-class="transition-[max-height,opacity] duration-200 ease-in"
                        leave-from-class="max-h-[1000px] opacity-100"
                        leave-to-class="max-h-0 opacity-0"
                    >
                        <div v-if="openId === faq.id" class="border-t border-slate-800/50">
                            <div class="p-5 sm:px-8 sm:pb-8">
                                <div
                                    class="prose prose-invert prose-orange mb-6 max-w-none text-sm leading-relaxed text-slate-400"
                                    v-html="faq.answer"
                                ></div>

                                <div
                                    class="flex items-center justify-between gap-4 border-t border-slate-800/50 pt-6"
                                >
                                    <div
                                        class="text-[10px] font-black uppercase tracking-widest text-slate-600"
                                    >
                                        Обновлено: {{ faq.updated_at }}
                                    </div>
                                    <div class="flex gap-2">
                                        <button
                                            @click.stop="
                                                router.patch(route('admin.faq.toggle', faq.id))
                                            "
                                            class="rounded-xl bg-slate-800 p-2.5 text-slate-400 transition-all hover:text-white"
                                        >
                                            <component
                                                :is="faq.is_published ? EyeSlashIcon : EyeIcon"
                                                class="h-5 w-5"
                                            />
                                        </button>
                                        <button
                                            @click.stop="openModal(faq)"
                                            class="rounded-xl bg-slate-800 p-2.5 text-slate-400 transition-all hover:text-orange-500"
                                        >
                                            <PencilSquareIcon class="h-5 w-5" />
                                        </button>
                                        <button
                                            @click.stop="
                                                router.delete(route('admin.faq.destroy', faq.id))
                                            "
                                            class="rounded-xl bg-red-500/10 p-2.5 text-red-500/60 transition-all hover:text-red-500"
                                        >
                                            <TrashIcon class="h-5 w-5" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>
        </div>

        <Modal :show="isModalOpen" @close="isModalOpen = false" max-width="2xl">
            <div class="p-8">
                <h3 class="mb-8 text-xl font-black uppercase tracking-tighter text-white">
                    {{ editMode ? 'Редактировать вопрос' : 'Новый вопрос FAQ' }}
                </h3>

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="space-y-1">
                        <label
                            class="ml-2 text-[10px] font-black uppercase tracking-widest text-slate-500"
                            >Текст вопроса</label
                        >
                        <textarea
                            v-model="form.question"
                            required
                            rows="2"
                            class="w-full rounded-2xl border-slate-800 bg-slate-950 p-4 text-white focus:border-orange-500"
                            placeholder="Как заказать доставку сена?"
                        ></textarea>
                    </div>

                    <div class="space-y-1">
                        <label
                            class="ml-2 text-[10px] font-black uppercase tracking-widest text-slate-500"
                            >Ответ (HTML)</label
                        >
                        <textarea
                            v-model="form.answer"
                            required
                            rows="6"
                            class="w-full rounded-2xl border-slate-800 bg-slate-950 p-4 font-mono text-sm text-slate-300 focus:border-orange-500"
                            placeholder="<p>Вы можете оформить заказ через...</p>"
                        ></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div class="space-y-1">
                            <label
                                class="ml-2 text-[10px] font-black uppercase tracking-widest text-slate-500"
                                >Порядок сортировки</label
                            >
                            <input
                                v-model="form.sort_order"
                                type="number"
                                class="w-full rounded-2xl border-slate-800 bg-slate-950 p-4 text-white focus:border-orange-500"
                            />
                        </div>
                        <div class="flex items-end pb-4">
                            <label class="flex cursor-pointer items-center gap-3">
                                <input
                                    type="checkbox"
                                    v-model="form.is_published"
                                    class="h-5 w-5 rounded border-slate-800 bg-slate-950 text-orange-600 focus:ring-orange-500"
                                />
                                <span
                                    class="text-[10px] font-black uppercase tracking-widest text-slate-400"
                                    >Опубликовать</span
                                >
                            </label>
                        </div>
                    </div>

                    <div class="flex gap-4 pt-4">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="flex-1 rounded-2xl bg-orange-600 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-white hover:bg-orange-500"
                        >
                            {{ editMode ? 'Обновить' : 'Создать' }}
                        </button>
                        <button
                            @click="isModalOpen = false"
                            type="button"
                            class="flex-1 rounded-2xl bg-slate-800 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 hover:text-white"
                        >
                            Отмена
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </AdminLayout>
</template>

<style scoped>
    /* Кастомная анимация для аккордеона через Transition */
    .prose-invert :deep(p) {
        margin-bottom: 1rem;
    }
    .prose-invert :deep(ul) {
        list-style-type: disc;
        padding-left: 1.5rem;
        margin-bottom: 1rem;
    }
</style>
