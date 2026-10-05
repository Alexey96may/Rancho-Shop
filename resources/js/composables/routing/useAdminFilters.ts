import { onUnmounted, ref } from 'vue';

import { InertiaForm } from '@inertiajs/vue3';

import { debounce } from 'lodash-es';

export function useAdminFilters() {
    const isFiltering = ref(false);
    let myDelay = ref(400);

    /**
     * Sends filters with a delay (Debounce)
     *
     * @param form      The Inertia form object (useForm)
     * @param routeName The name of the route to which the GET request should be sent (e.g., 'admin.animals.index')
     * @param delay     The delay duration in milliseconds (default: 400)
     */
    const submitFilters = debounce(
        <T extends object>(form: InertiaForm<T>, routeName: string, delay: number = 400) => {
            myDelay.value = delay;
            isFiltering.value = true;

            form.get(route(routeName), {
                preserveState: true,
                replace: true,
                preserveScroll: true,
                onFinish: () => {
                    isFiltering.value = false;
                },
            });
        },
        myDelay.value,
    );

    /**
     * Clears filters and resets the form.
     *
     * @param form The Inertia form object.
     */
    const clearFilters = <T extends object>(form: InertiaForm<T>) => {
        const emptyValues = Object.keys(form.data()).reduce((acc, key) => {
            acc[key as keyof T] = (Array.isArray(form[key as keyof T]) ? [] : null) as any;
            return acc;
        }, {} as Partial<T>);

        form.defaults(emptyValues);
        form.reset();
    };

    onUnmounted(() => {
        submitFilters.cancel();
    });

    return {
        isFiltering,
        submitFilters,
        clearFilters,
    };
}
