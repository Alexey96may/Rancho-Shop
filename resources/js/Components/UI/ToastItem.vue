<script setup lang="ts">
    import { computed } from 'vue';

    import type { Toast } from '@/stores/notifications';

    const props = defineProps<{ toast: Toast }>();
    const emit = defineEmits<{
        close: [];
        cancel: [];
    }>();

    const styles = computed(() => {
        switch (props.toast.type) {
            case 'success':
                return 'bg-green-50 border-green-200 text-green-900';
            case 'error':
                return 'bg-red-50 border-red-200 text-red-900';
            case 'warning':
                return 'bg-amber-50 border-amber-200 text-amber-900';
            default:
                return 'bg-slate-50 border-slate-200 text-slate-900';
        }
    });

    const barColor = computed(() => {
        switch (props.toast.type) {
            case 'success':
                return 'bg-green-500';
            case 'error':
                return 'bg-red-500';
            case 'warning':
                return 'bg-amber-500';
            default:
                return 'bg-slate-500';
        }
    });

    const hasCancel = computed(() => !!props.toast.onCancel);
    const showProgress = computed(() => props.toast.duration > 0 && props.toast.hasUndo);
</script>

<template>
    <div
        class="shadow-lg pointer-events-auto relative flex items-center justify-between gap-3 overflow-hidden rounded-xl border p-4"
        :class="styles"
        role="status"
    >
        <span class="text-sm">{{ toast.message }}</span>

        <div class="flex items-center gap-2">
            <button
                v-if="showProgress"
                @click="emit('cancel')"
                class="text-sm font-semibold underline hover:no-underline"
            >
                Отменить
            </button>

            <button
                v-if="!toast.hasUndo"
                @click="emit('close')"
                class="text-lg leading-none opacity-60 hover:opacity-100"
                aria-label="Закрыть"
            >
                ×
            </button>
        </div>

        <!-- PROGRESS BAR -->
        <div v-if="showProgress" class="absolute inset-x-0 bottom-0 h-1 overflow-hidden bg-black/5">
            <div
                class="toast-progress h-full"
                :class="barColor"
                :style="{ animationDuration: `${toast.duration}ms` }"
            />
        </div>
    </div>
</template>

<style scoped>
    .toast-progress {
        width: 100%;
        transform-origin: left center;
        animation-name: toast-shrink;
        animation-timing-function: linear;
        animation-fill-mode: forwards;
        will-change: transform;
    }

    @keyframes toast-shrink {
        from {
            transform: scaleX(1);
        }
        to {
            transform: scaleX(0);
        }
    }
</style>
