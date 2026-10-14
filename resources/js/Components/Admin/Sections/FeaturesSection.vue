<script setup lang="ts">
    import { PlusIcon } from '@heroicons/vue/24/outline';

    import AdminDeleteButton from '@/Components/Admin/UI/AdminDeleteButton.vue';

    const props = defineProps<{
        features: Record<string, string>;
    }>();

    const emit = defineEmits<{
        (e: 'update:features', features: Record<string, string>): void;
    }>();

    const addFeature = () => {
        const newFeatures = { ...props.features };
        const newKey = `Параметр ${Object.keys(newFeatures).length + 1}`;
        newFeatures[newKey] = '';
        emit('update:features', newFeatures);
    };

    const removeFeature = (key: string) => {
        const newFeatures = { ...props.features };
        delete newFeatures[key];
        emit('update:features', newFeatures);
    };

    const renameKey = (oldKey: string, newKey: string) => {
        if (oldKey === newKey || !newKey) return;
        const newFeatures = { ...props.features };
        const value = newFeatures[oldKey];
        delete newFeatures[oldKey];
        newFeatures[newKey] = value;
        emit('update:features', newFeatures);
    };
</script>

<template>
    <div class="space-y-8 sm:space-y-6">
        <!-- HEADER -->
        <div class="flex items-center justify-between gap-4">
            <h3
                class="text-[10px] font-black uppercase tracking-[0.15em] text-rancho-ink-400 sm:text-xs"
            >
                Доп. параметры
            </h3>

            <button
                type="button"
                @click="addFeature"
                class="inline-flex items-center gap-2 rounded-xl border border-rancho-ink-700 bg-rancho-ink-800 px-3 py-2 text-[10px] font-black uppercase tracking-widest text-rancho-mint transition-all hover:border-rancho-mint/40 hover:bg-rancho-ink-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-rancho-mint/40 active:scale-95 sm:px-4"
            >
                <PlusIcon class="h-3.5 w-3.5" aria-hidden="true" />
                <span class="hidden sm:inline">Добавить</span>
            </button>
        </div>

        <!-- EMPTY / LIST -->
        <Transition name="feature-swap" mode="out-in">
            <!-- EMPTY -->
            <div
                v-if="!Object.keys(features).length"
                key="empty"
                class="rounded-2xl border border-dashed border-rancho-ink-700 bg-rancho-ink-900/20 px-4 py-8 text-center"
            >
                <p class="text-xs text-rancho-ink-400 sm:text-sm">
                    Пока нет параметров. Добавьте первый — например, «Вес» или «Порода».
                </p>
            </div>

            <!-- LIST -->
            <TransitionGroup
                v-else
                key="list"
                name="feature"
                tag="div"
                class="relative grid grid-cols-1 gap-4 sm:gap-3"
            >
                <div
                    v-for="(value, key) in features"
                    :key="key"
                    class="group flex flex-col gap-3 rounded-2xl border border-rancho-ink-800 bg-rancho-ink-950 p-4 transition-colors focus-within:border-rancho-mint/40 sm:flex-row sm:items-center sm:gap-4 sm:p-2 sm:pl-4"
                >
                    <!-- Ключ -->
                    <input
                        type="text"
                        :value="key"
                        @change="
                            (e) => renameKey(String(key), (e.target as HTMLInputElement).value)
                        "
                        placeholder="Название"
                        class="w-full rounded-lg border-none bg-rancho-ink-700 px-2 py-2 text-xs font-black uppercase leading-3 tracking-wider text-rancho-mint placeholder:text-rancho-ink-600 focus:outline-none focus:ring-0 sm:w-1/3 sm:bg-transparent"
                        aria-label="Название параметра"
                    />

                    <!-- Разделитель -->
                    <div
                        class="hidden h-6 w-px bg-rancho-ink-800 sm:block"
                        aria-hidden="true"
                    ></div>

                    <!-- Значение -->
                    <input
                        v-model="features[key]"
                        type="text"
                        placeholder="Значение"
                        class="w-full flex-1 rounded-lg border-none bg-rancho-ink-700 px-2 py-2 text-xs leading-3 text-white placeholder:text-rancho-ink-600 focus:outline-none focus:ring-0 sm:bg-transparent sm:text-sm"
                        aria-label="Значение параметра"
                    />

                    <!-- Удаление -->
                    <AdminDeleteButton
                        @click="removeFeature(String(key))"
                        title="Удалить параметр"
                        class="self-end opacity-50 transition-opacity focus-visible:opacity-100 group-hover:opacity-100 sm:self-auto"
                    />
                </div>
            </TransitionGroup>
        </Transition>
    </div>
</template>

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
