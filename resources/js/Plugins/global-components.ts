import { App } from 'vue';

import AppContainer from '@/Components/UI/AppContainer.vue';

export default {
    install(app: App) {
        app.component('AppContainer', AppContainer);
    },
};
