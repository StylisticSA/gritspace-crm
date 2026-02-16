import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { Inertia } from '@inertiajs/inertia';
import { InertiaProgress } from '@inertiajs/progress';
import VueTheMask from 'vue-the-mask';

// PrimeVue & Theme
import PrimeVue from 'primevue/config';
import Aura from '@primeuix/themes/aura';

const appName = import.meta.env.VITE_APP_NAME || 'Gritspace';

// Handle Auth State
Inertia.on('success', event => {
    const isAuthenticated = event.detail.page.props.auth.user != null;
    window.localStorage.setItem('isAuthenticated', isAuthenticated);
});

// Prevent navigation if unauthenticated
window.addEventListener('popstate', event => {
    if (window.localStorage.getItem('isAuthenticated') === 'false') {
        event.stopImmediatePropagation();
        Inertia.replace('/login');
    }
});

// Handle 403 errors
Inertia.on('error', errors => {
    if (errors.status === 403) {
        alert('You are not authorized to view this page.');
        Inertia.visit('/dashboard');
    }
});

// Inertia app setup
createInertiaApp({
    title: title => `${title} - ${appName}`,
    resolve: name => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(VueTheMask)
            .use(PrimeVue, {
                theme: {
                    preset: Aura,
                    options: {
                        darkModeSelector: false || 'none',
                    },
                },
            })
            .mount(el);
    },

    progress: false,
});
InertiaProgress.init({
    color: '#F2676A',
    showSpinner: true,
});
