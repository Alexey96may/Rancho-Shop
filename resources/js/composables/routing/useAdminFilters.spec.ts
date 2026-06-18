import { InertiaForm } from '@inertiajs/vue3';

import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

import { useAdminFilters } from './useAdminFilters';

// Mock Laravel's Ziggy global route helper
const mockRoute = vi.fn((name) => `http://localhost/${name.replace('.', '/')}`);
vi.stubGlobal('route', mockRoute);

describe('useAdminFilters', () => {
    // Create a mock instance of the Inertia Form object
    const createMockForm = () =>
        ({
            get: vi.fn((url, options) => {
                if (options?.onFinish) options.onFinish();
            }),
            reset: vi.fn(),
        }) as unknown as InertiaForm<any>;

    beforeEach(() => {
        vi.clearAllMocks();
        vi.useFakeTimers(); // Enable fake timers to mock debounce delays
    });

    afterEach(() => {
        vi.useRealTimers(); // Restore real timers after each test run
    });

    it('should trigger form.get with correct parameters after the debounce delay', () => {
        const { isFiltering, submitFilters } = useAdminFilters();
        const mockForm = createMockForm();

        // Act: Call the function
        submitFilters(mockForm, 'admin.animals.index');

        // Assert: It shouldn't be executed immediately due to debounce
        expect(mockForm.get).not.toHaveBeenCalled();
        expect(isFiltering.value).toBe(false);

        // Fast-forward time by 400ms
        vi.advanceTimersByTime(400);

        // Assert: Executed after the timeout passed
        expect(mockRoute).toHaveBeenCalledWith('admin.animals.index');
        expect(mockForm.get).toHaveBeenCalledWith(
            'http://localhost/admin/animals/index',
            expect.objectContaining({
                preserveState: true,
                replace: true,
                preserveScroll: true,
            }),
        );
    });

    it('should manage isFiltering state correctly during submission lifecycle', () => {
        const { isFiltering, submitFilters } = useAdminFilters();

        // Custom mock form to manually control when onFinish is fired
        let triggerFinish: () => void = () => {};
        const mockForm = {
            reset: vi.fn(),
            get: vi.fn((url, options) => {
                triggerFinish = options.onFinish;
            }),
        } as unknown as InertiaForm<any>;

        submitFilters(mockForm, 'admin.animals.index');
        vi.advanceTimersByTime(400);

        // Assert: As soon as debounce finishes and request starts, state is true
        expect(isFiltering.value).toBe(true);

        // Act: Trigger the onFinish callback from Inertia
        triggerFinish();

        // Assert: State is reset back to false
        expect(isFiltering.value).toBe(false);
    });

    it('should debounce multiple consecutive calls and only execute the last one', () => {
        const { submitFilters } = useAdminFilters();
        const mockForm = createMockForm();

        // Act: Trigger multiple rapid updates (e.g., user typing fast)
        submitFilters(mockForm, 'admin.animals.index');
        vi.advanceTimersByTime(200); // 200ms passed, not enough to trigger

        submitFilters(mockForm, 'admin.animals.index');
        vi.advanceTimersByTime(200); // another 200ms passed, total 400ms from start, but reset by 2nd call

        expect(mockForm.get).not.toHaveBeenCalled();

        vi.advanceTimersByTime(200); // complete the remaining time for the second call

        // Assert: Form submission was triggered exactly once
        expect(mockForm.get).toHaveBeenCalledTimes(1);
    });

    it('should reset the form and immediately request data when clearFilters is called', () => {
        const { isFiltering, clearFilters } = useAdminFilters();
        const mockForm = createMockForm();

        // Act: Clear filters should execute immediately without debounce
        clearFilters(mockForm);

        // Assert: Form reset was called
        expect(mockForm.reset).toHaveBeenCalled();
        // Assert: Request went out immediately
        expect(mockForm.get).toHaveBeenCalledTimes(1);
        expect(mockRoute).toHaveBeenCalledWith('admin.animals.index');
    });
});
