<script setup lang="ts">
    import { ref, watch } from 'vue';

    import { router, useForm } from '@inertiajs/vue3';

    import debounce from 'lodash/debounce';
    import draggable from 'vuedraggable';

    import FaqItem from '@/Components/Admin/Cards/AdminFaqCard.vue';
    import AdminEmptyState from '@/Components/Admin/Shared/AdminEmptyState.vue';
    import AdminPageHeader from '@/Components/Admin/Shared/AdminPageHeader.vue';
    import AdminPagination from '@/Components/Admin/Shared/AdminPagination.vue';
    import AdminLoader from '@/Components/Admin/UI/AdminLoader.vue';
    import AdminNumberInput from '@/Components/Admin/UI/AdminNumberInput.vue';
    import AdminSearchInput from '@/Components/Admin/UI/AdminSearchInput.vue';
    import BaseCancelButton from '@/Components/UI/BaseCancelButton.vue';
    import BaseCreateButton from '@/Components/UI/BaseCreateButton.vue';
    import Modal from '@/Components/UI/BaseModal.vue';
    import BaseStatusToggle from '@/Components/UI/BaseStatusToggle.vue';
    import BaseSubmitButton from '@/Components/UI/BaseSubmitButton.vue';
    import BaseTextarea from '@/Components/UI/BaseTextarea.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useFlash } from '@/composables/useFlash';
    import { AdminFaq, Paginated } from '@/types';

    interface DraggableEvent {
        oldIndex: number;
        newIndex: number;
        item: HTMLElement;
        from: HTMLElement;
        to: HTMLElement;
    }

    defineOptions({ layout: AdminLayout });

    const props = defineProps<{
        faqs: Paginated<AdminFaq>;
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

    watch(search, () => {
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

    const deletingIds = ref(new Set<number>());

    const deleteFaq = async (id: number) => {
        if (deletingIds.value.has(id)) return;
        deletingIds.value.add(id);

        const isDeleted = await notifyWithUndo('Удаление вопроса');

        if (isDeleted) {
            router.delete(route('admin.faq.destroy', id), {
                preserveScroll: true,
                preserveState: true,
                onFinish: () => {
                    deletingIds.value.delete(id);
                },
            });
        } else {
            deletingIds.value.delete(id);
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
        <AdminPageHeader
            title="Вопросы - Ответы"
            subtitle="Управление вопросами и ответами на главной"
        />
    </Teleport>

    <div class="max-w-5xl space-y-6">
        <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
            <AdminSearchInput v-model="search" placeholder="Поиск вопроса..." />

            <BaseCreateButton @click="openModal()" label="Добавить вопрос" />
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
                            :is-deleting="deletingIds.has(faq.id)"
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

        <Transition name="fade-slide" mode="out-in">
            <AdminPagination v-show="!isFiltering" :links="faqs.meta.links" />
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
                <BaseTextarea
                    v-model="form.question"
                    v-model:error="form.errors.question"
                    label="Текст вопроса"
                    :disabled="form.processing"
                    placeholder="Например: Как активировать подписку?"
                    :max-height="150"
                />

                <BaseTextarea
                    v-model="form.answer"
                    v-model:error="form.errors.answer"
                    :disabled="form.processing"
                    label="Ответ (поддерживает HTML)"
                    placeholder="<p>Для активации перейдите в...</p>"
                    :max-height="150"
                />

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <AdminNumberInput
                        label="Порядок вывода"
                        v-model="form.sort_order"
                        :min="0"
                        :max="999"
                    />

                    <BaseStatusToggle
                        v-model="form.is_published"
                        label="Опубликовать на сайте"
                        :disabled="form.processing"
                    />
                </div>

                <div class="flex flex-col gap-4 pt-4 sm:flex-row">
                    <BaseCancelButton label="Отмена" @click="isModalOpen = false" />

                    <BaseSubmitButton
                        :processing="form.processing"
                        :is-edit="editMode"
                        :label="editMode ? 'Обновить' : 'Создать '"
                    />
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
