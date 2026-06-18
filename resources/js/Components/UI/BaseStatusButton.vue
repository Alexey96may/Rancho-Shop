<script setup lang="ts">
    import { computed } from 'vue';

    import { HandThumbDownIcon, HandThumbUpIcon } from '@heroicons/vue/24/outline';

    type ItemStatus = 'approved' | 'hidden' | 'pending';
    type ButtonType = 'approve' | 'reject';

    interface Props {
        type: ButtonType;
        currentStatus: ItemStatus;
        disabled?: boolean;
        loading?: boolean;
    }

    const props = withDefaults(defineProps<Props>(), {
        disabled: false,
        loading: false,
    });

    const emit = defineEmits<{
        (e: 'click'): void;
    }>();

    const isActive = computed(() => {
        if (props.type === 'approve') return props.currentStatus === 'approved';
        if (props.type === 'reject') return props.currentStatus === 'hidden';
        return false;
    });

    const icon = computed(() => (props.type === 'approve' ? HandThumbUpIcon : HandThumbDownIcon));

    const buttonText = computed(() => {
        if (!isActive.value) return '';
        return props.type === 'approve' ? 'Одобрен' : 'Снят';
    });

    const ariaLabel = computed(() => {
        if (props.type === 'approve') {
            return isActive.value ? 'Убрать одобрение' : 'Одобрить публикацию';
        }
        return isActive.value ? 'Снять с публикации (вернуть в черновики)' : 'Скрыть и отклонить';
    });

    const buttonClasses = computed(() => {
        if (props.type === 'approve') {
            return isActive.value
                ? 'flex-1 bg-emerald-500/10 border-emerald-500/30 text-emerald-400 hover:bg-emerald-500/20'
                : 'bg-slate-800 border-slate-800 text-slate-500 hover:text-emerald-400 hover:bg-emerald-500/5 hover:border-emerald-500/20';
        }

        return isActive.value
            ? 'flex-1 bg-rose-500/10 border-rose-500/30 text-rose-400 hover:bg-rose-500/20'
            : 'bg-slate-800 border-slate-800 text-slate-500 hover:text-rose-400 hover:bg-rose-500/5 hover:border-rose-500/20';
    });

    const handleClick = () => {
        if (!props.disabled && !props.loading) {
            emit('click');
        }
    };
</script>

<template>
    <button
        type="button"
        @click="handleClick"
        :disabled="disabled || loading"
        :aria-label="ariaLabel"
        :title="ariaLabel"
        class="group relative flex items-center justify-center gap-2 rounded-2xl border px-4 py-3 text-xs font-black uppercase tracking-widest outline-none transition-all duration-200 focus:ring-2 focus:ring-orange-500/50 active:scale-95 disabled:pointer-events-none disabled:opacity-40"
        :class="buttonClasses"
    >
        <svg
            v-if="loading"
            class="h-4 w-4 animate-spin text-current"
            fill="none"
            viewBox="0 0 24 24"
        >
            <circle
                class="opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                stroke-width="4"
            />
            <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
            />
        </svg>

        <component
            v-else
            :is="icon"
            class="h-4 w-4 transition-transform duration-200"
            :class="[
                isActive ? 'scale-110' : 'group-hover:scale-110',
                type === 'approve' ? 'group-hover:rotate-6' : 'group-hover:-rotate-6',
            ]"
            aria-hidden="true"
        />

        <Transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="max-w-0 opacity-0 translate-x-1"
            enter-to-class="max-w-[100px] opacity-100 translate-x-0"
            leave-active-class="transition duration-100 ease-in"
            leave-from-class="max-w-[100px] opacity-100 translate-x-0"
            leave-to-class="max-w-0 opacity-0 -translate-x-1"
        >
            <span v-if="buttonText" class="overflow-hidden whitespace-nowrap">
                {{ buttonText }}
            </span>
        </Transition>
    </button>
</template>
