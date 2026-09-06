<div>
    @php
        $lines = $report['lines'] ?? [];
        $totals = $report['totals'] ?? [];
        $bySection = collect($lines)->groupBy('group');
        $cur = $company?->functional_currency ?? '';
        $balanced = $report['balanced'] ?? false;
        $sections = [
            ['key' => 'asset', 'label' => __('erp.statement.assets'), 'total' => $totals['assets'] ?? '0'],
            ['key' => 'liability', 'label' => __('erp.statement.liabilities'), 'total' => $totals['liabilities'] ?? '0'],
            ['key' => 'equity', 'label' => __('erp.statement.equity'), 'total' => $totals['equity'] ?? '0', 'resultRow' => $totals['net_result'] ?? '0'],
        ];
    @endphp

    <x-ui.report-shell
        :title="__('erp.nav.balance_sheet')"
        :description="__('erp.statement.balance_sheet_hint')"
        :breadcrumbs="[['label' => __('erp.nav.reports')], ['label' => __('erp.nav.balance_sheet')]]"
        :company="$company"
        :book="$book"
        :period="$period"
        :as-of="$asOf"
    >
        <x-slot:filters>
            <x-ui.date-filter model="asOf" />
        </x-slot:filters>

        @if ($lines === [])
            <x-ui.empty-state :title="__('erp.statement.empty_title')" :message="__('erp.statement.empty_hint')" />
        @else
            <div class="mx-auto max-w-3xl">
                <x-ui.statement-block>
                    @foreach ($sections as $section)
                        @php $sectionLines = $bySection[$section['key']] ?? collect(); @endphp
                        <div class="border-b border-border last:border-b-0">
                            <div class="flex items-center justify-between bg-surface-sunken px-5 py-2.5">
                                <h3 class="erp-section-title text-foreground">{{ $section['label'] }}</h3>
                            </div>
                            <dl>
                                @forelse ($sectionLines as $line)
                                    <div class="flex items-center justify-between gap-4 px-5 py-1.5 transition-colors hover:bg-[var(--brand-50)]">
                                        <dt class="flex min-w-0 flex-col items-baseline gap-0.5 sm:flex-row sm:gap-2.5">
                                            <span class="shrink-0 font-mono text-xs text-muted-foreground" dir="ltr">{{ $line->code }}</span>
                                            <span class="break-words text-[0.8125rem] text-foreground">{{ $line->nameAr }}</span>
                                        </dt>
                                        <dd class="shrink-0"><x-ui.money :amount="$line->amount" /></dd>
                                    </div>
                                @empty
                                    @if (($section['resultRow'] ?? null) === null || (float) ($section['resultRow'] ?? 0) == 0)
                                        <div class="px-5 py-2 text-[0.8125rem] text-muted-foreground">{{ __('erp.no_data') }}</div>
                                    @endif
                                @endforelse

                                @if (($section['resultRow'] ?? null) !== null && (float) $section['resultRow'] != 0)
                                    <div class="flex items-center justify-between gap-4 px-5 py-1.5">
                                        <dt class="text-[0.8125rem] italic text-muted-foreground">{{ __('erp.statement.current_result') }}</dt>
                                        <dd><x-ui.money :amount="$section['resultRow']" /></dd>
                                    </div>
                                @endif

                                <div class="flex items-center justify-between gap-4 border-t border-border bg-surface-sunken/60 px-5 py-2.5">
                                    <dt class="text-[0.8125rem] font-semibold">{{ __('erp.statement.total_prefix') }} {{ $section['label'] }}</dt>
                                    <dd><x-ui.money :amount="$section['total']" :currency="$cur" size="lg" /></dd>
                                </div>
                            </dl>
                        </div>
                    @endforeach
                </x-ui.statement-block>

                {{-- The accounting equation is the report's own proof of correctness. --}}
                @php $liabPlusEq = \App\Services\Accounting\Support\Decimal::add($totals['liabilities'] ?? '0', $totals['equity'] ?? '0'); @endphp
                <div class="mt-4 flex flex-wrap items-center justify-center gap-x-3 gap-y-1.5 rounded-lg border px-5 py-3.5 text-[0.8125rem]"
                     style="border-color: {{ $balanced ? 'var(--success-line)' : 'var(--danger-line)' }}; background: {{ $balanced ? 'var(--success-muted)' : 'var(--danger-muted)' }}">
                    <span class="font-medium text-muted-foreground">{{ __('erp.statement.equation') }}</span>
                    <x-ui.money :amount="$totals['assets'] ?? '0'" size="lg" />
                    <span class="text-muted-foreground">=</span>
                    <x-ui.money :amount="$liabPlusEq" size="lg" />
                    <x-ui.badge :variant="$balanced ? 'success' : 'danger'" class="ms-2">{{ $balanced ? __('erp.balanced') : __('erp.unbalanced') }}</x-ui.badge>
                </div>
            </div>
        @endif
    </x-ui.report-shell>
</div>
