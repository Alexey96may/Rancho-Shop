import { ref } from 'vue';

import { defineStore } from 'pinia';

/**
 * Notifications Store
 *
 * Central place for toasts: list, timers, undo callbacks.
 * Components only render — logic lives here.
 *
 * - Max 4 toasts. Adding a new one drops the oldest (timer cleared).
 * - `duration: 0` — never auto-closes, close manually via `remove(id)`.
 *
 * Methods:
 *   success(message, duration?)                        → id
 *   error(message, duration?)                          → id
 *   info(message, duration?)                           → id
 *   warning(message, duration?)                        → id
 *   withUndo(message, onCancel, onConfirm?, duration?) → id
 *   add({ type, message, duration, onCancel?, onConfirm? }) → id
 *   remove(id)  — remove, do NOT call onConfirm
 *   cancel(id)  — remove + call onCancel (undo button)
 *
 * Examples:
 *
 *   const notify = useNotificationsStore();
 *
 *   notify.success('Order placed');
 *   notify.error('Payment failed', 0); // sticky
 *
 *   // undo pattern
 *   notify.withUndo(
 *     `"${item.name}" removed`,
 *     () => cart.restore(item),       // user clicks "Undo"
 *     () => {},                        // timer expired
 *     5000,
 *   );
 *
 *   // manual close
 *   const id = notify.info('Loading...', 0);
 *   notify.remove(id);
 *
 * Inertia flash:
 *   Backend: return back()->with('success', 'Saved');
 *   Frontend: use useFlashToasts() once in layout.
 *
 * Render: <Toaster /> once in MainLayout.vue.
 *
 * Notes:
 *   - never write setTimeout in components — use this store;
 *   - onCancel / onConfirm are mutually exclusive;
 *   - remove() skips onConfirm by design (manual dismiss).
 */

export type ToastType = 'success' | 'error' | 'info' | 'warning';

export interface Toast {
    id: number;
    type: ToastType;
    message: string;
    duration: number;
    onCancel?: () => void;
    onConfirm?: () => void;
    hasUndo?: boolean;
}

const MAX_TOASTS = 4;

export const useNotificationsStore = defineStore('notifications', () => {
    const toasts = ref<Toast[]>([]);
    const timers = new Map<number, ReturnType<typeof setTimeout>>();
    let nextId = 1;

    function add(payload: Omit<Toast, 'id'>): number {
        const id = nextId++;
        const toast: Toast = { id, ...payload };

        // Лимит: сразу удаляем самые старые, вместе с их таймерами
        while (toasts.value.length >= MAX_TOASTS) {
            remove(toasts.value[0].id);
        }

        toasts.value.push(toast);

        if (toast.duration > 0) {
            const t = setTimeout(() => {
                remove(id);
                toast.onConfirm?.();
            }, toast.duration);
            timers.set(id, t);
        }

        return id;
    }

    function remove(id: number): void {
        const t = timers.get(id);
        if (t) {
            clearTimeout(t);
            timers.delete(id);
        }
        toasts.value = toasts.value.filter((x) => x.id !== id);
    }

    function cancel(id: number): void {
        const toast = toasts.value.find((x) => x.id === id);
        if (!toast) return;
        remove(id);
        toast.onCancel?.();
    }

    const success = (message: string, duration = 3000) =>
        add({ type: 'success', message, duration });
    const error = (message: string, duration = 5000) => add({ type: 'error', message, duration });
    const info = (message: string, duration = 3000) => add({ type: 'info', message, duration });
    const warning = (message: string, duration = 4000) =>
        add({ type: 'warning', message, duration });

    function withUndo(
        message: string,
        onCancel: () => void,
        onConfirm: () => void = () => {},
        duration = 5000,
    ): number {
        return add({ type: 'warning', message, duration, onCancel, onConfirm, hasUndo: true });
    }

    return { toasts, add, remove, cancel, success, error, info, warning, withUndo };
});
