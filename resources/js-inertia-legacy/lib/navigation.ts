import type { ComposerTranslation } from 'vue-i18n';

export type ErpNavItem = {
    title: string;
    href: string;
    permission?: string;
};

export type ErpNavGroup = {
    title: string;
    items: ErpNavItem[];
};

export function buildErpNav(t: ComposerTranslation): ErpNavGroup[] {
    return [
        {
            title: t('nav.platform'),
            items: [{ title: t('nav.home'), href: '/dashboard' }],
        },
        {
            title: t('nav.ar'),
            items: [
                {
                    title: t('nav.customers'),
                    href: '/ar/customers',
                    permission: 'ar.read',
                },
                {
                    title: t('nav.salesInvoices'),
                    href: '/ar/sales-invoices',
                    permission: 'ar.read',
                },
                {
                    title: t('nav.receipts'),
                    href: '/ar/receipts',
                    permission: 'ar.read',
                },
                {
                    title: t('nav.creditNotes'),
                    href: '/ar/credit-notes',
                    permission: 'ar.read',
                },
                {
                    title: t('nav.debitNotes'),
                    href: '/ar/debit-notes',
                    permission: 'ar.read',
                },
                {
                    title: t('nav.arAging'),
                    href: '/ar/aging',
                    permission: 'ar.read',
                },
                {
                    title: t('nav.arOpenItems'),
                    href: '/ar/open-items',
                    permission: 'ar.read',
                },
                {
                    title: t('nav.arReconciliation'),
                    href: '/ar/reconciliation',
                    permission: 'ar.read',
                },
            ],
        },
        {
            title: t('nav.ap'),
            items: [
                {
                    title: t('nav.suppliers'),
                    href: '/ap/suppliers',
                    permission: 'ap.read',
                },
                {
                    title: t('nav.purchaseInvoices'),
                    href: '/ap/purchase-invoices',
                    permission: 'ap.read',
                },
                {
                    title: t('nav.payments'),
                    href: '/ap/supplier-payments',
                    permission: 'ap.read',
                },
                {
                    title: t('nav.apAging'),
                    href: '/ap/aging',
                    permission: 'ap.read',
                },
                {
                    title: t('nav.apOpenItems'),
                    href: '/ap/open-items',
                    permission: 'ap.read',
                },
                {
                    title: t('nav.apReconciliation'),
                    href: '/ap/reconciliation',
                    permission: 'ap.read',
                },
            ],
        },
        {
            title: t('nav.gl'),
            items: [
                {
                    title: t('nav.accounts'),
                    href: '/gl/accounts',
                    permission: 'gl.read',
                },
                {
                    title: t('nav.journals'),
                    href: '/gl/journals',
                    permission: 'gl.read',
                },
                {
                    title: t('nav.generalLedger'),
                    href: '/gl/general-ledger',
                    permission: 'gl.read',
                },
                {
                    title: t('nav.trialBalance'),
                    href: '/gl/trial-balance',
                    permission: 'gl.read',
                },
                {
                    title: t('nav.accountBalances'),
                    href: '/gl/account-balances',
                    permission: 'gl.read',
                },
                {
                    title: t('nav.periods'),
                    href: '/gl/periods',
                    permission: 'gl.read',
                },
            ],
        },
        {
            title: t('nav.banking'),
            items: [{ title: t('nav.banking'), href: '/banking' }],
        },
        {
            title: t('nav.expenses'),
            items: [
                {
                    title: t('nav.expenses'),
                    href: '/expenses',
                    permission: 'expenses.read',
                },
            ],
        },
        {
            title: t('nav.projects'),
            items: [
                {
                    title: t('nav.projects'),
                    href: '/projects',
                    permission: 'projects.read',
                },
            ],
        },
        {
            title: t('nav.investments'),
            items: [
                {
                    title: t('nav.investments'),
                    href: '/investments',
                    permission: 'investments.read',
                },
            ],
        },
        {
            title: t('nav.tax'),
            items: [
                {
                    title: t('nav.tax'),
                    href: '/tax',
                    permission: 'tax.read',
                },
            ],
        },
        {
            title: t('nav.openingBalances'),
            items: [
                {
                    title: t('nav.openingBalances'),
                    href: '/opening-balances',
                    permission: 'opening_balances.read',
                },
            ],
        },
        {
            title: t('nav.books'),
            items: [
                {
                    title: t('nav.books'),
                    href: '/books',
                    permission: 'books.read',
                },
            ],
        },
        {
            title: t('nav.reports'),
            items: [
                {
                    title: t('nav.balanceSheet'),
                    href: '/reports/balance-sheet',
                    permission: 'reports.read',
                },
                {
                    title: t('nav.profitLoss'),
                    href: '/reports/profit-loss',
                    permission: 'reports.read',
                },
                {
                    title: t('nav.cashFlow'),
                    href: '/reports/cash-flow',
                    permission: 'reports.read',
                },
                {
                    title: t('nav.managementPack'),
                    href: '/reports/management-pack',
                    permission: 'reports.read',
                },
            ],
        },
    ];
}
