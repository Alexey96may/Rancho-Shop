<script setup lang="ts">
    import { computed } from 'vue';

    import type { Errors } from '@inertiajs/core';

    import { PlusIcon } from '@heroicons/vue/24/outline';

    import AdminDeleteButton from '@/Components/Admin/UI/AdminDeleteButton.vue';
    import type { Attribute } from '@/types';

    const props = defineProps<{
        attributes: Attribute[];
        errors?: Errors;
    }>();

    const emit = defineEmits<{
        (e: 'update:attributes', attributes: Attribute[]): void;
    }>();

    const uid = () => `attr_${Date.now()}_${Math.random().toString(36).slice(2, 9)}`;

    const addAttribute = () => {
        emit('update:attributes', [
            ...props.attributes,
            {
                id: uid(),
                label: '',
                value: '',
            },
        ]);
    };

    const removeAttribute = (index: number) => {
        const next = [...props.attributes];
        next.splice(index, 1);
        emit('update:attributes', next);
    };

    const updateField = (index: number, field: keyof Attribute, value: string) => {
        const next = [...props.attributes];
        next[index] = { ...next[index], [field]: value };
        emit('update:attributes', next);
    };

    const errorMessage = computed(() => {
        if (!props.errors) return null;

        return Object.values(props.errors)[0];
    });
</script>

<template>
    <div class="space-y-8 sm:space-y-6">
        <!-- HEADER -->
        <div class="flex items-center justify-between gap-4">
            <h3
                class="text-[10px] font-black uppercase tracking-[0.15em] text-rancho-ink-400 sm:text-xs"
            >
                Характеристики
            </h3>

            <button
                type="button"
                class="inline-flex items-center gap-2 rounded-xl border border-rancho-ink-700 bg-rancho-ink-800 px-3 py-2 text-[10px] font-black uppercase tracking-widest text-rancho-mint transition-all hover:border-rancho-mint/40 hover:bg-rancho-ink-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-rancho-mint/40 active:scale-95 sm:px-4"
                @click="addAttribute"
            >
                <PlusIcon class="h-3.5 w-3.5" aria-hidden="true" />
                <span class="hidden sm:inline">Добавить</span>
            </button>
        </div>

        <!-- EMPTY / LIST -->
        <Transition name="feature-swap" mode="out-in">
            <div
                v-if="!attributes.length"
                key="empty"
                class="rounded-2xl border border-dashed border-rancho-ink-700 bg-rancho-ink-900/20 px-4 py-8 text-center"
            >
                <p class="text-xs text-rancho-ink-400 sm:text-sm">
                    Пока нет характеристик. Добавьте первую — например, «Вес» или «Жирность».
                </p>
            </div>

            <TransitionGroup
                v-else
                key="list"
                name="feature"
                tag="div"
                class="relative grid grid-cols-1 gap-4 sm:gap-3"
            >
                <div
                    v-for="(attr, index) in attributes"
                    :key="attr.id"
                    class="group flex flex-col gap-3 rounded-2xl border border-rancho-ink-800 bg-rancho-ink-950 p-4 transition-colors focus-within:border-rancho-mint/40 sm:flex-row sm:items-center sm:gap-4 sm:p-2 sm:pl-4"
                >
                    <input
                        :value="attr.label"
                        type="text"
                        placeholder="Название"
                        class="w-full rounded-lg border-none bg-rancho-ink-900 px-2 py-2 text-xs text-rancho-mint placeholder:text-rancho-ink-600 focus:outline-none focus:ring-0 sm:w-1/3 sm:bg-transparent sm:text-sm"
                        aria-label="Название характеристики"
                        @input="
                            updateField(index, 'label', ($event.target as HTMLInputElement).value)
                        "
                    />

                    <div class="hidden h-6 w-px bg-rancho-ink-800 sm:block" aria-hidden="true" />

                    <input
                        :value="attr.value"
                        type="text"
                        placeholder="Значение"
                        class="w-full flex-1 rounded-lg border-none bg-rancho-ink-900 px-2 py-2 text-xs leading-3 text-white placeholder:text-rancho-ink-600 focus:outline-none focus:ring-0 sm:bg-transparent sm:text-sm"
                        aria-label="Значение характеристики"
                        @input="
                            updateField(index, 'value', ($event.target as HTMLInputElement).value)
                        "
                    />

                    <AdminDeleteButton
                        class="self-end opacity-50 transition-opacity focus-visible:opacity-100 group-hover:opacity-100 sm:self-auto"
                        title="Удалить характеристику"
                        @click="removeAttribute(index)"
                    />
                </div>
            </TransitionGroup>
        </Transition>

        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="transform -translate-y-2 opacity-0"
            enter-to-class="transform translate-y-0 opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <p v-if="errorMessage" class="text-xs font-medium text-rancho-ember" role="alert">
                Ошибка: {{ errorMessage }}
            </p>
        </Transition>
    </div>
</template>

<style scoped>
    /* ... стили как были ... */
</style>

<style scoped>
    /* Смена empty ↔ list */
    .feature-swap-enter-active,
    .feature-swap-leave-active {
        transition:
            opacity 0.2s ease,
            transform 0.2s ease;
    }

    .feature-swap-enter-from {
        opacity: 0;
        transform: translateY(4px);
    }

    .feature-swap-leave-to {
        opacity: 0;
        transform: translateY(-4px);
    }

    /* Строки списка */
    .feature-enter-active,
    .feature-leave-active {
        transition:
            opacity 0.25s ease,
            transform 0.25s ease;
    }

    .feature-enter-from {
        opacity: 0;
        transform: translateY(-8px) scale(0.98);
    }

    .feature-leave-to {
        opacity: 0;
        transform: translateY(-8px) scale(0.98);
    }

    .feature-leave-active {
        position: absolute;
        width: 100%;
    }

    .feature-move {
        transition: transform 0.3s ease;
    }

    @media (prefers-reduced-motion: reduce) {
        .feature-swap-enter-active,
        .feature-swap-leave-active,
        .feature-enter-active,
        .feature-leave-active,
        .feature-move {
            transition: none;
        }
    }
</style>
