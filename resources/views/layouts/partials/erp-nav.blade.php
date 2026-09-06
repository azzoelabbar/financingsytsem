@php
    $navLink = function (array $patterns): string {
        foreach ($patterns as $pattern) {
            if (request()->routeIs($pattern)) {
                return 'erp-sidebar-link erp-sidebar-link-active';
            }
        }

        return 'erp-sidebar-link';
    };

    $groupOpen = function (array $patterns): bool {
        foreach ($patterns as $pattern) {
            if (request()->routeIs($pattern)) {
                return true;
            }
        }

        return false;
    };

    // Icon path library (single family, 1.5 stroke).
    $ic = [
        'dashboard' => 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z',
        'ar' => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
        'ap' => 'M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12',
        'banking' => 'M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z',
        'expenses' => 'M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0c1.1.128 1.907 1.077 1.907 2.185zM9.75 9h.008v.008H9.75V9zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm4.125 4.5h.008v.008h-.008V13.5zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z',
        'projects' => 'M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0M12 12.75h.008v.008H12v-.008z',
        'investments' => 'M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941',
        'accounting' => 'M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25',
        'fixed_assets' => 'M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9',
        'tax' => 'M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0c1.1.128 1.907 1.077 1.907 2.185z',
        'opening' => 'M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0012 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 01-2.031.352 5.988 5.988 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L18.75 4.971zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 01-2.031.352 5.989 5.989 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L5.25 4.971z',
        'books' => 'M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25',
        'reports' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z',
    ];

    $sections = [
        ['type' => 'link', 'route' => 'dashboard', 'active' => ['dashboard'], 'label' => __('erp.nav.dashboard'), 'icon' => $ic['dashboard']],
        ['type' => 'heading', 'label' => __('erp.nav_group.operations')],
        ['type' => 'group', 'label' => __('erp.nav.ar'), 'patterns' => ['ar.*'], 'icon' => $ic['ar'], 'items' => [
            ['route' => 'ar.customers', 'active' => ['ar.customers', 'ar.customers.*'], 'label' => __('erp.nav.customers')],
            ['route' => 'ar.invoices', 'active' => ['ar.invoices', 'ar.invoices.*'], 'label' => __('erp.nav.sales_invoices')],
            ['route' => 'ar.receipts', 'active' => ['ar.receipts', 'ar.receipts.*'], 'label' => __('erp.nav.receipts')],
            ['route' => 'ar.credit-notes', 'active' => ['ar.credit-notes', 'ar.credit-notes.*'], 'label' => __('erp.nav.credit_notes')],
            ['route' => 'ar.debit-notes', 'active' => ['ar.debit-notes', 'ar.debit-notes.*'], 'label' => __('erp.nav.debit_notes')],
            ['route' => 'ar.statement', 'active' => ['ar.statement'], 'label' => __('erp.nav.customer_statement')],
            ['route' => 'ar.open-items', 'active' => ['ar.open-items'], 'label' => __('erp.nav.open_items')],
            ['route' => 'ar.aging', 'active' => ['ar.aging'], 'label' => __('erp.nav.ar_aging')],
            ['route' => 'ar.reconciliation', 'active' => ['ar.reconciliation'], 'label' => __('erp.nav.ar_reconciliation')],
        ]],
        ['type' => 'group', 'label' => __('erp.nav.ap'), 'patterns' => ['ap.*'], 'icon' => $ic['ap'], 'items' => [
            ['route' => 'ap.suppliers', 'active' => ['ap.suppliers', 'ap.suppliers.*'], 'label' => __('erp.nav.suppliers')],
            ['route' => 'ap.invoices', 'active' => ['ap.invoices', 'ap.invoices.*'], 'label' => __('erp.nav.purchase_invoices')],
            ['route' => 'ap.payments', 'active' => ['ap.payments', 'ap.payments.*'], 'label' => __('erp.nav.supplier_payments')],
            ['route' => 'ap.credit-notes', 'active' => ['ap.credit-notes', 'ap.credit-notes.*'], 'label' => __('erp.nav.credit_notes')],
            ['route' => 'ap.debit-notes', 'active' => ['ap.debit-notes', 'ap.debit-notes.*'], 'label' => __('erp.nav.debit_notes')],
            ['route' => 'ap.statement', 'active' => ['ap.statement'], 'label' => __('erp.nav.supplier_statement')],
            ['route' => 'ap.open-items', 'active' => ['ap.open-items'], 'label' => __('erp.nav.open_items')],
            ['route' => 'ap.aging', 'active' => ['ap.aging'], 'label' => __('erp.nav.ap_aging')],
            ['route' => 'ap.reconciliation', 'active' => ['ap.reconciliation'], 'label' => __('erp.nav.ap_reconciliation')],
        ]],
        ['type' => 'group', 'label' => __('erp.nav.banking'), 'patterns' => ['banking.*'], 'icon' => $ic['banking'], 'items' => [
            ['route' => 'banking.bank-accounts', 'active' => ['banking.bank-accounts', 'banking.bank-accounts.*', 'banking.index'], 'label' => __('erp.banking.bank_accounts')],
            ['route' => 'banking.cash-accounts', 'active' => ['banking.cash-accounts', 'banking.cash-accounts.*'], 'label' => __('erp.banking.cash_accounts')],
            ['route' => 'banking.transactions', 'active' => ['banking.transactions', 'banking.transactions.*'], 'label' => __('erp.banking.transactions')],
            ['route' => 'banking.reconciliation', 'active' => ['banking.reconciliation', 'banking.reconciliation.*'], 'label' => __('erp.banking.reconciliation')],
        ]],
        ['type' => 'link', 'route' => 'expenses.index', 'active' => ['expenses.*'], 'label' => __('erp.nav.expenses'), 'icon' => $ic['expenses']],
        ['type' => 'heading', 'label' => __('erp.nav_group.assets_projects')],
        ['type' => 'link', 'route' => 'projects.index', 'active' => ['projects.*'], 'label' => __('erp.nav.projects'), 'icon' => $ic['projects']],
        ['type' => 'link', 'route' => 'investments.index', 'active' => ['investments.*'], 'label' => __('erp.nav.investments'), 'icon' => $ic['investments']],
        ['type' => 'group', 'label' => __('erp.nav.fixed_assets'), 'patterns' => ['assets.*'], 'icon' => $ic['fixed_assets'], 'items' => [
            ['route' => 'assets.index', 'active' => ['assets.index', 'assets.create', 'assets.show'], 'label' => __('erp.nav.assets')],
            ['route' => 'assets.depreciation', 'active' => ['assets.depreciation'], 'label' => __('erp.nav.depreciation')],
        ]],
        ['type' => 'heading', 'label' => __('erp.nav_group.accounting')],
        ['type' => 'group', 'label' => __('erp.nav.gl'), 'patterns' => ['gl.*'], 'icon' => $ic['accounting'], 'items' => [
            ['route' => 'gl.accounts', 'active' => ['gl.accounts'], 'label' => __('erp.nav.accounts')],
            ['route' => 'gl.journals', 'active' => ['gl.journals', 'gl.journals.*'], 'label' => __('erp.nav.journals')],
            ['route' => 'gl.ledger', 'active' => ['gl.ledger'], 'label' => __('erp.nav.gl_ledger')],
            ['route' => 'gl.trial-balance', 'active' => ['gl.trial-balance'], 'label' => __('erp.nav.trial_balance')],
            ['route' => 'gl.account-balances', 'active' => ['gl.account-balances'], 'label' => __('erp.nav.account_balances')],
            ['route' => 'gl.periods', 'active' => ['gl.periods'], 'label' => __('erp.nav.periods')],
        ]],
        ['type' => 'group', 'label' => __('erp.nav.tax'), 'patterns' => ['tax.*'], 'icon' => $ic['tax'], 'items' => [
            ['route' => 'tax.index', 'active' => ['tax.index', 'tax.create'], 'label' => __('erp.nav.tax_codes')],
            ['route' => 'tax.rules', 'active' => ['tax.rules', 'tax.rules.*'], 'label' => __('erp.nav.tax_rules')],
        ]],
        ['type' => 'link', 'route' => 'opening-balances.index', 'active' => ['opening-balances.*'], 'label' => __('erp.nav.opening_balances'), 'icon' => $ic['opening']],
        ['type' => 'link', 'route' => 'books.index', 'active' => ['books.*'], 'label' => __('erp.nav.books'), 'icon' => $ic['books']],
        ['type' => 'heading', 'label' => __('erp.nav_group.reporting')],
        ['type' => 'group', 'label' => __('erp.nav.reports'), 'patterns' => ['reports.*'], 'icon' => $ic['reports'], 'items' => [
            ['route' => 'reports.balance-sheet', 'active' => ['reports.balance-sheet'], 'label' => __('erp.nav.balance_sheet')],
            ['route' => 'reports.profit-loss', 'active' => ['reports.profit-loss'], 'label' => __('erp.nav.profit_loss')],
            ['route' => 'reports.cash-flow', 'active' => ['reports.cash-flow'], 'label' => __('erp.nav.cash_flow')],
            ['route' => 'reports.oci', 'active' => ['reports.oci'], 'label' => __('erp.nav.oci')],
            ['route' => 'reports.cash-forecast', 'active' => ['reports.cash-forecast'], 'label' => __('erp.nav.cash_forecast')],
            ['route' => 'reports.budget-vs-actual', 'active' => ['reports.budget-vs-actual'], 'label' => __('erp.nav.budget_vs_actual')],
            ['route' => 'reports.management-pack', 'active' => ['reports.management-pack'], 'label' => __('erp.nav.management_pack')],
        ]],
    ];
@endphp

@foreach ($sections as $section)
    @if ($section['type'] === 'heading')
        <p class="erp-nav-group-label">{{ $section['label'] }}</p>
    @elseif ($section['type'] === 'link')
        <a href="{{ route($section['route']) }}" class="{{ $navLink($section['active']) }}" wire:navigate>
            <svg class="h-4 w-4 shrink-0 opacity-80" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $section['icon'] }}" /></svg>
            {{ $section['label'] }}
        </a>
    @else
        <div data-nav-group="{{ $section['label'] }}" x-data="{ open: {{ $groupOpen($section['patterns']) ? 'true' : 'false' }} }" class="pt-0.5">
            <button type="button" @click="open = !open" :aria-expanded="open" class="mizan-nav-trigger">
                <svg class="h-4 w-4 shrink-0 opacity-80" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $section['icon'] }}" /></svg>
                <span class="flex-1 text-start font-medium">{{ $section['label'] }}</span>
                <svg class="h-3.5 w-3.5 shrink-0 text-muted-foreground transition-transform" :class="open ? 'rotate-180' : ''" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
            </button>
            <div x-show="open" x-collapse class="mt-0.5 space-y-0.5 ps-4">
                @foreach ($section['items'] as $item)
                    <a href="{{ route($item['route']) }}" class="{{ $navLink($item['active']) }} !text-[0.8125rem]" wire:navigate>{{ $item['label'] }}</a>
                @endforeach
            </div>
        </div>
    @endif
@endforeach
