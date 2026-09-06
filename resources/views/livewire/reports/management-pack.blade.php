<div>
    @php
        $cur = $company?->functional_currency ?? '';
        $bs = $pack['balance_sheet'] ?? [];
        $bsTotals = $bs['totals'] ?? [];
        $incomeStatement = $pack['income_statement'] ?? [];
        $cf = $pack['cash_flow'] ?? [];
        $netProfit = (float) ($incomeStatement['net_profit'] ?? 0);
        $netCash = (float) ($cf['net'] ?? 0);

        $sections = [
            [
                'label' => __('erp.nav.balance_sheet'),
                'hint' => __('erp.statement.balance_sheet_hint'),
                'route' => route('reports.balance-sheet'),
                'rows' => [
                    [__('erp.reports.total_assets'), $bsTotals['assets'] ?? '0'],
                    [__('erp.reports.total_liabilities'), $bsTotals['liabilities'] ?? '0'],
                    [__('erp.reports.total_equity'), $bsTotals['equity'] ?? '0'],
                ],
                'footLabel' => __('erp.statement.current_result'),
                'footAmount' => $bsTotals['net_result'] ?? '0',
                'footTone' => null,
            ],
            [
                'label' => __('erp.nav.profit_loss'),
                'hint' => __('erp.statement.pl_hint'),
                'route' => route('reports.profit-loss'),
                'rows' => [
                    [__('erp.statement.revenue'), $incomeStatement['revenue'] ?? '0'],
                    [__('erp.statement.expenses'), $incomeStatement['expenses'] ?? '0'],
                ],
                'footLabel' => __('erp.statement.net_income'),
                'footAmount' => $incomeStatement['net_profit'] ?? '0',
                'footTone' => $netProfit >= 0 ? 'success' : 'danger',
            ],
            [
                'label' => __('erp.nav.cash_flow'),
                'hint' => __('erp.reports.cash_flow_hint'),
                'route' => route('reports.cash-flow'),
                'rows' => [
                    [__('erp.reports.cash_in'), $cf['inflows'] ?? '0'],
                    [__('erp.reports.cash_out'), $cf['outflows'] ?? '0'],
                    [__('erp.reports.activity_operating'), $cf['operating'] ?? '0'],
                    [__('erp.reports.activity_investing'), $cf['investing'] ?? '0'],
                    [__('erp.reports.activity_financing'), $cf['financing'] ?? '0'],
                ],
                'footLabel' => __('erp.reports.net_cash_change'),
                'footAmount' => $cf['net'] ?? '0',
                'footTone' => $netCash >= 0 ? 'success' : 'danger',
            ],
        ];
    @endphp

    <x-ui.report-shell
        :title="__('erp.nav.management_pack')"
        :description="__('erp.reports.pack_hint')"
        :breadcrumbs="[['label' => __('erp.nav.reports')], ['label' => __('erp.nav.management_pack')]]"
        :company="$company"
        :book="$book"
        :period="$period"
        :as-of="$asOf"
    >
        <x-slot:filters>
            <x-ui.date-filter model="asOf" />
        </x-slot:filters>

        @if ($pack === [])
            <x-ui.empty-state :title="__('erp.reports.pack_empty_title')" :message="__('erp.reports.pack_empty_hint')" />
        @else
            <div class="grid gap-5 lg:grid-cols-3">
                @foreach ($sections as $section)
                    @php
                        $footStyle = $section['footTone'] === null
                            ? 'background: var(--surface-sunken)'
                            : 'background: '.($section['footTone'] === 'success' ? 'var(--success-muted)' : 'var(--danger-muted)');
                    @endphp
                    <div class="flex flex-col overflow-hidden rounded-lg border border-border bg-card">
                        <div class="border-b border-border bg-[var(--brand-50)] px-5 py-3">
                            <h3 class="text-[0.875rem] font-semibold text-[var(--brand-900)]">{{ $section['label'] }}</h3>
                            <p class="mt-0.5 text-xs leading-relaxed text-muted-foreground">{{ $section['hint'] }}</p>
                        </div>

                        <dl class="flex-1 divide-y divide-border px-5">
                            @foreach ($section['rows'] as [$label, $amount])
                                <div class="flex items-center justify-between gap-4 py-2.5">
                                    <dt class="text-[0.8125rem] text-muted-foreground">{{ $label }}</dt>
                                    <dd><x-ui.money :amount="$amount" :negative="(float) $amount < 0" /></dd>
                                </div>
                            @endforeach
                        </dl>

                        <div class="flex items-center justify-between gap-4 border-t border-border px-5 py-3.5" style="{{ $footStyle }}">
                            <span class="text-[0.8125rem] font-semibold">{{ $section['footLabel'] }}</span>
                            <x-ui.money :amount="$section['footAmount']" :currency="$cur" :negative="(float) $section['footAmount'] < 0" size="lg" />
                        </div>

                        <div class="border-t border-border px-5 py-3">
                            <a href="{{ $section['route'] }}" wire:navigate class="text-[0.8125rem] font-medium text-[var(--brand-600)] hover:underline">{{ __('erp.reports.open_full_report') }}</a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-ui.report-shell>
</div>
