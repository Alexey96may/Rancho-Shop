<script setup lang="ts">
    import { type PropType, ref } from 'vue';

    import { useForm } from '@inertiajs/vue3';

    import { LayoutIcon, PlusIcon, SaveIcon } from 'lucide-vue-next';

    import ContentItemCard from '@/Components/Admin/Cards/AdminContentItemCard.vue';
    import AdminPageHeader from '@/Components/Admin/Shared/AdminPageHeader.vue';
    import BaseCancelButton from '@/Components/UI/BaseCancelButton.vue';
    import BaseInput from '@/Components/UI/BaseInput.vue';
    import BaseSubmitButton from '@/Components/UI/BaseSubmitButton.vue';
    import BaseSwitch from '@/Components/UI/BaseSwitch.vue';
    import AdminLayout from '@/Layouts/AdminLayout.vue';
    import { useAdminForm } from '@/composables/crud/useAdminForm';
    import { useNotificationsStore } from '@/stores/notifications';
    import type { AdminLandingBlock, LandingContentItem, ResourceSingle } from '@/types';

    defineOptions({ layout: AdminLayout });

    const props = defineProps({
        block: {
            type: Object as PropType<ResourceSingle<AdminLandingBlock>>,
            required: true,
            validator: (value: unknown): boolean => {
                const val = value as Record<string, unknown>;
                const hasData = val?.data && typeof val.data === 'object' && val.data !== null;

                const dataObj = val?.data as Record<string, unknown>;
                const hasRequiredFields = !!(
                    hasData &&
                    'title' in dataObj &&
                    'subtitle' in dataObj &&
                    'is_visible' in dataObj &&
                    Array.isArray(dataObj?.content)
                );

                if (!hasRequiredFields) {
                    console.warn(
                        'Runtime Error: The "block" prop is missing a valid API resource wrapper ' +
                            'or the required fields (title, subtitle, is_visible, content array) are absent.',
                    );
                }

                return hasRequiredFields;
            },
        },
    });

    function uuid() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
            const r = (Math.random() * 16) | 0;
            const v = c === 'x' ? r : (r & 0x3) | 0x8;
            return v.toString(16);
        });
    }

    const generateId = (): string => {
        if (typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function') {
            return crypto.randomUUID();
        }
        return uuid();
    };

    const form = useForm({
        title: props.block.data.title,
        subtitle: props.block.data.subtitle,
        is_visible: props.block.data.is_visible,
        content: JSON.parse(JSON.stringify(props.block.data.content)).map(
            (item: LandingContentItem) => ({
                ...item,
                id: item.id || generateId(),
            }),
        ),
    });

    const notify = useNotificationsStore();

    const deletingIds = ref(new Set<number>());

    const addContentItem = () => {
        form.content.push({
            id: generateId(),
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

        notify.withUndo(
            'Удаление карточки #' + (index + 1),
            () => {
                deletingIds.value.delete(index);
            },
            () => {
                form.content.splice(index, 1);
            },
        );
    };

    const moveItem = (index: number, direction: 'up' | 'down') => {
        const target = direction === 'up' ? index - 1 : index + 1;

        if (target >= 0 && target < form.content.length) {
            const [movedItem] = form.content.splice(index, 1);
            form.content.splice(target, 0, movedItem);
        }
    };

    const { submitForm } = useAdminForm();

    const submit = () => {
        submitForm(form, 'admin.features', props.block.data.id);
    };
</script>

<template>
    <Teleport to="#admin-header-content">
        <AdminPageHeader title="Редактирование блока" :subtitle="block.data.label" />
    </Teleport>

    <div class="space-y-10">
        <!-- TOP BAR -->
        <div class="flex flex-wrap items-center justify-between gap-4 sm:gap-8">
            <BaseCancelButton :route-name="'admin.features.index'" label="Назад" theme="dark" />

            <p class="text-rancho-ink-400 text-[11px] italic sm:text-xs">
                *Здесь можно использовать HTML-разметку
            </p>
        </div>

        <!-- FORM -->
        <form @submit.prevent="submit" class="max-w-5xl space-y-12 pb-20 sm:space-y-8">
            <!-- ОБЩИЕ ПАРАМЕТРЫ -->
            <section
                class="border-rancho-ink-800 bg-rancho-ink-900/40 space-y-6 rounded-2xl border px-4 py-6 sm:rounded-3xl sm:p-6 md:p-8"
            >
                <div class="text-rancho-mint mb-4 flex items-center gap-2">
                    <LayoutIcon class="h-5 w-5 sm:h-3 sm:w-3" aria-hidden="true" />
                    <h2 class="text-xs font-black">Общие параметры</h2>
                </div>

                <div class="grid gap-4 sm:gap-6 md:grid-cols-2">
                    <BaseInput
                        v-model="form.title"
                        v-model:error="form.errors.title"
                        label="Заголовок"
                        placeholder="Напишите заголовок..."
                        :disabled="form.processing"
                        theme="dark"
                    />

                    <BaseInput
                        v-model="form.subtitle"
                        v-model:error="form.errors.subtitle"
                        label="Подзаголовок"
                        placeholder="Напишите подзаголовок, если он предусмотрен..."
                        :disabled="form.processing"
                        theme="dark"
                    />
                </div>

                <BaseSwitch
                    v-model="form.is_visible"
                    label="Отображать на сайте"
                    active-text="Будет виден на сайте"
                    inactive-text="В черновик"
                    :disabled="form.processing"
                    theme="dark"
                />
            </section>

            <!-- СОСТАВ КОНТЕНТА -->
            <section class="space-y-6">
                <div
                    class="flex flex-col items-center justify-between gap-4 px-2 sm:flex-row sm:items-center sm:px-4"
                >
                    <h2 class="text-rancho-mint text-xs font-black uppercase tracking-widest">
                        Состав контента
                    </h2>

                    <button
                        type="button"
                        @click="addContentItem"
                        class="border-rancho-ink-700 bg-rancho-ink-800 text-rancho-mint hover:border-rancho-mint/40 hover:bg-rancho-ink-700 focus-visible:ring-rancho-mint/40 inline-flex items-center gap-2 rounded-xl border px-3 py-2 text-[10px] font-black transition-all focus:outline-none focus-visible:ring-2 active:scale-95 sm:px-4"
                    >
                        <PlusIcon class="h-4 w-4" aria-hidden="true" />
                        Добавить элемент
                    </button>
                </div>

                <div v-if="form.content.length" key="cards" class="grid gap-4">
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

            <!-- STICKY ACTIONS -->
            <div
                class="border-rancho-ink-800 bg-rancho-ink-900/95 sticky bottom-2 flex justify-end gap-3 rounded-2xl border p-3 backdrop-blur-md sm:bottom-4 sm:gap-4 sm:p-4"
            >
                <BaseCancelButton :route-name="'admin.features.index'" theme="dark" />

                <BaseSubmitButton
                    :processing="form.processing"
                    label="Сохранить"
                    :icon="SaveIcon"
                />
            </div>
        </form>
    </div>
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

    @media (prefers-reduced-motion: reduce) {
        .list-move,
        .list-enter-active,
        .list-leave-active {
            transition: none;
        }
    }
</style>
