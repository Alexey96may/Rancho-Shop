<script setup lang="ts">
    import { computed } from 'vue';

    import {
        CheckCircleIcon,
        ExclamationTriangleIcon,
        InformationCircleIcon,
        XCircleIcon,
        XMarkIcon,
    } from '@heroicons/vue/24/outline';

    import type { Toast } from '@/stores/notifications';

    type Theme = 'light' | 'dark';

    const props = withDefaults(
        defineProps<{
            toast: Toast;
            theme?: Theme;
        }>(),
        {
            theme: 'light',
        },
    );

    const emit = defineEmits<{
        close: [];
        cancel: [];
    }>();

    const isDark = computed(() => props.theme === 'dark');

    /** Конфиг тоста по типу — иконка, цвета, полоска */
    const config = computed(() => {
        const dark = isDark.value;

        switch (props.toast.type) {
            case 'success':
                return {
                    icon: CheckCircleIcon,
                    container: dark
                        ? 'bg-rancho-ink-900 border-rancho-pine/40 text-rancho-ink-100'
                        : 'bg-white border-rancho-pine/30 text-rancho-forest',
                    iconWrap: dark
                        ? 'bg-rancho-pine/20 text-rancho-mint'
                        : 'bg-rancho-pine/10 text-rancho-pine',
                    bar: 'bg-rancho-pine',
                };
            case 'error':
                return {
                    icon: XCircleIcon,
                    container: dark
                        ? 'bg-rancho-ink-900 border-rose-500/40 text-rancho-ink-100'
                        : 'bg-white border-rose-300 text-rancho-forest',
                    iconWrap: dark
                        ? 'bg-rose-500/20 text-rose-300'
                        : 'bg-rose-500/10 text-rose-600',
                    bar: 'bg-rose-500',
                };
            case 'warning':
                return {
                    icon: ExclamationTriangleIcon,
                    container: dark
                        ? 'bg-rancho-ink-900 border-rancho-buttercup/40 text-rancho-ink-100'
                        : 'bg-white border-rancho-buttercup/60 text-rancho-forest',
                    iconWrap: dark
                        ? 'bg-rancho-buttercup/20 text-rancho-buttercup'
                        : 'bg-rancho-buttercup/20 text-rancho-ember',
                    bar: 'bg-rancho-buttercup',
                };
            default:
                return {
                    icon: InformationCircleIcon,
                    container: dark
                        ? 'bg-rancho-ink-900 border-rancho-ink-700 text-rancho-ink-200'
                        : 'bg-white border-rancho-ink-200 text-rancho-forest',
                    iconWrap: dark
                        ? 'bg-rancho-ink-800 text-rancho-ink-300'
                        : 'bg-rancho-ink-100 text-rancho-ink-600',
                    bar: dark ? 'bg-rancho-ink-500' : 'bg-rancho-ink-400',
                };
        }
    });

    const showProgress = computed(() => props.toast.duration > 0 && props.toast.hasUndo);

    const actionButtonClasses = computed(() =>
        isDark.value
            ? 'text-rancho-ink-200 hover:text-white'
            : 'text-rancho-ink-600 hover:text-rancho-forest',
    );
</script>

<template>
    <div
        class="shadow-lg pointer-events-auto relative flex items-center gap-3 overflow-hidden rounded-2xl border p-4"
        :class="config.container"
        role="status"
    >
        <!-- Иконка -->
        <div
            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full sm:h-9 sm:w-9 sm:rounded-xl"
            :class="config.iconWrap"
            aria-hidden="true"
        >
            <component :is="config.icon" class="h-5 w-5" />
        </div>

        <!-- Текст -->
        <div class="min-w-0 flex-1">
            <p class="text-xs font-medium leading-snug sm:text-sm">
                {{ toast.message }}
            </p>
        </div>

        <!-- Кнопки действий -->
        <div class="flex shrink-0 items-center gap-1">
            <button
                v-if="showProgress"
                type="button"
                @click="emit('cancel')"
                class="focus-visible:ring-current/40 rounded-lg px-2.5 py-1.5 text-[10px] font-bold uppercase tracking-wider transition-colors focus:outline-none focus-visible:ring-2 sm:text-xs"
                :class="actionButtonClasses"
            >
                Отменить
            </button>

            <button
                v-if="!showProgress"
                type="button"
                @click="emit('close')"
                class="focus-visible:ring-current/40 flex h-7 w-7 items-center justify-center rounded-lg opacity-60 transition-opacity hover:opacity-100 focus:outline-none focus-visible:ring-2"
                aria-label="Закрыть"
            >
                <XMarkIcon class="h-4 w-4" aria-hidden="true" />
            </button>
        </div>

        <!-- PROGRESS BAR -->
        <div
            v-if="showProgress"
            class="absolute inset-x-0 bottom-0 h-1 overflow-hidden"
            :class="isDark ? 'bg-white/5' : 'bg-rancho-ink-100'"
            aria-hidden="true"
        >
            <div
                class="toast-progress h-full"
                :class="config.bar"
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

    @media (prefers-reduced-motion: reduce) {
        .toast-progress {
            animation: none;
        }
    }
</style>
