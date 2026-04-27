<script setup lang="ts">
    import { ref, watch } from 'vue';

    import { router, useForm } from '@inertiajs/vue3';

    import { PlusIcon } from '@heroicons/vue/24/outline';
    import debounce from 'lodash/debounce';
    import draggable from 'vuedraggable';

    import FaqItem from '@/Components/Admin/Cards/AdminFaqCard.vue';
    import AdminEmptyState from '@/Components/Admin/Shared/AdminEmptyState.vue';
    import AdminLoader from '@/Components/Admin/UI/AdminLoader.vue';
    import AdminNumberInput from '@/Components/Admin/UI/AdminNumberInput.vue';
    import AdminSearchInput from '@/Components/Admin/UI/AdminSearchInput.vue';
    import Modal from '@/Components/UI/BaseModal.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useFlash } from '@/composables/useFlash';
    import { AdminFaq, ResourceCollection } from '@/types';

    interface DraggableEvent {
        oldIndex: number;
        newIndex: number;
        item: HTMLElement;
        from: HTMLElement;
        to: HTMLElement;
    }

    defineOptions({ layout: AdminLayout });

    const props = defineProps<{
        faqs: ResourceCollection<AdminFaq>;
        filters: { search: string };
    }>();

    const openIds = ref(new Set<number>());
    const isModalOpen = ref(false);
    const editMode = ref(false);
    const currentId = ref<number | null>(null);

    const form = useForm({
        question: '',
        answer: '',
        sort_order: 0,
        is_published: true,
    });

    const search = ref(props.filters.search || '');
    const { notifyWithUndo } = useFlash();

    const isFiltering = ref(false);

    watch(search, (newValue) => {
        isFiltering.value = true;

        performSearch();
    });

    const performSearch = debounce(() => {
        router.get(
            route('admin.faq.index'),
            { search: search.value },
            {
                preserveState: true,
                replace: true,
                onFinish: () => {
                    isFiltering.value = false;
                },
            },
        );
    }, 300);

    const vibrate = () => {
        if (!window) return;

        if (window.navigator && window.navigator.vibrate) {
            window.navigator.vibrate(10);
        }
    };

    const toggleAccordion = (id: number) => {
        const newSet = new Set(openIds.value);

        if (newSet.has(id)) {
            newSet.delete(id);
        } else {
            newSet.add(id);
        }
        openIds.value = newSet;
    };

    const toggleStatus = debounce((id: number) => {
        router.patch(
            route('admin.faq.toggle', id),
            {},
            {
                preserveScroll: true,
                preserveState: true,
            },
        );
    }, 100);

    const deleteFaq = async (id: number) => {
        const isDeleted = await notifyWithUndo('Удаление вопроса');

        if (isDeleted) {
            router.delete(route('admin.faq.destroy', id), {
                preserveScroll: true,
            });
        }
    };

    const handleReorder = (e: DraggableEvent) => {
        if (e.oldIndex === e.newIndex) {
            return;
        }

        const droppedItem = e.item;
        droppedItem.classList.remove('drop-highlight');

        void droppedItem.offsetWidth;

        droppedItem.classList.add('drop-highlight');

        setTimeout(() => {
            droppedItem.classList.remove('drop-highlight');
        }, 2000);

        const ids = props.faqs.data.map((item) => item.id);

        router.patch(
            route('admin.faq.reorder'),
            { ids },
            {
                preserveScroll: true,
                preserveState: true,
            },
        );
    };

    const openModal = (faq: AdminFaq | null = null) => {
        form.clearErrors();
        editMode.value = !!faq;

        if (faq) {
            currentId.value = faq.id;
            form.question = faq.question;
            form.answer = faq.answer;
            form.sort_order = faq.sort_order;
            form.is_published = !!faq.is_published;
        } else {
            form.reset();

            form.sort_order =
                props.faqs.data.length > 0
                    ? Math.max(...props.faqs.data.map((f: AdminFaq) => f.sort_order)) + 1
                    : 1;
        }
        isModalOpen.value = true;
    };

    const submit = () => {
        const action = editMode.value
            ? route('admin.faq.update', currentId.value!)
            : route('admin.faq.store');

        const method = editMode.value ? 'put' : 'post';

        form[method](action, {
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            },
        });
    };

    const clearFilters = () => {
        search.value = '';
    };
</script>

<template>
    <Teleport to="#admin-header-content">
        <h1 class="flex items-center gap-2 text-xl font-black text-white">
            Модерация "Вопросы - Ответы"
        </h1>
    </Teleport>

    <div class="max-w-5xl space-y-6">
        <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
            <AdminSearchInput v-model="search" placeholder="Поиск вопроса..." />

            <button
                @click="openModal()"
                class="flex h-[54px] w-full items-center justify-center gap-2 rounded-2xl bg-orange-600 px-8 text-xs font-black uppercase tracking-widest text-white transition-all hover:bg-orange-500 sm:w-auto"
            >
                <PlusIcon class="h-5 w-5" /> Добавить вопрос
            </button>
        </div>

        <Transition name="fade-slide" mode="out-in">
            <draggable
                v-if="faqs.data.length"
                key="questions"
                :list="faqs.data"
                handle=".drag-handle"
                item-key="id"
                tag="div"
                :animation="300"
                fallback-tolerance="3"
                :force-fallback="true"
                @start="vibrate"
                @end="handleReorder"
                ghost-class="ghost-card"
                chosen-class="chosen-card"
                drag-class="drag-card"
                class="space-y-3"
            >
                <template #item="{ element: faq }">
                    <div :key="faq.id" class="sortable-item">
                        <FaqItem
                            :faq="faq"
                            :is-open="openIds.has(faq.id)"
                            @toggle="toggleAccordion"
                            @edit="openModal"
                            @delete="deleteFaq"
                            @toggle-status="toggleStatus"
                        />
                    </div>
                </template>
            </draggable>

            <AdminLoader v-else-if="isFiltering" key="loading" text="Синхронизация" />

            <AdminEmptyState
                v-else
                key="empty"
                :title="search ? 'Вопросы не найдены' : 'Список вопросов пуст'"
                @action="search ? clearFilters() : openModal()"
                :action-text="search ? 'Очистить фильтр' : 'Добавить вопрос'"
                :show-action="true"
                :description="
                    search ? 'По запросу «' + search + '» совпадений нет' : 'Нет ни одного вопроса'
                "
            />
        </Transition>
    </div>

    <Modal
        :show="isModalOpen"
        @close="isModalOpen = false"
        max-width="2xl"
        aria-labelledby="modal-title"
    >
        <div class="p-8">
            <h3
                id="modal-title"
                class="mb-8 text-xl font-black uppercase tracking-tighter text-white"
            >
                {{ editMode ? 'Редактировать вопрос' : 'Новый вопрос FAQ' }}
            </h3>

            <form @submit.prevent="submit" class="space-y-6">
                <div class="space-y-1.5">
                    <label
                        for="faq-question"
                        class="ml-2 text-[10px] font-black uppercase tracking-widest text-slate-500"
                    >
                        Текст вопроса
                    </label>
                    <textarea
                        id="faq-question"
                        v-model="form.question"
                        required
                        rows="2"
                        class="w-full rounded-2xl border-slate-800 bg-slate-950 p-4 text-white transition-all focus:border-orange-500 focus:ring-0"
                        placeholder="Например: Как активировать подписку?"
                    ></textarea>

                    <div v-if="form.errors.question" class="ml-2 text-xs text-red-500">
                        {{ form.errors.question }}
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label
                        for="faq-answer"
                        class="ml-2 text-[10px] font-black uppercase tracking-widest text-slate-500"
                    >
                        Ответ (поддерживает HTML)
                    </label>
                    <textarea
                        id="faq-answer"
                        v-model="form.answer"
                        required
                        rows="6"
                        class="w-full rounded-2xl border-slate-800 bg-slate-950 p-4 font-mono text-sm text-slate-300 transition-all focus:border-orange-500 focus:ring-0"
                        placeholder="<p>Для активации перейдите в...</p>"
                    ></textarea>

                    <div v-if="form.errors.answer" class="ml-2 text-xs text-red-500">
                        {{ form.errors.answer }}
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <AdminNumberInput
                        label="Порядок вывода"
                        v-model="form.sort_order"
                        :min="0"
                        :max="999"
                    />

                    <div class="flex items-end pb-4">
                        <label class="group flex cursor-pointer items-center gap-3">
                            <div class="relative flex items-center">
                                <input
                                    type="checkbox"
                                    v-model="form.is_published"
                                    class="peer h-6 w-6 rounded-lg border-slate-800 bg-slate-950 text-orange-600 transition-all focus:ring-0 focus:ring-offset-0"
                                />
                            </div>
                            <span
                                class="text-[10px] font-black uppercase tracking-widest text-slate-400 transition-colors group-hover:text-slate-200"
                            >
                                Опубликовать на сайте
                            </span>
                        </label>
                    </div>
                </div>

                <div class="flex flex-col gap-4 pt-4 sm:flex-row">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="flex-1 rounded-2xl bg-orange-600 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-white transition-all hover:bg-orange-500 disabled:opacity-50"
                    >
                        <span v-if="form.processing">Сохранение...</span>
                        <span v-else>{{ editMode ? 'Обновить данные' : 'Создать вопрос' }}</span>
                    </button>

                    <button
                        @click="isModalOpen = false"
                        type="button"
                        class="flex-1 rounded-2xl bg-slate-800 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 transition-all hover:bg-slate-700 hover:text-white"
                    >
                        Отмена
                    </button>
                </div>
            </form>
        </div>
    </Modal>
</template>

<style scoped>
    .sortable-item {
        user-select: none;
        -webkit-user-select: none;
        transition: opacity 0.2s ease;
    }

    .faq-answer-content {
        user-select: text;
    }

    .chosen-card {
        opacity: 0.4;
        background-color: rgba(249, 115, 22, 0.05);
    }

    .drag-card {
        opacity: 1 !important;
        transform: scale(1.02);
        cursor: grabbing;
        box-shadow:
            0 20px 25px -5px rgb(0 0 0 / 0.5),
            0 8px 10px -6px rgb(0 0 0 / 0.5);
        z-index: 9999;
        transition: none !important;
    }

    .ghost-card {
        background-color: rgba(249, 115, 22, 0.1) !important;
        border: 1px dashed rgb(92, 92, 92) !important;
        border-radius: 1.5rem;
        opacity: 0.4 !important;
    }

    .drag-handle {
        touch-action: none;
        cursor: grab;
    }

    .drop-highlight {
        animation: pulse-border 2s cubic-bezier(0.4, 0, 0.6, 1);

        border-radius: 1.5rem;
        position: relative;
        z-index: 10;
    }

    @keyframes pulse-border {
        0% {
            outline: 1px solid #22c55e; /* green-500 */
            box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.5);
            transform: scale(1.005);
        }
        30% {
            outline: 1px solid #ffffff;
            box-shadow: 0 0 0 10px rgba(255, 255, 255, 0);
            transform: scale(1);
        }
        100% {
            outline: 1px solid rgba(255, 255, 255, 0);
            box-shadow: 0 0 0 0 rgba(255, 255, 255, 0);
        }
    }

    .fade-slide-enter-active {
        transition: all 0.4s ease-out;
    }

    .fade-slide-enter-from {
        opacity: 0;
        transform: translateY(-10px);
    }
</style>
