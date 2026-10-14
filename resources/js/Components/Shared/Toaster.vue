<script setup lang="ts">
    import { storeToRefs } from 'pinia';

    import ToastItem from '@/Components/UI/ToastItem.vue';
    import { useNotificationsStore } from '@/stores/notifications';

    const store = useNotificationsStore();
    const { toasts } = storeToRefs(store);

    type Theme = 'light' | 'dark';

    const props = withDefaults(
        defineProps<{
            theme?: Theme;
        }>(),
        {
            theme: 'light',
        },
    );
</script>

<template>
    <Teleport to="body">
        <div
            class="pointer-events-none fixed inset-0 bottom-4 left-1/2 top-auto z-[100] flex w-[90%] -translate-x-1/2 flex-col gap-2 sm:left-auto sm:right-4 sm:top-auto sm:w-[90vw] sm:max-w-sm sm:translate-x-0"
        >
            <TransitionGroup name="toast" tag="div" class="flex flex-col gap-2">
                <ToastItem
                    v-for="toast in toasts"
                    :key="toast.id"
                    :toast="toast"
                    :theme="theme"
                    @close="store.remove(toast.id)"
                    @cancel="store.cancel(toast.id)"
                />
            </TransitionGroup>
        </div>
    </Teleport>
</template>

<style scoped>
    .toast-enter-active,
    .toast-leave-active {
        transition:
            opacity 0.2s ease,
            transform 0.2s ease;
    }
    .toast-enter-from {
        opacity: 0;
        transform: translateX(20px);
    }
    .toast-leave-to {
        opacity: 0;
        transform: translateX(20px);
    }
    .toast-move {
        transition: transform 0.2s ease;
    }
</style>
