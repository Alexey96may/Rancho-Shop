import { onMounted, onUnmounted, ref } from 'vue';

/**
 * Определяет, поддерживает ли устройство реальный hover (мышь).
 * На тач-устройствах — false.
 */
export function useCanHover() {
    const canHover = ref(true);

    let mq: MediaQueryList | null = null;
    let onChange: ((e: MediaQueryListEvent) => void) | null = null;

    onMounted(() => {
        if (typeof window === 'undefined') return;

        mq = window.matchMedia('(hover: hover) and (pointer: fine)');
        canHover.value = mq.matches;

        onChange = (e: MediaQueryListEvent) => {
            canHover.value = e.matches;
        };

        mq.addEventListener('change', onChange);
    });

    onUnmounted(() => {
        if (mq && onChange) {
            mq.removeEventListener('change', onChange);
        }
    });

    return { canHover };
}
