import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import VueKonva from 'vue-konva';

createInertiaApp({
    title: (title) => title ? `${title} | Magma Decorador` : 'Magma Decorador',
    resolve: name => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
        return pages[`./Pages/${name}.vue`];
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(VueKonva)
            .mount(el);
    },
});
