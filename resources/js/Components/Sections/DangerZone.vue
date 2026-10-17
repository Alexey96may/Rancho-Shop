<script setup lang="ts">
    import { ExclamationTriangleIcon } from '@heroicons/vue/24/outline';

    import AdminDeleteButton from '@/Components/Admin/UI/AdminDeleteButton.vue';

    interface Props {
        /** Заголовок (по умолчанию «Опасная зона») */
        title?: string;
        /** Описание, что произойдёт при удалении */
        description?: string;
        /** Показывать ли блок (если false — не рендерится) */
        show?: boolean;
        /** Кнопка удаления disabled */
        disabled?: boolean;
        /** Текст кнопки / title */
        buttonTitle?: string;
        /** Текст в слоте кнопки (по умолчанию «Удалить») */
        buttonLabel?: string;
    }

    const props = withDefaults(defineProps<Props>(), {
        title: 'Опасная зона',
        description: 'Это действие нельзя отменить.',
        show: true,
        disabled: false,
        buttonTitle: 'Удалить',
        buttonLabel: 'Удалить',
    });

    const emit = defineEmits<{
        (e: 'confirm'): void;
    }>();

    const handleConfirm = () => {
        if (!props.disabled) {
            emit('confirm');
        }
    };
</script>

<template>
    <section
        v-if="show"
        class="flex flex-col items-start gap-4 rounded-2xl border border-rose-500/30 bg-rose-500/5 p-3 sm:flex-row sm:items-center sm:justify-between sm:gap-4 sm:rounded-3xl sm:p-4 sm:p-6"
        aria-label="Опасная зона"
    >
        <div class="flex items-center gap-3 sm:gap-4">
            <ExclamationTriangleIcon
                class="h-3 w-3 shrink-0 text-rose-400 sm:h-5 sm:w-5"
                aria-hidden="true"
            />

            <div>
                <h3
                    class="text-[10px] font-black uppercase tracking-widest text-rose-400 md:text-xs"
                >
                    {{ title }}
                </h3>

                <p
                    v-if="description"
                    class="mt-1 text-[10px] leading-tight text-rose-200/70 md:mt-2 md:text-xs"
                >
                    {{ description }}
                </p>

                <slot name="description" />
            </div>
        </div>

        <slot name="action">
            <AdminDeleteButton
                class="self-end sm:self-auto"
                @click="handleConfirm"
                :title="buttonTitle"
                :disabled="disabled"
            >
                {{ buttonLabel }}
            </AdminDeleteButton>
        </slot>
    </section>
</template>
