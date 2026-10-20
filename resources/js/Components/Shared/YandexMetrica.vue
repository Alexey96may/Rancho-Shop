<script setup lang="ts">
    import { onMounted, watch } from 'vue';

    import { usePage } from '@inertiajs/vue3';

    import { useCookieConsent } from '@/composables/useCookieConsent';

    const page = usePage();
    const { consent } = useCookieConsent();

    const metrikaId = page.props.metrikaId as number | null;

    const loadMetrika = (id: number) => {
        if (typeof window === 'undefined') return;
        if ((window as any).ym) return; // уже загружена

        // Стандартный сниппет Яндекс.Метрики
        (function (m: any, e: Document, t: string, r: string, i: string, k?: any, a?: any) {
            m[i] =
                m[i] ||
                function () {
                    (m[i].a = m[i].a || []).push(arguments);
                };
            m[i].l = 1 * Date.now();
            for (let j = 0; j < e.scripts.length; j++) {
                if (e.scripts[j].src === r) return;
            }
            k = e.createElement(t);
            a = e.getElementsByTagName(t)[0];
            k.async = 1;
            k.src = r;
            a.parentNode?.insertBefore(k, a);
        })(window, document, 'script', `https://mc.yandex.ru/metrika/tag.js?id=${id}`, 'ym');

        (window as any).ym(id, 'init', {
            clickmap: true,
            trackLinks: true,
            accurateTrackBounce: true,
            referrer: document.referrer,
            url: location.href,
        });

        (window as any).ymConfig = { id };
    };

    onMounted(() => {
        if (consent.value === true && metrikaId) {
            loadMetrika(metrikaId);
        }
    });

    // Если пользователь нажал «Принять» уже после загрузки страницы
    watch(consent, (value) => {
        if (value === true && metrikaId && !(window as any).ym) {
            loadMetrika(metrikaId);
        }
    });
</script>

<template></template>
