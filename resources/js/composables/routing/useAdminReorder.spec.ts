import { router } from '@inertiajs/vue3';

import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

import { useFlash } from '@/composables/ui/useFlash';

import { type DraggableEvent, useAdminReorder } from './useAdminReorder';

// Laravel (Ziggy)
const mockRoute = vi.fn((name) => `http://localhost/${name.replace('.', '/')}`);
vi.stubGlobal('route', mockRoute);

// Inertia router
vi.mock('@getinertiajs/vue3', () => ({
    router: {
        patch: vi.fn(),
    },
}));

// Notification Hook
vi.mock('@/composables/ui/useFlash', () => ({
    useFlash: () => ({
        notify: vi.fn(),
    }),
}));

describe('useAdminReorder', () => {
    let mockElement: HTMLElement;
    let mockItemsData: Array<{ id: number }>;

    // Helper for creating a default draggable event
    const createMockEvent = (oldIndex: number, newIndex: number): DraggableEvent => ({
        oldIndex,
        newIndex,
        item: mockElement,
        from: document.createElement('div'),
        to: document.createElement('div'),
    });

    beforeEach(() => {
        vi.useFakeTimers(); // Enable fake timers before each test
        vi.clearAllMocks();
        vi.spyOn(console, 'error').mockImplementation(() => {});

        // Prepare a clean DOM element for tests
        mockElement = document.createElement('div');
        mockElement.classList.add('some-class');

        // Prepare test data
        mockItemsData = [{ id: 10 }, { id: 20 }, { id: 30 }];
    });

    afterEach(() => {
        vi.useRealTimers(); // Return the actual time after the test
    });

    it('should do nothing if oldIndex equals newIndex', () => {
        const { handleReorder } = useAdminReorder();
        const event = createMockEvent(2, 2);

        handleReorder(event, 'admin.faq.reorder', mockItemsData);

        // The animation class must not jitter.
        expect(mockElement.classList.contains('drop-highlight')).toBe(false);
        // The request should not be sent via Inertia.
        expect(router.patch).not.toHaveBeenCalled();
    });

    it('should manage drop-highlight class and remove it after 2000ms', () => {
        const { handleReorder } = useAdminReorder();
        const event = createMockEvent(0, 2);

        handleReorder(event, 'admin.faq.reorder', mockItemsData);

        // The class should be successfully added immediately after the drop.
        expect(mockElement.classList.contains('drop-highlight')).toBe(true);

        // Advance time by 1999ms — the class should still be present.
        vi.advanceTimersByTime(1999);
        expect(mockElement.classList.contains('drop-highlight')).toBe(true);

        // Advance another 1ms (total: 2000ms) — the class should be removed.
        vi.advanceTimersByTime(1);
        expect(mockElement.classList.contains('drop-highlight')).toBe(false);
    });

    it('should clear previous timer if handleReorder is called rapidly', () => {
        const { handleReorder } = useAdminReorder();
        const event = createMockEvent(0, 2);

        const clearTimeoutSpy = vi.spyOn(globalThis, 'clearTimeout');

        // First call
        handleReorder(event, 'admin.faq.reorder', mockItemsData);
        expect(clearTimeoutSpy).not.toHaveBeenCalled();

        // 500ms have passed; the user is dragging another element.
        vi.advanceTimersByTime(500);
        handleReorder(event, 'admin.faq.reorder', mockItemsData);

        // clearTimeout should execute to reset the first timer.
        expect(clearTimeoutSpy).toHaveBeenCalledTimes(1);
    });

    it('should send patch request with correctly mapped IDs via Inertia router', () => {
        const { handleReorder } = useAdminReorder();
        const event = createMockEvent(1, 0);

        handleReorder(event, 'admin.faq.reorder', mockItemsData);

        expect(mockRoute).toHaveBeenCalledWith('admin.faq.reorder');
        expect(router.patch).toHaveBeenCalledWith(
            'http://localhost/admin/faq/reorder',
            { ids: [10, 20, 30] },
            expect.objectContaining({
                preserveScroll: true,
                preserveState: true,
                onError: expect.any(Function),
            }),
        );
    });

    it('should trigger flash notification and log error when server request fails', () => {
        const { handleReorder } = useAdminReorder();
        const { notify } = useFlash();
        const event = createMockEvent(1, 3);
        const mockServerError = { message: 'Server Error' };

        // Emulate an immediate call to onError when sending the request.
        vi.mocked(router.patch).mockImplementationOnce((url, data, options) => {
            if (options?.onError) options.onError(mockServerError);
            return null as any;
        });

        handleReorder(event, 'admin.faq.reorder', mockItemsData);

        expect(console.error).toHaveBeenCalledWith('Error on position changing', mockServerError);
        expect(notify).toHaveBeenCalledWith('Ошибка при смены позиции', 'error');
    });
});
