import { PageProps as InertiaPageProps } from '@inertiajs/core';

import { AxiosInstance } from 'axios';
import { route as ziggyRoute } from 'ziggy-js';

import AppContainer from '@/Components/UI/AppContainer.vue';
import AppImage from '@/Components/UI/AppImage.vue';

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
        AppImage: typeof AppImage;
    }
}

declare module '@heroicons/vue/20/solid' {
    import { FunctionalComponent, HTMLAttributes, VNodeProps } from 'vue';
    const content: FunctionalComponent<HTMLAttributes & VNodeProps>;
    export default content;
    export const ChevronUpDownIcon: FunctionalComponent<HTMLAttributes & VNodeProps>;
    export const CheckIcon: FunctionalComponent<HTMLAttributes & VNodeProps>;
}

declare module '@heroicons/vue/24/outline' {
    import { FunctionalComponent, HTMLAttributes, VNodeProps } from 'vue';
    const content: FunctionalComponent<HTMLAttributes & VNodeProps>;
    export default content;
}
