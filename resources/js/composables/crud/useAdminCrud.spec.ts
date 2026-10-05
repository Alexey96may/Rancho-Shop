import { router } from '@inertiajs/vue3';

import { beforeEach, describe, expect, it, vi } from 'vitest';

import { useFlash } from '@/composables/ui/useFlash';

import { useAdminCrud } from './useAdminCrud';

const { notifyWithUndo } = useFlash();

// Mock Inertia router
vi.mock('@inertiajs/vue3', () => ({
    router: {
        delete: vi.fn((url, options) => {
            // Emulate immediate execution of onFinish callback if provided
            if (options?.onFinish) options.onFinish();
        }),
    },
}));

// Mock custom notification system
vi.mock('@/composables/ui/useFlash', () => ({
    useFlash: () => ({
        notifyWithUndo: vi.fn(),
    }),
}));

// Mock Laravel's Ziggy global route helper
const mockRoute = vi.fn((name, params) => `http://localhost/${name.replace('.', '/')}/${params}`);
vi.stubGlobal('route', mockRoute);

describe('useAdminCrud', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('should successfully trigger router.delete when action is confirmed', async () => {
        // Arrange: notifyWithUndo resolves to true (user confirmed / didn't click undo)
        vi.mocked(notifyWithUndo).mockResolvedValue(true);
        const { deleteEntity, isDeleting } = useAdminCrud();
        const entityId = 15;

        // Act
        const deletePromise = deleteEntity('admin.animals', entityId, 'Delete Animal', 4000);

        // Assert: While waiting for confirmation, the ID should be in the deleting state
        expect(isDeleting(entityId)).toBe(true);

        await deletePromise;

        // Assert: router.delete was called with correct parameters
        expect(mockRoute).toHaveBeenCalledWith('admin.animals.destroy', entityId);
        expect(router.delete).toHaveBeenCalledWith(
            'http://localhost/admin/animals/destroy/15',
            expect.objectContaining({ preserveScroll: true }),
        );

        // Assert: After execution finishes, the ID is removed from the tracking set
        expect(isDeleting(entityId)).toBe(false);
    });

    it('should not trigger router.delete and should clean up ID when action is canceled (undo clicked)', async () => {
        // Arrange: notifyWithUndo resolves to false (user clicked undo)
        vi.mocked(notifyWithUndo).mockResolvedValue(false);
        const { deleteEntity, isDeleting } = useAdminCrud();
        const entityId = 42;

        // Act
        await deleteEntity('admin.products', entityId, 'Delete Product');

        // Assert: router.delete should not be invoked
        expect(router.delete).not.toHaveBeenCalled();

        // Assert: The ID must be clean and not stuck in loading state
        expect(isDeleting(entityId)).toBe(false);
    });

    it('should prevent duplicate submissions if the same entity deletion is already in progress', async () => {
        // Arrange: notifyWithUndo is pending (we won't resolve it immediately)
        let resolveConfirmation: (value: boolean) => void = () => {};
        vi.mocked(notifyWithUndo).mockImplementation(() => {
            return new Promise((resolve) => {
                resolveConfirmation = resolve;
            });
        });

        const { deleteEntity } = useAdminCrud();
        const entityId = 99;

        // Act: Trigger first deletion
        deleteEntity('admin.animals', entityId);
        // Act: Instantly trigger second deletion while first is still pending
        deleteEntity('admin.animals', entityId);

        // Assert: notifyWithUndo should only be called once despite double click
        expect(notifyWithUndo).toHaveBeenCalledTimes(1);

        // Clean up pending promise
        resolveConfirmation(true);
    });

    it('should clean up the deleting ID even if an unhandled exception occurs', async () => {
        // Arrange: notifyWithUndo throws an unexpected error
        vi.mocked(notifyWithUndo).mockRejectedValue(new Error('Unexpected UI Crash'));

        // Suppress console.error in test output
        const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {});

        const { deleteEntity, isDeleting } = useAdminCrud();
        const entityId = 77;

        // Act
        await deleteEntity('admin.animals', entityId);

        // Assert: The ID should not be locked in deleting state permanently
        expect(isDeleting(entityId)).toBe(false);
        expect(router.delete).not.toHaveBeenCalled();

        consoleSpy.mockRestore();
    });
});
