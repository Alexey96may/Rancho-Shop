import { router } from '@inertiajs/vue3';

import { beforeEach, describe, expect, it, vi } from 'vitest';

import { useAdminNavigation } from './useAdminNavigation';

// Mock the Inertia router
vi.mock('@inertiajs/vue3', () => ({
    router: {
        get: vi.fn(),
    },
}));

// Mock Laravel's Ziggy global route helper
const mockRoute = vi.fn((name, params) => {
    if (params) {
        return `http://localhost/${name.replace('.', '/')}/${params}`;
    }
    return `http://localhost/${name.replace('.', '/')}`;
});
vi.stubGlobal('route', mockRoute);

describe('useAdminNavigation', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        vi.restoreAllMocks();
        vi.spyOn(console, 'error').mockImplementation(() => {});

        stubWindowSearch('');
    });

    // Helper to mutate window.location.search in the testing environment (happy-dom/jsdom)
    function stubWindowSearch(searchString: string) {
        vi.stubGlobal('window', {
            location: {
                search: searchString,
            },
        });
    }

    it('should call router.get for the create page by default without ID', () => {
        const { navigateWithContext } = useAdminNavigation();

        navigateWithContext('admin.animals');

        expect(mockRoute).toHaveBeenCalledWith('admin.animals.create');
        expect(router.get).toHaveBeenCalledWith('http://localhost/admin/animals/create', {});
    });

    it('should call router.get for the edit page with the provided ID', () => {
        const { navigateWithContext } = useAdminNavigation();

        navigateWithContext('admin.products', 'edit', 42);

        expect(mockRoute).toHaveBeenCalledWith('admin.products.edit', 42);
        expect(router.get).toHaveBeenCalledWith('http://localhost/admin/products/edit/42', {});
    });

    it('should call router.get for the show page with the provided ID', () => {
        const { navigateWithContext } = useAdminNavigation();

        navigateWithContext('admin.faqs', 'show', 15);

        expect(mockRoute).toHaveBeenCalledWith('admin.faqs.show', 15);
        expect(router.get).toHaveBeenCalledWith('http://localhost/admin/faqs/show/15', {});
    });

    it('should pass current query parameters inside the "back" key to the create page', () => {
        const { navigateWithContext } = useAdminNavigation();
        const mockSearch = '?page=3&search=barsik';
        stubWindowSearch(mockSearch);

        navigateWithContext('admin.animals', 'create');

        expect(router.get).toHaveBeenCalledWith('http://localhost/admin/animals/create', {
            back: mockSearch,
        });
    });

    it('should pass current query parameters inside the "back" key to the edit and show pages', () => {
        const { navigateWithContext } = useAdminNavigation();
        const mockSearch = '?category_id=1';
        stubWindowSearch(mockSearch);

        // edit
        navigateWithContext('admin.products', 'edit', 10);
        expect(router.get).toHaveBeenCalledWith('http://localhost/admin/products/edit/10', {
            back: mockSearch,
        });

        // show
        navigateWithContext('admin.products', 'show', 10);
        expect(router.get).toHaveBeenCalledWith('http://localhost/admin/products/show/10', {
            back: mockSearch,
        });
    });

    it('should log an error and block navigation if id is missing for "edit" or "show" actions', () => {
        const { navigateWithContext } = useAdminNavigation();

        // edit without ID
        navigateWithContext('admin.products', 'edit');
        expect(console.error).toHaveBeenCalledWith(
            'Navigation error: ID is required for "edit" action.',
        );
        expect(router.get).not.toHaveBeenCalled();

        vi.clearAllMocks();

        // show without ID
        navigateWithContext('admin.products', 'show');
        expect(console.error).toHaveBeenCalledWith(
            'Navigation error: ID is required for "show" action.',
        );
        expect(router.get).not.toHaveBeenCalled();
    });

    it('should not throw an error or trigger navigation when window object is undefined (SSR environment)', () => {
        vi.stubGlobal('window', undefined);

        const { navigateWithContext } = useAdminNavigation();

        expect(() => {
            navigateWithContext('admin.animals', 'create');
        }).not.toThrow();

        expect(router.get).not.toHaveBeenCalled();
    });
});
