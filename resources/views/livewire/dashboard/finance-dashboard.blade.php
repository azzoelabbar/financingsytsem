<div class="mizan-dashboard">
    <x-ui.page-header
        :title="__('erp.mizan.overview')"
        :description="__('erp.mizan.overview_hint')"
        :eyebrow="__('erp.dashboard')"
    >
        @if ($company && $book)
            <x-slot:actions>
                <x-ui.export-button />
                <x-ui.button variant="secondary" :href="route('reports.management-pack')">{{ __('erp.nav.management_pack') }}</x-ui.button>
                <x-ui.button :href="route('ar.invoices.create')"><x-ui.icon name="plus" class="h-4 w-4" />{{ __('erp.sales_invoice.create') }}</x-ui.button>
            </x-slot:actions>
        @endif
    </x-ui.page-header>

    @if (! $company || ! $book)
        <x-ui.empty-state
            :title="__('erp.dashboard_no_context_title')"
            :message="__('erp.dashboard_no_context_message')"
        />
    @else
        @php
            $net = (float) ($kpis['net_profit'] ?? 0);
            $rev = (float) ($kpis['revenue'] ?? 0);
            $exp = (float) ($kpis['expenses'] ?? 0);
            $assets = (float) ($kpis['total_assets'] ?? 0);
            $equity = (float) ($kpis['equity'] ?? 0);
            $liabilities = $kpis['total_liabilities'] ?? '0';
            $perfMax = max(abs($rev), abs($exp), 1);
            $cur = $company->functional_currency ?? '';

            // Current is healthy; each older bucket is a step more urgent.
            $agingColors = [
                'current' => 'var(--success-color)',
                '1_30' => 'var(--warning-soft)',
                '31_60' => 'var(--warning-color)',
                '61_90' => 'var(--warning-strong)',
                '90_plus' => 'var(--danger)',
            ];

            $arOverdue = 0.0;
            foreach (['1_30', '31_60', '61_90', '90_plus'] as $bucket) {
                $arOverdue += (float) ($arAging['buckets'][$bucket] ?? 0);
            }

            // Everything a finance user must act on today, in one list.
            $attention = [];
            if (! ($kpis['tb_balanced'] ?? false)) {
                $attention[] = ['label' => __('erp.dashboard_kpi.tb_balanced'), 'detail' => __('erp.dashboard_attention.tb_unbalanced'), 'href' => route('gl.trial-balance')];
            }
            if ($arReconciliation !== null && ! ($arReconciliation['passed'] ?? false)) {
                $attention[] = ['label' => __('erp.nav.ar_reconciliation'), 'detail' => __('erp.dashboard_attention.ar_mismatch'), 'href' => route('ar.reconciliation')];
            }
            if ($apReconciliation !== null && ! ($apReconciliation['passed'] ?? false)) {
                $attention[] = ['label' => __('erp.nav.ap_reconciliation'), 'detail' => __('erp.dashboard_attention.ap_mismatch'), 'href' => route('ap.reconciliation')];
            }
            if ($arOverdue > 0) {
                $attention[] = ['label' => __('erp.aging.overdue_total'), 'detail' => __('erp.dashboard_attention.ar_overdue'), 'href' => route('ar.aging'), 'amount' => (string) $arOverdue];
            }
        @endphp

        {{-- ============ Headline position ============ --}}
        <div class="mizan-overview">
            <section class="mizan-capital-card">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm text-white/75">{{ __('erp.dashboard_kpi.cash') }}</p>
                    <x-ui.icon name="wallet" class="h-6 w-6 text-white/60" />
                </div>
                <a href="{{ route('reports.balance-sheet') }}" wire:navigate class="mizan-cash-value mt-5 block">
                    <x-ui.money :amount="$kpis['cash'] ?? '0'" :currency="$cur" size="hero" />
                </a>
                <div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-white/15 pt-4">
                    <a href="{{ route('reports.profit-loss') }}" wire:navigate class="mizan-profit-value flex items-center gap-3" data-negative="{{ $net < 0 ? 'true' : 'false' }}">
                        <span class="text-xs text-white/70">{{ __('erp.dashboard_kpi.net_profit') }}</span>
                        <x-ui.money :amount="$kpis['net_profit'] ?? '0'" :negative="$net < 0" />
                    </a>
                    <a href="{{ route('reports.balance-sheet') }}" class="text-white/80 hover:text-white" aria-label="{{ __('erp.mizan.view_statement') }}"><x-ui.icon class="h-4 w-4 rtl:rotate-180" /></a>
                </div>
            </section>
            <x-ui.stat tone="brand" :label="__('erp.dashboard_kpi.ar_total')" :hint="__('erp.mizan.open_receivables')" :href="route('ar.aging')">
                <x-slot:icon><span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-tint text-brand"><x-ui.icon class="h-4 w-4 -rotate-45" /></span></x-slot:icon>
                <x-ui.money :amount="$kpis['ar_total'] ?? '0'" :currency="$cur" size="hero" />
            </x-ui.stat>
            <x-ui.stat tone="warning" :label="__('erp.dashboard_kpi.ap_total')" :hint="__('erp.mizan.open_payables')" :href="route('ap.aging')">
                <x-slot:icon><span class="flex h-9 w-9 items-center justify-center rounded-xl bg-[var(--warning-muted)] text-[var(--warning-color)]"><x-ui.icon class="h-4 w-4 rotate-45" /></span></x-slot:icon>
                <x-ui.money :amount="$kpis['ap_total'] ?? '0'" :currency="$cur" size="hero" />
            </x-ui.stat>
        </div>

        {{-- ============ Needs attention + controls ============ --}}
        <div class="mt-4 grid gap-4">
            <x-ui.card
                class="mizan-attention-card"
                :title="__('erp.dashboard_sections.attention')"
                :description="__('erp.dashboard_sections.attention_hint')"
                :tone="$attention === [] ? 'success' : 'warning'"
                flush
            >
                @if ($attention === [])
                    <div class="flex items-center gap-3 px-5 py-5">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[var(--success-muted)] text-[var(--success-color)]">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 011.4-1.4l3.3 3.3 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
                        </span>
                        <div>
                            <p class="text-[0.875rem] font-semibold text-foreground">{{ __('erp.dashboard_attention.all_clear_title') }}</p>
                            <p class="mt-0.5 text-[0.8125rem] text-muted-foreground">{{ __('erp.dashboard_attention.all_clear_hint') }}</p>
                        </div>
                    </div>
                @else
                    <ul class="divide-y divide-border">
                        @foreach ($attention as $item)
                            <li>
                                <a href="{{ $item['href'] }}" wire:navigate class="flex items-center justify-between gap-4 px-5 py-3 transition-colors hover:bg-[var(--brand-50)]">
                                    <span class="flex min-w-0 items-start gap-3">
                                        <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-[var(--warning-muted)] text-[var(--warning-color)]">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M8.5 3.6a1.7 1.7 0 013 0l6.3 11.2A1.7 1.7 0 0116.3 17H3.7a1.7 1.7 0 01-1.5-2.2L8.5 3.6zM10 7a1 1 0 00-1 1v3a1 1 0 102 0V8a1 1 0 00-1-1zm0 7.5a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                                        </span>
                                        <span class="min-w-0">
                                            <span class="block text-[0.8125rem] font-medium text-foreground">{{ $item['label'] }}</span>
                                            <span class="mt-0.5 block text-xs text-muted-foreground">{{ $item['detail'] }}</span>
                                        </span>
                                    </span>
                                    @if (isset($item['amount']))
                                        <x-ui.money :amount="$item['amount']" tone="danger" size="lg" />
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-ui.card>

            {{-- Accounting integrity: the controls a finance team checks first. --}}
            <section class="mizan-controls" aria-label="{{ __('erp.dashboard_sections.integrity') }}">
                <ul class="grid gap-2 sm:grid-cols-3">
                    @php
                        $controls = [
                            ['label' => __('erp.dashboard_kpi.tb_balanced'), 'ok' => (bool) ($kpis['tb_balanced'] ?? false), 'href' => route('gl.trial-balance')],
                            ['label' => __('erp.mizan.ar_control'), 'ok' => (bool) ($arReconciliation['passed'] ?? false), 'href' => route('ar.reconciliation')],
                            ['label' => __('erp.mizan.ap_control'), 'ok' => (bool) ($apReconciliation['passed'] ?? false), 'href' => route('ap.reconciliation')],
                        ];
                    @endphp
                    @foreach ($controls as $control)
                        <li>
                            <a href="{{ $control['href'] }}" wire:navigate class="flex items-center justify-between gap-3 rounded-lg border border-border bg-white/60 px-4 py-3 transition-colors hover:border-border-strong hover:bg-white">
                                <span class="text-[0.8125rem] font-medium">{{ $control['label'] }}</span>
                                <x-ui.badge :variant="$control['ok'] ? 'success' : 'danger'">{{ $control['ok'] ? __('erp.balanced') : __('erp.unbalanced') }}</x-ui.badge>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        </div>

        {{-- ============ Position + performance ============ --}}
        <div class="mt-5 grid gap-5 lg:grid-cols-2">
            <x-ui.card :title="__('erp.dashboard_sections.position')" :description="__('erp.dashboard_sections.position_hint')">
                <dl class="space-y-3">
                    <div class="flex items-baseline justify-between">
                        <dt class="text-[0.8125rem] text-muted-foreground">{{ __('erp.dashboard_kpi.total_assets') }}</dt>
                        <dd><x-ui.money :amount="$kpis['total_assets'] ?? '0'" :currency="$cur" size="lg" /></dd>
                    </div>
                    @php $eqPct = $assets != 0 ? max(0, min(100, $equity / $assets * 100)) : 0; @endphp
                    <div class="flex h-2.5 overflow-hidden rounded-full bg-muted" role="img" aria-label="{{ __('erp.dashboard_sections.capital_structure') }}">
                        <div class="h-full" style="width: {{ 100 - $eqPct }}%; background: var(--border-strong)"></div>
                        <div class="h-full" style="width: {{ $eqPct }}%; background: var(--brand-600)"></div>
                    </div>
                    <div class="flex items-center justify-between text-[0.8125rem]">
                        <dt class="flex items-center gap-2 text-muted-foreground"><span class="h-2.5 w-2.5 rounded-sm" style="background: var(--border-strong)"></span>{{ __('erp.dashboard_kpi.liabilities') }}</dt>
                        <dd><x-ui.money :amount="(string) $liabilities" /></dd>
                    </div>
                    <div class="flex items-center justify-between text-[0.8125rem]">
                        <dt class="flex items-center gap-2 text-muted-foreground"><span class="h-2.5 w-2.5 rounded-sm" style="background: var(--brand-600)"></span>{{ __('erp.dashboard_kpi.equity') }}</dt>
                        <dd><x-ui.money :amount="$kpis['equity'] ?? '0'" /></dd>
                    </div>
                </dl>
            </x-ui.card>

            <x-ui.card :title="__('erp.dashboard_sections.performance')" :description="__('erp.dashboard_sections.performance_hint')">
                <div class="space-y-3.5">
                    <div>
                        <div class="mb-1.5 flex items-center justify-between text-[0.8125rem]">
                            <span class="text-muted-foreground">{{ __('erp.dashboard_kpi.revenue') }}</span>
                            <x-ui.money :amount="$kpis['revenue'] ?? '0'" />
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-muted"><div class="h-full rounded-full" style="width: {{ abs($rev) / $perfMax * 100 }}%; background: var(--success-color)"></div></div>
                    </div>
                    <div>
                        <div class="mb-1.5 flex items-center justify-between text-[0.8125rem]">
                            <span class="text-muted-foreground">{{ __('erp.dashboard_kpi.expenses') }}</span>
                            <x-ui.money :amount="$kpis['expenses'] ?? '0'" />
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-muted"><div class="h-full rounded-full" style="width: {{ abs($exp) / $perfMax * 100 }}%; background: var(--warning-color)"></div></div>
                    </div>
                    <div class="flex items-baseline justify-between border-t border-border pt-3">
                        <span class="text-[0.8125rem] font-semibold">{{ __('erp.dashboard_kpi.net_profit') }}</span>
                        <x-ui.money :amount="$kpis['net_profit'] ?? '0'" :currency="$cur" :negative="$net < 0" size="lg" />
                    </div>
                </div>
            </x-ui.card>
        </div>

        {{-- ============ Receivables & payables risk ============ --}}
        <div class="mt-5 grid gap-5 lg:grid-cols-2">
            @foreach ([
                ['data' => $arAging, 'title' => __('erp.dashboard_sections.ar_aging_summary'), 'hint' => __('erp.dashboard_sections.ar_aging_hint'), 'href' => route('ar.aging')],
                ['data' => $apAging, 'title' => __('erp.dashboard_sections.ap_aging_summary'), 'hint' => __('erp.dashboard_sections.ap_aging_hint'), 'href' => route('ap.aging')],
            ] as $panel)
                <x-ui.card :title="$panel['title']" :description="$panel['hint']">
                    <x-slot:actions>
                        <x-ui.button :href="$panel['href']" variant="ghost" size="sm">{{ __('erp.view_all') }}</x-ui.button>
                    </x-slot:actions>
                    @if ($panel['data'] && (float) ($panel['data']['total'] ?? 0) != 0)
                        @php $total = (float) $panel['data']['total']; @endphp
                        <div class="mb-4 flex h-3 overflow-hidden rounded-full bg-muted">
                            @foreach ($agingColors as $bucket => $color)
                                @php $amt = (float) ($panel['data']['buckets'][$bucket] ?? 0); $w = $total != 0 ? $amt / $total * 100 : 0; @endphp
                                @if ($w > 0)<div class="h-full" style="width: {{ $w }}%; background: {{ $color }}" title="{{ __('erp.aging.'.$bucket) }}"></div>@endif
                            @endforeach
                        </div>
                        <dl class="space-y-1.5">
                            @foreach ($agingColors as $bucket => $color)
                                @php $amt = (float) ($panel['data']['buckets'][$bucket] ?? 0); @endphp
                                <div class="flex items-center justify-between text-[0.8125rem]">
                                    <dt class="flex items-center gap-2 text-muted-foreground"><span class="h-2.5 w-2.5 rounded-sm" style="background: {{ $color }}"></span>{{ __('erp.aging.'.$bucket) }}</dt>
                                    <dd><x-ui.money :amount="$panel['data']['buckets'][$bucket] ?? '0'" :muted="$amt == 0.0" /></dd>
                                </div>
                            @endforeach
                            <div class="mt-2 flex items-baseline justify-between border-t border-border pt-2.5">
                                <dt class="text-[0.8125rem] font-semibold">{{ __('erp.total') }}</dt>
                                <dd><x-ui.money :amount="$panel['data']['total'] ?? '0'" :currency="$cur" size="lg" /></dd>
                            </div>
                        </dl>
                    @else
                        <x-ui.empty-state variant="panel" :message="__('erp.dashboard_sections.no_aging')" />
                    @endif
                </x-ui.card>
            @endforeach
        </div>

        {{-- ============ Recent activity + quick actions ============ --}}
        <div class="mt-5 grid gap-5 lg:grid-cols-3">
            <x-ui.card class="lg:col-span-2" :title="__('erp.dashboard_sections.recent_journals')" flush>
                <x-slot:actions>
                    <x-ui.button :href="route('gl.journals')" variant="ghost" size="sm">{{ __('erp.view_all') }}</x-ui.button>
                </x-slot:actions>
                @if ($recentJournals->isNotEmpty())
                    <x-ui.table flush>
                        <thead>
                            <tr>
                                <th>{{ __('erp.number') }}</th>
                                <th>{{ __('erp.date') }}</th>
                                <th class="!text-end">{{ __('erp.document.total') }}</th>
                                <th>{{ __('erp.status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentJournals as $journal)
                                <tr wire:key="dj-{{ $journal->id }}">
                                    <td><a href="{{ route('gl.journals.show', $journal) }}" wire:navigate class="font-medium text-[var(--brand-600)] hover:underline" dir="ltr">{{ $journal->number }}</a></td>
                                    <td class="tabular-nums text-muted-foreground" dir="ltr">{{ $journal->journal_date?->format('Y-m-d') }}</td>
                                    <td class="text-end"><x-ui.money :amount="$journal->total_debit" /></td>
                                    <td><x-ui.status-badge :status="$journal->status?->value" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </x-ui.table>
                @else
                    <x-ui.empty-state variant="panel" :message="__('erp.dashboard_sections.no_journals_message')" />
                @endif
            </x-ui.card>

            <x-ui.card :title="__('erp.dashboard_sections.quick_actions')">
                <div class="grid grid-cols-1 gap-2">
                    @foreach ([
                        ['label' => __('erp.sales_invoice.create'), 'href' => route('ar.invoices.create'), 'icon' => 'M12 4.5v15m7.5-7.5h-15'],
                        ['label' => __('erp.customer.create'), 'href' => route('ar.customers.create'), 'icon' => 'M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM3 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 019.374 21c-2.331 0-4.512-.645-6.374-1.766z'],
                        ['label' => __('erp.purchase_invoice.create'), 'href' => route('ap.invoices.create'), 'icon' => 'M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z'],
                        ['label' => __('erp.nav.trial_balance'), 'href' => route('gl.trial-balance'), 'icon' => 'M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25'],
                    ] as $qa)
                        <a href="{{ $qa['href'] }}" wire:navigate class="flex items-center gap-3 rounded-md border border-border px-3 py-2.5 text-[0.8125rem] font-medium transition-colors hover:border-[var(--brand-600)] hover:bg-[var(--brand-50)]">
                            <span class="flex h-8 w-8 items-center justify-center rounded-md bg-[var(--brand-50)] text-[var(--brand-600)]"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $qa['icon'] }}" /></svg></span>
                            <span class="flex-1">{{ $qa['label'] }}</span>
                            <svg class="h-4 w-4 text-muted-foreground rtl:rotate-180" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.3 4.3a1 1 0 011.4 0l5 5a1 1 0 010 1.4l-5 5a1 1 0 01-1.4-1.4L11.6 10 7.3 5.7a1 1 0 010-1.4z" clip-rule="evenodd"/></svg>
                        </a>
                    @endforeach
                </div>
            </x-ui.card>
        </div>
    @endif
</div>
