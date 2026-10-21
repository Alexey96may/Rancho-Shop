<script setup lang="ts">
    import { onMounted, watch } from 'vue';

    import { usePage } from '@inertiajs/vue3';

    import { useCookieConsent } from '@/composables/useCookieConsent';

    interface YmFunction {
        (id: number, action: string, ...args: unknown[]): void;
        a?: unknown[];
        l?: number;
    }

    type YmWindow = Window & {
        ym?: YmFunction;
        ymConfig?: { id: number };
    };

    const page = usePage();
    const { consent } = useCookieConsent();

    const metrikaId = page.props.metrikaId as number | null;

    const loadMetrika = (id: number) => {
        if (typeof window === 'undefined') return;

        const w = window as YmWindow;
        if (w.ym) return;

        // Создаём ym, если нет
        const fn = function (...args: unknown[]) {
            (fn.a = fn.a || []).push(args);
        } as YmFunction;
        fn.l = Date.now();
        w.ym = fn;

        // Подключаем скрипт (если ещё не подключён)
        const scriptSrc = `https://mc.yandex.ru/metrika/tag.js?id=${id}`;
        const exists = Array.from(document.scripts).some((s) => s.src === scriptSrc);
        if (!exists) {
            const k = document.createElement('script');
            k.async = true;
            k.src = scriptSrc;
            document.head.appendChild(k);
        }

        // Теперь w.ym точно YmFunction, и TS это видит (без сужения)
        w.ym(id, 'init', {
            clickmap: true,
            trackLinks: true,
            accurateTrackBounce: true,
            referrer: document.referrer,
            url: location.href,
        });

        w.ymConfig = { id };
    };

    onMounted(() => {
        if (consent.value === true && metrikaId) {
            loadMetrika(metrikaId);
        }
    });

    watch(consent, (value) => {
        if (value === true && metrikaId && !(window as YmWindow).ym) {
            loadMetrika(metrikaId);
        }
    });
</script>

<template></template>
