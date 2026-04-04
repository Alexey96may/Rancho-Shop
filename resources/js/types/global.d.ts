import { PageProps as InertiaPageProps } from '@inertiajs/core';

import { AxiosInstance } from 'axios';
import { route as ziggyRoute } from 'ziggy-js';

import AppContainer from '@/Components/AppContainer.vue';

import { PageProps as AppPageProps } from './';

declare global {
    interface Window {
        axios: AxiosInstance;
    }

    /* eslint-disable no-var */
    var route: typeof ziggyRoute;
}

declare module 'vue' {
    interface ComponentCustomProperties {
        route: typeof ziggyRoute;
    }
}

declare module '@inertiajs/core' {
    interface PageProps extends InertiaPageProps, AppPageProps {}
}

declare module '@vue/runtime-core' {
    export interface GlobalComponents {
        AppContainer: typeof AppContainer;
    }
}
