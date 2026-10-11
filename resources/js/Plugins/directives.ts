import type { App } from 'vue';

import { vScrollReveal } from '@/directives/vScrollReveal';

export default {
    install(app: App) {
        app.directive('scroll-reveal', vScrollReveal);
    },
};
