import { describe, expect, it } from 'vitest';
import { formatDate, formatMoney, formatNumber } from '../format';

describe('formatMoney', () => {
    it('formats numeric strings without calculating', () => {
        const ar = formatMoney('1250.5', 'LYD', 'ar');
        const en = formatMoney('1250.5', 'LYD', 'en');
        expect(ar).toContain('1');
        expect(en).toContain('1');
    });

    it('handles empty values', () => {
        expect(formatMoney(null)).toBe('—');
        expect(formatMoney(undefined)).toBe('—');
    });
});

describe('formatNumber', () => {
    it('formats decimals', () => {
        expect(formatNumber(10, 'en', 2)).toMatch(/10/);
    });
});

describe('formatDate', () => {
    it('formats iso dates', () => {
        expect(formatDate('2026-03-15', 'en')).not.toBe('—');
        expect(formatDate(null)).toBe('—');
    });
});
