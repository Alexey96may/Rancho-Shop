<script setup lang="ts">
    /* Basic Using Example

        import EmptyState from '@/Components/UI/EmptyState.vue';

        <EmptyState
            title="Товары не найдены"
            description="Попробуйте сбросить установленные фильтры категории или наличия."
            action-label="Сбросить фильтры"
            @action="resetFilters"
        />

        <EmptyState theme="light" ... />
    */
    import { computed, useSlots } from 'vue';

    import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline';

    type Theme = 'light' | 'dark';

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
        /** Цветовая тема */
        theme?: Theme;
    }

    const props = withDefaults(defineProps<Props>(), {
        title: 'Ничего не найдено',
        description:
            'По вашему запросу ничего не удалось найти. Попробуйте изменить параметры поиска или фильтры.',
        ariaLive: 'polite',
        theme: 'light',
    });

    const emit = defineEmits<{
        (e: 'action'): void;
    }>();

    const slots = useSlots();
    const hasAction = computed(() => Boolean(props.actionLabel || slots.action));

    const isDark = computed(() => props.theme === 'dark');

    /* ===== Классы ===== */

    const wrapperClasses = computed(() => [
        'flex flex-col items-center justify-center rounded-2xl border border-dashed p-6 text-center sm:p-8 md:p-12',
        isDark.value
            ? 'border-rancho-ink-800 bg-rancho-ink-900/30'
            : 'border-rancho-ink-300 bg-rancho-ink-50/50',
    ]);

    const iconWrapperClasses = computed(() => [
        'mb-4 flex h-14 w-14 items-center justify-center rounded-2xl sm:h-16 sm:w-16',
        isDark.value
            ? 'bg-rancho-ink-800/60 text-rancho-ink-500'
            : 'bg-rancho-ink-100 text-rancho-ink-500',
    ]);

    const titleClasses = computed(() => [
        'text-base font-black tracking-tight sm:text-lg',
        isDark.value ? 'text-rancho-ink-100' : 'text-rancho-forest',
    ]);

    const descriptionClasses = computed(() => [
        'mt-2 max-w-sm text-xs leading-relaxed sm:text-sm',
        isDark.value ? 'text-rancho-ink-400' : 'text-rancho-ink-600',
    ]);

    const actionClasses = computed(() => [
        // Базовая
        'inline-flex items-center justify-center gap-2 rounded-xl px-5 py-2.5 text-[10px] font-black uppercase tracking-widest shadow-md transition-all duration-200 active:scale-95 sm:rounded-2xl sm:px-6 sm:py-3 sm:text-xs',
        // Тема
        isDark.value
            ? 'bg-rancho-buttercup text-rancho-ink-950 hover:bg-rancho-buttercup/90 focus:outline-none focus-visible:ring-2 focus-visible:ring-rancho-buttercup focus-visible:ring-offset-2 focus-visible:ring-offset-rancho-ink-950'
            : 'bg-rancho-pine text-white hover:bg-rancho-forest focus:outline-none focus-visible:ring-2 focus-visible:ring-rancho-pine focus-visible:ring-offset-2 focus-visible:ring-offset-rancho-paper',
    ]);
</script>

<template>
    <div :class="wrapperClasses" role="status" :aria-live="ariaLive" aria-atomic="true">
        <!-- Иконка -->
        <div :class="iconWrapperClasses" aria-hidden="true">
            <slot name="icon">
                <MagnifyingGlassIcon class="h-7 w-7 sm:h-8 sm:w-8" />
            </slot>
        </div>

        <!-- Заголовок -->
        <h3 :class="titleClasses">
            <slot name="title">{{ title }}</slot>
        </h3>

        <!-- Описание -->
        <p v-if="description || slots.description" :class="descriptionClasses">
            <slot name="description">{{ description }}</slot>
        </p>

        <!-- Действие -->
        <div v-if="hasAction" class="mt-6">
            <slot name="action">
                <!-- Ссылка -->
                <a v-if="actionHref" :href="actionHref" :class="actionClasses">
                    {{ actionLabel }}
                </a>

                <!-- Кнопка -->
                <button v-else type="button" :class="actionClasses" @click="emit('action')">
                    {{ actionLabel }}
                </button>
            </slot>
        </div>
    </div>
</template>
