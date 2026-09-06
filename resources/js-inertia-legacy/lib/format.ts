/**
 * Display-only formatting helpers. Never recalculate accounting amounts.
 */

export function formatMoney(
    amount: string | number | null | undefined,
    currency = 'LYD',
    locale = 'ar',
): string {
    if (amount === null || amount === undefined || amount === '') {
        return '—';
    }
    const n = typeof amount === 'number' ? amount : Number(amount);
    if (Number.isNaN(n)) {
        return String(amount);
    }
    try {
        return new Intl.NumberFormat(locale === 'ar' ? 'ar-LY' : 'en-LY', {
            style: 'currency',
            currency,
            minimumFractionDigits: 2,
            maximumFractionDigits: 3,
        }).format(n);
    } catch {
        return `${n.toFixed(2)} ${currency}`;
    }
}

export function formatNumber(
    amount: string | number | null | undefined,
    locale = 'ar',
    fractionDigits = 2,
): string {
    if (amount === null || amount === undefined || amount === '') {
        return '—';
    }
    const n = typeof amount === 'number' ? amount : Number(amount);
    if (Number.isNaN(n)) {
        return String(amount);
    }
    return new Intl.NumberFormat(locale === 'ar' ? 'ar' : 'en', {
        minimumFractionDigits: fractionDigits,
        maximumFractionDigits: fractionDigits,
    }).format(n);
}

export function formatDate(
    value: string | null | undefined,
    locale = 'ar',
): string {
    if (!value) {
        return '—';
    }
    const d = new Date(value);
    if (Number.isNaN(d.getTime())) {
        return value;
    }
    return new Intl.DateTimeFormat(locale === 'ar' ? 'ar' : 'en-GB', {
        year: 'numeric',
        month: 'short',
        day: '2-digit',
    }).format(d);
}
