import type { ObjectDirective } from 'vue';

const observers = new WeakMap<HTMLElement, IntersectionObserver>();

const BASE_CLASS = 'scroll-reveal';
const VISIBLE_CLASS = 'scroll-reveal--visible';

export const vScrollReveal: ObjectDirective<HTMLElement> = {
    getSSRProps() {
        return { class: 'scroll-reveal' };
    },

    beforeMount(el) {
        el.classList.add(BASE_CLASS);
    },

    mounted(el) {
        if (typeof window === 'undefined') return;

        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            el.classList.remove(BASE_CLASS);
            return;
        }

        const observer = new IntersectionObserver(
            ([entry], obs) => {
                if (!entry.isIntersecting) return;

                el.classList.add(VISIBLE_CLASS);

                const onEnd = (e: TransitionEvent) => {
                    if (e.target !== el) return;
                    el.classList.remove(BASE_CLASS, VISIBLE_CLASS);
                    el.removeEventListener('transitionend', onEnd);
                };
                el.addEventListener('transitionend', onEnd);

                obs.disconnect();
                observers.delete(el);
            },
            {
                threshold: 0,
                rootMargin: '0px 0px -100px 0px',
            },
        );

        observer.observe(el);
        observers.set(el, observer);
    },

    unmounted(el) {
        const observer = observers.get(el);
        if (observer) {
            observer.disconnect();
            observers.delete(el);
        }
        el.classList.remove(BASE_CLASS, VISIBLE_CLASS);
    },
};
