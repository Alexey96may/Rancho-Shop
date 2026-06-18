import { computed } from 'vue';

import { usePage } from '@inertiajs/vue3';

export function useNavigation() {
    const page = usePage();

    const currentQuery = computed(() => {
        const url = page.url;
        const searchIndex = url.indexOf('?');
        return searchIndex !== -1 ? url.slice(searchIndex) : '';
    });

    const backParams = computed(() => ({
        back: currentQuery.value,
    }));

    return {
        currentQuery,
        backParams,
    };
}
