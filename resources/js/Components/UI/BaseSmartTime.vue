<script setup lang="ts">
    import { computed, ref } from 'vue';

    import { formatDateTime, formatRelativeTime } from '@/utils/format';

    type Theme = 'light' | 'dark';

    interface Props {
        date: string | null | undefined;
        theme?: Theme;
    }

    const props = withDefaults(defineProps<Props>(), {
        theme: 'light',
    });

    const showExactDate = ref(false);

    const toggleDate = () => {
        showExactDate.value = !showExactDate.value;
    };

    const isDark = computed(() => props.theme === 'dark');

    const computedDateTime = computed(() => formatDateTime(props.date ?? null));
    const computedRelativeTime = computed(() => formatRelativeTime(props.date ?? null));

    const colorClasses = computed(() =>
        isDark.value
            ? 'text-rancho-ink-300 hover:text-rancho-buttercup'
            : 'text-rancho-ink-500 hover:text-rancho-forest',
    );
</script>

<template>
    <Transition name="fade-date" mode="out-in">
        <time
            v-if="props.date"
            :key="showExactDate.toString()"
            :datetime="props.date"
            :title="
                showExactDate
                    ? 'Нажмите, чтобы увидеть относительное время'
                    : 'Нажмите, чтобы увидеть точную дату'
            "
            @click="toggleDate"
            class="cursor-pointer select-none text-[11px] font-medium underline-offset-2 transition-colors hover:underline"
            :class="colorClasses"
        >
            {{ showExactDate ? computedDateTime : computedRelativeTime }}
        </time>
    </Transition>
</template>

<style scoped>
    .fade-date-enter-active,
    .fade-date-leave-active {
        transition:
            opacity 0.2s ease,
            transform 0.2s ease;
    }

    .fade-date-enter-from {
        opacity: 0;
        transform: translateY(2px);
    }

    .fade-date-leave-to {
        opacity: 0;
        transform: translateY(-2px);
    }

    @media (prefers-reduced-motion: reduce) {
        .fade-date-enter-active,
        .fade-date-leave-active {
            transition: none;
        }
    }
</style>
