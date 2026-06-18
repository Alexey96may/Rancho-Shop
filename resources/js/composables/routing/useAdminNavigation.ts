import { router } from '@inertiajs/vue3';

export type NavigationAction = 'create' | 'edit' | 'show';

export function useAdminNavigation() {
    /**
     * Navigates to the create or edit page while preserving the current query parameters (filters).
     *
     * @param baseRouteName The base part of the route (e.g., 'admin.products' or 'admin.animals').
     * @param action        The target action ('create', 'edit', 'show').
     * @param id The ID of the entity to edit (if not provided, navigates to the create page).
     */
    const navigateWithContext = (
        baseRouteName: string,
        action: NavigationAction = 'create',
        id?: number | string,
    ) => {
        if (typeof window === 'undefined') return;

        // Save the current search string (e.g., "?page=2&search=rex")
        const currentParams = window.location.search;
        const queryParams = currentParams ? { back: currentParams } : {};

        if (action !== 'create' && !id) {
            console.error(`Navigation error: ID is required for "${action}" action.`);
            return;
        }

        switch (action) {
            case 'show':
                router.get(route(`${baseRouteName}.show`, id), queryParams);
                break;
            case 'edit':
                router.get(route(`${baseRouteName}.edit`, id), queryParams);
                break;
            case 'create':
            default:
                router.get(route(`${baseRouteName}.create`), queryParams);
                break;
        }
    };

    return {
        navigateWithContext,
    };
}
