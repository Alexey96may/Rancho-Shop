import { ref } from 'vue';

import { router } from '@inertiajs/vue3';

import { useNotificationsStore } from '@/stores/notifications';

export function useAdminCrud() {
    const deletingIds = ref<Set<number | string>>(new Set());
    const restoringIds = ref<Set<number | string>>(new Set());

    /**
     * Generic entity deletion with confirmation and delay (Undo)
     *
     * @param baseRouteName The base route name for deletion (e.g., 'admin.animals' or 'admin.products')
     * @param id The ID of the record to be deleted
     * @param message The text of the notification message
     * @param delay The cancellation timeout in milliseconds (default: 4000)
     */
    const deleteEntity = (
        baseRouteName: string,
        id: number | string,
        message: string = 'Удаление записи',
        delay: number = 4000,
    ) => {
        if (typeof window === 'undefined') return;
        if (deletingIds.value.has(id)) return;

        const notify = useNotificationsStore();

        // Save the current search string (e.g., "?page=2&search=rex")
        const currentParams = window.location.search;
        const queryParams = currentParams ? { back: currentParams } : {};

        deletingIds.value.add(id);

        try {
            notify.withUndo(
                message,
                () => {
                    deletingIds.value.delete(id);
                },
                () => {
                    const url = route(`${baseRouteName}.destroy`, {
                        id: id,
                        ...queryParams,
                    });

                    router.delete(url, {
                        preserveScroll: true,
                        onFinish: () => {
                            deletingIds.value.delete(id);
                        },
                    });
                },
                delay,
            );
        } catch (error) {
            deletingIds.value.delete(id);
            console.error(`Failed to delete entity on ${baseRouteName}.destroy:`, error);
        }
    };

    /**
     * Generic entity restoration with confirmation and delay (Undo)
     * * @param baseRouteName The base route name for restoration (e.g., 'admin.animals' or 'admin.comments')
     * @param id The ID of the record to be restored
     * @param name The display name of the object for the message
     * @param delay The cancellation timeout in milliseconds (default: 4000)
     */

    const restoreEntity = (
        baseRouteName: string,
        id: number | string,
        name: string,
        delay = 4000,
    ) => {
        if (typeof window === 'undefined') return;
        if (restoringIds.value.has(id)) return;

        const notify = useNotificationsStore();

        const currentParams = window.location.search;
        const queryParams = currentParams ? { back: currentParams } : {};

        restoringIds.value.add(id);

        notify.withUndo(
            `Восстановить «${name}»?`,
            () => {
                restoringIds.value.delete(id);
            },
            () => {
                const url = route(`${baseRouteName}.restore`, { id, ...queryParams });

                router.patch(
                    url,
                    {},
                    {
                        preserveScroll: true,
                        onFinish: () => restoringIds.value.delete(id),
                    },
                );
            },
            delay,
        );
    };

    const isDeleting = (id: number | string): boolean => {
        return deletingIds.value.has(id);
    };

    const isRestoring = (id: number | string): boolean => {
        return restoringIds.value.has(id);
    };

    return { deletingIds, restoringIds, deleteEntity, restoreEntity, isDeleting, isRestoring };
}
