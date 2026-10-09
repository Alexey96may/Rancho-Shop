<script setup lang="ts">
    import { storeToRefs } from 'pinia';

    import ToastItem from '@/Components/UI/ToastItem.vue';
    import { useNotificationsStore } from '@/stores/notifications';

    const store = useNotificationsStore();
    const { toasts } = storeToRefs(store);
</script>

<template>
    <Teleport to="body">
        <div
            class="pointer-events-none fixed bottom-4 right-4 z-[100] flex w-full max-w-sm flex-col gap-2"
        >
            <TransitionGroup name="toast" tag="div" class="flex flex-col gap-2">
                <ToastItem
                    v-for="toast in toasts"
                    :key="toast.id"
                    :toast="toast"
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
