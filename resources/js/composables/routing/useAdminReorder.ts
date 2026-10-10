import { onUnmounted } from 'vue';

import { router } from '@inertiajs/vue3';

import { useNotificationsStore } from '@/stores/notifications';

export interface DraggableEvent {
    oldIndex: number;
    newIndex: number;
    item: HTMLElement;
    from: HTMLElement;
    to: HTMLElement;
}

export function useAdminReorder() {
    let reorderTimer: ReturnType<typeof setTimeout> | null = null;

    /**
     * Generic handler for reordering elements
     *
     * @param e          Event object from the draggable library
     * @param routeName  The name of the route that accepts the new IDs (e.g., 'admin.faq.reorder')
     * @param itemsData  The current array of items from props (e.g., props.faqs.data)
     */
    const handleReorder = (
        e: DraggableEvent,
        routeName: string,
        itemsData: Array<{ id: number | string }>,
    ) => {
        const notify = useNotificationsStore();
        if (e.oldIndex === e.newIndex) return;

        const droppedItem = e.item;

        // Reset the previous timer if the user drags elements very quickly.
        if (reorderTimer) {
            clearTimeout(reorderTimer);
        }

        // Restart the CSS highlight animation (clever magic using the offsetWidth hack)
        droppedItem.classList.remove('drop-highlight');
        void droppedItem.offsetWidth;
        droppedItem.classList.add('drop-highlight');

        // Reliably remove the class after 2 seconds
        reorderTimer = setTimeout(() => {
            droppedItem.classList.remove('drop-highlight');
            reorderTimer = null;
        }, 2000);

        // Assemble an array of IDs in their new order
        const ids = itemsData.map((item) => item.id);

        router.patch(
            route(routeName),
            { ids },
            {
                preserveScroll: true,
                preserveState: true,
                onError: (error) => {
                    notify.error('Ошибка при смене позиции');
                    console.error('Error on position changing', error);
                },
            },
        );
    };

    onUnmounted(() => {
        if (reorderTimer) {
            clearTimeout(reorderTimer);
        }
    });

    return {
        handleReorder,
    };
}
