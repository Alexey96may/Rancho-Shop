import { type DefineComponent, createSSRApp, h } from 'vue';

import { createInertiaApp } from '@inertiajs/vue3';
import createServer from '@inertiajs/vue3/server';

import { renderToString } from '@vue/server-renderer';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createPinia } from 'pinia';

import Directives from '@/Plugins/directives';
import GlobalComponents from '@/Plugins/global-components';
import { Ziggy } from '@/ziggy';

import { ZiggyVue } from '../../vendor/tightenco/ziggy';

globalThis.Ziggy = Ziggy;
const appName = import.meta.env.VITE_APP_NAME || 'Молочная Долина';

createServer((page) =>
    createInertiaApp({
        page,
        // @ts-expect-error — Inertia 2 SSR overload not inferred
        render: renderToString,
        title: (title) => `${title} - ${appName}`,
        resolve: (name) =>
            resolvePageComponent(
                `./Pages/${name}.vue`,
                import.meta.glob<DefineComponent>('./Pages/**/*.vue'),
            ),
        setup({ App, props, plugin }) {
            const pinia = createPinia();
            return createSSRApp({ render: () => h(App, props) })
                .use(plugin)
                .use(pinia)
                .use(Directives)
                .use(GlobalComponents)
                .use(ZiggyVue);
        },
    }),
);
