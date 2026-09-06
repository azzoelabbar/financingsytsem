import { defineStore } from 'pinia';
import { computed } from 'vue';
import {
    applyDocumentDirection,
    getStoredLocale,
    setLocale,
    type AppLocale,
} from '@/i18n';
import { i18n } from '@/i18n';

export const useLocaleStore = defineStore('locale', () => {
    const locale = computed(() => i18n.global.locale.value as AppLocale);
    const isRtl = computed(() => locale.value === 'ar');

    function init(): void {
        const stored = getStoredLocale();
        setLocale(stored);
    }

    function toggle(): void {
        setLocale(locale.value === 'ar' ? 'en' : 'ar');
    }

    function set(next: AppLocale): void {
        setLocale(next);
        applyDocumentDirection(next);
    }

    return { locale, isRtl, init, toggle, set };
});
