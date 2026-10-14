// resources/js/composables/useMediaQuery.ts
import { onBeforeUnmount, onMounted, ref } from 'vue';

export function useMediaQuery(query: string) {
    const matches = ref(false);

    let mql: MediaQueryList | null = null;

    const update = (e: MediaQueryListEvent | MediaQueryList) => {
        matches.value = e.matches;
    };

    onMounted(() => {
        if (typeof window === 'undefined') return;
        mql = window.matchMedia(query);
        update(mql);
        mql.addEventListener('change', update);
    });

    onBeforeUnmount(() => {
        mql?.removeEventListener('change', update);
    });

    return matches;
}
