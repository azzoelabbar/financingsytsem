import { describe, expect, it } from 'vitest';
import { getStoredLocale } from '@/i18n';

describe('locale defaults', () => {
    it('defaults to arabic when storage empty', () => {
        localStorage.removeItem('erp.locale');
        expect(getStoredLocale()).toBe('ar');
    });

    it('reads english preference', () => {
        localStorage.setItem('erp.locale', 'en');
        expect(getStoredLocale()).toBe('en');
        localStorage.removeItem('erp.locale');
    });
});
