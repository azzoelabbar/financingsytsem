import { createInertiaApp } from '@inertiajs/vue3';
import { createPinia } from 'pinia';
import { createApp, h, type DefineComponent } from 'vue';
import { initializeTheme } from '@/composables/useAppearance';
import { applyDocumentDirection, getStoredLocale, i18n } from '@/i18n';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { initializeFlashToast } from '@/lib/flashToast';

const appName = import.meta.env.VITE_APP_NAME || 'Mizan';

applyDocumentDirection(getStoredLocale());

void createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            case name === 'Welcome':
                return null;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },
    progress: {
        color: '#4B5563',
    },
    setup({ el, App, props, plugin }) {
        if (!el) {
            throw new Error('Inertia root element missing');
        }

        createApp({ render: () => h(App as DefineComponent, props) })
            .use(plugin)
            .use(createPinia())
            .use(i18n)
            .mount(el);
    },
});

initializeTheme();
initializeFlashToast();
