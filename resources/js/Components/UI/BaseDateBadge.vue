<script setup lang="ts">
    import { computed, ref } from 'vue';

    import { CalendarIcon } from '@heroicons/vue/24/outline';

    import { formatDateTime, formatRelativeTime } from '@/utils/format';

    interface Props {
        date?: string | null;
        label?: string;
        eternalText?: string;
    }

    const props = withDefaults(defineProps<Props>(), {
        date: null,
        label: 'Срок действия',
        eternalText: 'Бессрочно',
    });

    const emit = defineEmits<{
        (e: 'click', isShort: boolean): void;
    }>();

    const isShortData = ref(true);

    const parsedDate = computed(() => {
        if (!props.date) return null;
        return new Date(props.date);
    });

    const isExpired = computed(() => {
        if (!parsedDate.value) return false;
        return parsedDate.value < new Date();
    });

    const formattedMonth = computed(() => {
        if (!parsedDate.value) return '';
        return parsedDate.value.toLocaleString('ru', { month: 'short' }).replace('.', '');
    });

    const formattedDay = computed(() => {
        if (!parsedDate.value) return '';
        return parsedDate.value.getDate();
    });

    const toggleDataFormat = () => {
        isShortData.value = !isShortData.value;
        emit('click', isShortData.value);
    };

    const currentTextValue = computed(() => {
        if (!parsedDate.value) return '';
        return isShortData.value ? formatDateTime(props.date) : formatRelativeTime(props.date);
    });

    const titleTooltip = computed(() => {
        return isShortData.value ? 'Показать сколько осталось' : 'Показать полную дату';
    });
</script>

<template>
    <div
        class="flex items-center gap-3 rounded-2xl border border-slate-800 bg-slate-950/50 p-3 transition-colors"
        :class="{ 'border-rose-500/20 bg-rose-950/5': isExpired }"
    >
        <template v-if="parsedDate">
            <div
                class="flex w-10 shrink-0 flex-col items-center overflow-hidden rounded-lg border bg-slate-900 transition-colors"
                :class="isExpired ? 'border-rose-500/30' : 'border-orange-500/30'"
                aria-hidden="true"
            >
                <div
                    class="w-full py-0.5 text-center text-[7px] font-black uppercase text-white transition-colors"
                    :class="isExpired ? 'bg-rose-600' : 'bg-orange-600'"
                >
                    {{ formattedMonth }}
                </div>
                <div class="text-lg font-black leading-tight text-white">
                    {{ formattedDay }}
                </div>
            </div>

            <div class="flex min-w-0 flex-col items-start">
                <span
                    class="text-[9px] font-black uppercase tracking-widest transition-colors"
                    :class="isExpired ? 'text-rose-500' : 'text-slate-500'"
                >
                    {{ isExpired ? 'Истёк' : 'Истекает' }}
                </span>

                <button
                    type="button"
                    @click="toggleDataFormat"
                    :title="titleTooltip"
                    :aria-label="`${isExpired ? 'Истёк' : 'Истекает'}: ${currentTextValue}. ${titleTooltip}`"
                    class="rounded text-left text-xs font-bold outline-none transition-colors focus-visible:ring-2 focus-visible:ring-orange-500 focus-visible:ring-offset-2 focus-visible:ring-offset-slate-950"
                    :class="[
                        isExpired
                            ? 'text-rose-400/80 hover:text-rose-400'
                            : 'text-white hover:text-orange-400',
                    ]"
                >
                    <Transition name="date-fade" mode="out-in">
                        <span :key="isShortData ? 'short' : 'full'">
                            {{ currentTextValue }}
                        </span>
                    </Transition>
                </button>
            </div>
        </template>

        <template v-else>
            <div class="flex items-center gap-3 text-slate-500" role="status">
                <CalendarIcon class="h-5 w-5 text-slate-600" aria-hidden="true" />
                <div class="flex flex-col">
                    <span class="text-[9px] font-black uppercase tracking-widest text-slate-600">
                        {{ label }}
                    </span>
                    <span class="text-xs font-bold uppercase italic text-slate-400">
                        {{ eternalText }}
                    </span>
                </div>
            </div>
        </template>
    </div>
</template>

<style scoped>
    .date-fade-enter-active,
    .date-fade-leave-active {
        transition:
            opacity 0.15s ease,
            transform 0.15s ease;
    }

    .date-fade-enter-from {
        opacity: 0;
        transform: translateY(2px);
    }

    .date-fade-leave-to {
        opacity: 0;
        transform: translateY(-2px);
    }
</style>
