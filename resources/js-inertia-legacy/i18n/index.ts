import { createI18n } from 'vue-i18n';
import ar from './locales/ar';
import en from './locales/en';

export const LOCALE_KEY = 'erp.locale';

export type AppLocale = 'ar' | 'en';

export function getStoredLocale(): AppLocale {
    const stored = localStorage.getItem(LOCALE_KEY);
    return stored === 'en' ? 'en' : 'ar';
}

export function applyDocumentDirection(locale: AppLocale): void {
    const dir = locale === 'ar' ? 'rtl' : 'ltr';
    document.documentElement.lang = locale;
    document.documentElement.dir = dir;
    document.documentElement.style.setProperty('--erp-direction', dir);
}

export const i18n = createI18n({
    legacy: false,
    locale: typeof window !== 'undefined' ? getStoredLocale() : 'ar',
    fallbackLocale: 'en',
    messages: { ar, en },
});

export function setLocale(locale: AppLocale): void {
    i18n.global.locale.value = locale;
    localStorage.setItem(LOCALE_KEY, locale);
    applyDocumentDirection(locale);
}
