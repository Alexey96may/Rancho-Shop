<script setup lang="ts">
    /* Basic Using Example

        import EmptyState from '@/Components/UI/EmptyState.vue';

        <EmptyState
            title="Товары не найдены"
            description="Попробуйте сбросить установленные фильтры категории или наличия."
            action-label="Сбросить фильтры"
            @action="resetFilters"
        />
    */
    import { computed, useSlots } from 'vue';

    interface Props {
        /** Message title */
        title?: string;
        /** Additional description */
        description?: string;
        /** Text for the primary action button */
        actionLabel?: string;
        /** Link for the primary button, if it is a link rather than a button */
        actionHref?: string;
        /** aria-live attribute for the container ('polite' | 'assertive' | 'off') */
        ariaLive?: 'polite' | 'assertive' | 'off';
    }

    const props = withDefaults(defineProps<Props>(), {
        title: 'Ничего не найдено',
        description:
            'По вашему запросу ничего не удалось найти. Попробуйте изменить параметры поиска или фильтры.',
        ariaLive: 'polite',
    });

    const emit = defineEmits<{
        (e: 'action'): void;
    }>();

    const slots = useSlots();
    const hasAction = computed(() => Boolean(props.actionLabel || slots.action));
</script>

<template>
    <div
        class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-gray-300 bg-gray-50/50 p-8 text-center dark:border-gray-700 dark:bg-gray-800/30"
        role="status"
        :aria-live="ariaLive"
        aria-atomic="true"
    >
        <!-- Слоты для сложной иконки / Иконка по умолчанию -->
        <div class="aria-hidden:true mb-4 text-gray-400 dark:text-gray-500" aria-hidden="true">
            <slot name="icon">
                <svg
                    class="stroke-1.5 h-16 w-16"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"
                    />
                </svg>
            </slot>
        </div>

        <!-- Заголовок -->
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            <slot name="title">{{ title }}</slot>
        </h3>

        <!-- Описание -->
        <p
            v-if="description || slots.description"
            class="mt-1.5 max-w-sm text-sm leading-relaxed text-gray-500 dark:text-gray-400"
        >
            <slot name="description">{{ description }}</slot>
        </p>

        <!-- Слот или стандартная кнопка действия -->
        <div v-if="hasAction" class="mt-6">
            <slot name="action">
                <a
                    v-if="actionHref"
                    :href="actionHref"
                    class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                >
                    {{ actionLabel }}
                </a>

                <button
                    v-else
                    type="button"
                    class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                    @click="emit('action')"
                >
                    {{ actionLabel }}
                </button>
            </slot>
        </div>
    </div>
</template>
