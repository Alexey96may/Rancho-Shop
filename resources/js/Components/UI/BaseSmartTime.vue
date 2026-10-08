<script setup lang="ts">
    import { computed, ref } from 'vue';

    import { formatDateTime, formatRelativeTime } from '@/utils/format';

    const props = defineProps({
        date: {
            type: String as () => string | null | undefined,
            required: true,
            default: null,
        },
    });

    const showExactDate = ref(false);

    const toggleDate = () => {
        showExactDate.value = !showExactDate.value;
    };

    const computedDateTime = computed(() => formatDateTime(props.date));
    const computedRelativeTime = computed(() => formatRelativeTime(props.date));
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
            class="cursor-pointer select-none text-[10px] font-medium text-slate-500 transition-colors hover:text-slate-300"
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
</style>
