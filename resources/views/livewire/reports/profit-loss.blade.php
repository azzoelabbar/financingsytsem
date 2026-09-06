<div>
    @php
        $lines = $report['lines'] ?? [];
        $bySection = collect($lines)->groupBy('group');
        $cur = $company?->functional_currency ?? '';
        $revenue = $report['revenue'] ?? '0';
        $expenses = $report['expenses'] ?? '0';
        $net = $report['net_profit'] ?? '0';
        $sections = [
            ['key' => 'revenue', 'label' => __('erp.statement.revenue'), 'total' => $revenue],
            ['key' => 'cost_of_sales', 'label' => __('erp.statement.cost_of_sales'), 'total' => null],
            ['key' => 'expense', 'label' => __('erp.statement.expenses'), 'total' => $expenses],
        ];
    @endphp

    <x-ui.report-shell
        :title="__('erp.nav.profit_loss')"
        :description="__('erp.statement.pl_hint')"
        :breadcrumbs="[['label' => __('erp.nav.reports')], ['label' => __('erp.nav.profit_loss')]]"
        :company="$company"
        :book="$book"
        :period="$period"
        :as-of="$asOf"
    >
        <x-slot:filters>
            <x-ui.date-filter model="asOf" />
        </x-slot:filters>

        @if ($lines === [])
            <x-ui.empty-state :title="__('erp.statement.pl_empty_title')" :message="__('erp.statement.pl_empty_hint')" />
        @else
            {{-- Headline result first, then the detail that produces it. --}}
            <div class="mx-auto mb-5 grid max-w-3xl gap-3 sm:grid-cols-3">
                <x-ui.stat :label="__('erp.statement.revenue')" tone="success">
                    <x-ui.money :amount="$revenue" :currency="$cur" size="lg" />
                </x-ui.stat>
                <x-ui.stat :label="__('erp.statement.expenses')" tone="warning">
                    <x-ui.money :amount="$expenses" :currency="$cur" size="lg" />
                </x-ui.stat>
                <x-ui.stat :label="__('erp.statement.net_income')" :tone="(float) $net >= 0 ? 'success' : 'danger'">
                    <x-ui.money :amount="$net" :currency="$cur" :negative="(float) $net < 0" size="lg" />
                </x-ui.stat>
            </div>

            <div class="mx-auto max-w-3xl">
                <x-ui.statement-block>
                    @foreach ($sections as $section)
                        @php $sectionLines = $bySection[$section['key']] ?? collect(); @endphp
                        @if ($sectionLines->isNotEmpty() || $section['total'] !== null)
                            <div class="border-b border-border">
                                <div class="bg-surface-sunken px-5 py-2.5">
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
                                        <div class="px-5 py-2 text-[0.8125rem] text-muted-foreground">{{ __('erp.no_data') }}</div>
                                    @endforelse

                                    @if ($section['total'] !== null)
                                        <div class="flex items-center justify-between gap-4 border-t border-border bg-surface-sunken/60 px-5 py-2.5">
                                            <dt class="text-[0.8125rem] font-semibold">{{ __('erp.statement.total_prefix') }} {{ $section['label'] }}</dt>
                                            <dd><x-ui.money :amount="$section['total']" :currency="$cur" size="lg" /></dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>
                        @endif
                    @endforeach

                    <div class="flex items-center justify-between gap-4 px-5 py-4"
                         style="background: {{ (float) $net >= 0 ? 'var(--success-muted)' : 'var(--danger-muted)' }}">
                        <span class="text-[0.9375rem] font-semibold">{{ __('erp.statement.net_income') }}</span>
                        <x-ui.money :amount="$net" :currency="$cur" :negative="(float) $net < 0" size="hero" />
                    </div>
                </x-ui.statement-block>
            </div>
        @endif
    </x-ui.report-shell>
</div>
