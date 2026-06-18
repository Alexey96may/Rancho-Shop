import { App } from 'vue';

import AppContainer from '@/Components/UI/AppContainer.vue';
import AppImage from '@/Components/UI/AppImage.vue';

export default {
    install(app: App) {
        app.component('AppContainer', AppContainer);
        app.component('AppImage', AppImage);
    },
};
