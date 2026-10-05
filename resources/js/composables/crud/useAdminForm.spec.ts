import { InertiaForm, router } from '@inertiajs/vue3';

import { beforeEach, describe, expect, it, vi } from 'vitest';

import { useFlash } from '@/composables/ui/useFlash';

import { useAdminForm } from './useAdminForm';

// Mock the global Ziggy Laravel routing helper
const mockRoute = vi.fn((name, id) => {
    return id
        ? `http://localhost/${name.replace('.', '/')}/${id}`
        : `http://localhost/${name.replace('.', '/')}`;
});
vi.stubGlobal('route', mockRoute);

// Mock the global Inertia router
vi.mock('@inertiajs/vue3', async (importOriginal) => {
    const original = await importOriginal<typeof import('@inertiajs/vue3')>();
    return {
        ...original,
        router: {
            post: vi.fn(),
        },
    };
});

// Mocking the useFlash composable
vi.mock('@/composables/ui/useFlash', () => ({
    useFlash: () => ({
        notify: vi.fn(),
    }),
}));

describe('useAdminForm - Submit Logic', () => {
    const createMockForm = (initialData = {}) =>
        ({
            data: vi.fn(() => initialData),
            post: vi.fn(),
            put: vi.fn(),
            clearErrors: vi.fn(),
            setError: vi.fn(),
            reset: vi.fn(),
            processing: false,
        }) as unknown as InertiaForm<any>;

    beforeEach(() => {
        vi.clearAllMocks();
        vi.spyOn(console, 'error').mockImplementation(() => {});
    });

    it('should trigger form.post with the store route when id is null (create mode)', async () => {
        const { submitForm } = useAdminForm();
        const mockForm = createMockForm();

        await submitForm(mockForm, 'admin.categories', null);

        expect(mockRoute).toHaveBeenCalledWith('admin.categories.store');
        expect(mockForm.post).toHaveBeenCalledWith(
            'http://localhost/admin/categories/store',
            expect.any(Object),
        );
        expect(mockForm.put).not.toHaveBeenCalled();
        expect(router.post).not.toHaveBeenCalled();
    });

    it('should trigger form.put with the update route when id is provided and no files (standard edit mode)', async () => {
        const { submitForm } = useAdminForm();
        const mockForm = createMockForm();

        await submitForm(mockForm, 'admin.categories', 42, { hasFiles: false });

        expect(mockRoute).toHaveBeenCalledWith('admin.categories.update', 42);
        expect(mockForm.put).toHaveBeenCalledWith(
            'http://localhost/admin/categories/update/42',
            expect.any(Object),
        );
        expect(mockForm.post).not.toHaveBeenCalled();
        expect(router.post).not.toHaveBeenCalled();
    });

    it('should route through global router.post with _method: PUT when edit mode has files', async () => {
        const { submitForm } = useAdminForm();
        const mockFormData = { name: 'Grizzly Bear', avatar: null };
        const mockForm = createMockForm(mockFormData);

        await submitForm(mockForm, 'admin.animals', 7, { hasFiles: true });

        expect(mockRoute).toHaveBeenCalledWith('admin.animals.update', 7);

        // Verify that the form's local methods were NOT called.
        expect(mockForm.put).not.toHaveBeenCalled();
        expect(mockForm.post).not.toHaveBeenCalled();

        // Verify that the global router sent a POST request with method spoofing in the data.
        expect(router.post).toHaveBeenCalledWith(
            'http://localhost/admin/animals/update/7',
            {
                name: 'Grizzly Bear',
                avatar: null,
                _method: 'PUT',
            },
            expect.objectContaining({
                forceFormData: true,
            }),
        );
    });

    it('should synchronize form state hooks correctly when executing global router.post for files', async () => {
        const { submitForm } = useAdminForm();
        const mockForm = createMockForm({ name: 'Wolf' });

        // Intercept the options passed to router.post to manually trigger the hooks.
        vi.mocked(router.post).mockImplementationOnce((url, data, options: any) => {
            options.onBefore();
            options.onStart();
            expect(mockForm.processing).toBe(true); // Check the startup loader
            options.onFinish();
            expect(mockForm.processing).toBe(false); // Verify loader removal
            options.onSuccess();
        });

        const onSuccessCallback = vi.fn();
        await submitForm(mockForm, 'admin.animals', 99, {
            hasFiles: true,
            onSuccess: onSuccessCallback,
        });

        expect(mockForm.clearErrors).toHaveBeenCalled();
        expect(mockForm.reset).toHaveBeenCalled();
        expect(onSuccessCallback).toHaveBeenCalled();
    });

    it('should propagate validation errors to the form object when global router path fails', async () => {
        const { submitForm } = useAdminForm();
        const { notify } = useFlash();
        const mockForm = createMockForm({ name: '' });
        const mockValidationErrors = { name: 'The name field is required.' };

        vi.mocked(router.post).mockImplementationOnce((url, data, options: any) => {
            options.onError(mockValidationErrors);
        });

        await submitForm(mockForm, 'admin.animals', 99, { hasFiles: true });

        expect(mockForm.setError).toHaveBeenCalledWith(mockValidationErrors);
        expect(console.error).toHaveBeenCalledWith(mockValidationErrors);
        expect(notify).toHaveBeenCalledWith('Ошибка валидации', 'error');
    });

    it('should execute onSuccessCallback when standard form.post succeeds', async () => {
        const { submitForm } = useAdminForm();
        const onSuccessCallback = vi.fn();

        const mockForm = {
            post: vi.fn((url, options) => {
                if (options?.onSuccess) options.onSuccess();
            }),
        } as unknown as InertiaForm<any>;

        await submitForm(mockForm, 'admin.categories', null, { onSuccess: onSuccessCallback });

        expect(onSuccessCallback).toHaveBeenCalledTimes(1);
    });
});

describe('useAdminForm - Modal Management', () => {
    const createMockForm = (initialData: Record<string, any>) =>
        ({
            data: vi.fn(() => initialData),
            clearErrors: vi.fn(),
            reset: vi.fn(),
            setData: vi.fn(),
        }) as unknown as InertiaForm<any>;

    it('should initialize in create mode when no entity is provided', () => {
        const { isModalOpen, editMode, currentId, openModal } = useAdminForm();
        const mockForm = createMockForm({ name: '', type: '' });

        openModal(mockForm, null);

        expect(isModalOpen.value).toBe(true);
        expect(editMode.value).toBe(false);
        expect(currentId.value).toBeNull();
        expect(mockForm.clearErrors).toHaveBeenCalled();
        expect(mockForm.reset).toHaveBeenCalled();
        expect(mockForm.setData).not.toHaveBeenCalled();
    });

    it('should map entity fields to form and set edit mode when entity is provided', () => {
        const { isModalOpen, editMode, currentId, openModal } = useAdminForm();
        const mockForm = createMockForm({ name: '', type: '' });
        const mockEntity = {
            id: 123,
            name: 'Test Category',
            type: 'animal',
            created_at: '2026-01-01',
        };

        openModal(mockForm, mockEntity);

        expect(isModalOpen.value).toBe(true);
        expect(editMode.value).toBe(true);
        expect(currentId.value).toBe(123);
        expect(mockForm.clearErrors).toHaveBeenCalled();
        expect(mockForm.setData).toHaveBeenCalledWith({
            name: 'Test Category',
            type: 'animal',
        });
    });

    it('should reset state completely when closeModal is called', () => {
        const { isModalOpen, editMode, currentId, openModal, closeModal } = useAdminForm();
        const mockForm = createMockForm({ name: '' });

        openModal(mockForm, { id: 5, name: 'Cats' });
        closeModal(mockForm);

        expect(isModalOpen.value).toBe(false);
        expect(editMode.value).toBe(false);
        expect(currentId.value).toBeNull();
        expect(mockForm.reset).toHaveBeenCalled();
    });
});
