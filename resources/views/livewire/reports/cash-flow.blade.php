<div>
    <x-ui.report-shell
        :title="__('erp.nav.cash_flow')"
        :description="__('erp.reports.cash_flow_hint')"
        :breadcrumbs="[['label' => __('erp.nav.reports')], ['label' => __('erp.nav.cash_flow')]]"
        :company="$company"
        :book="$book"
        :period="$period"
        :as-of="$asOf"
    >
        <x-slot:filters>
            <x-ui.date-filter model="asOf" />
        </x-slot:filters>



    @php
        $cur = $company?->functional_currency ?? '';
        $net = (float) ($report['net'] ?? 0);
        $activities = [
            ['key' => 'operating', 'label' => __('erp.reports.activity_operating'), 'hint' => __('erp.reports.activity_operating_hint'), 'amount' => $report['operating'] ?? '0'],
            ['key' => 'investing', 'label' => __('erp.reports.activity_investing'), 'hint' => __('erp.reports.activity_investing_hint'), 'amount' => $report['investing'] ?? '0'],
            ['key' => 'financing', 'label' => __('erp.reports.activity_financing'), 'hint' => __('erp.reports.activity_financing_hint'), 'amount' => $report['financing'] ?? '0'],
        ];
    @endphp

    @if ($movements === [])
        <x-ui.empty-state :title="__('erp.reports.cash_flow_empty_title')" :message="__('erp.reports.cash_flow_empty_hint')" />
    @else
        {{-- Headline numbers --}}
        <div class="mb-6 grid gap-3 sm:grid-cols-3">
            <x-ui.stat :label="__('erp.reports.cash_in')" tone="success">
                <x-ui.money :amount="$report['inflows'] ?? '0'" :currency="$cur" size="hero" />
            </x-ui.stat>
            <x-ui.stat :label="__('erp.reports.cash_out')" tone="warning">
                <x-ui.money :amount="$report['outflows'] ?? '0'" :currency="$cur" size="hero" />
            </x-ui.stat>
            <x-ui.stat :label="__('erp.reports.net_cash_change')" :tone="$net >= 0 ? 'success' : 'danger'">
                <x-ui.money :amount="$report['net'] ?? '0'" :currency="$cur" :negative="$net < 0" size="hero" />
            </x-ui.stat>
        </div>

        {{-- Where the money moved --}}
        <div class="mb-6 overflow-hidden rounded-lg border border-border bg-card">
            <div class="border-b border-border bg-surface-sunken px-5 py-3">
                <h3 class="text-sm font-semibold text-foreground">{{ __('erp.reports.cash_by_activity') }}</h3>
            </div>
            <dl class="divide-y divide-border">
                @foreach ($activities as $activity)
                    <div class="flex flex-wrap items-center justify-between gap-4 px-5 py-4">
                        <dt class="min-w-0">
                            <span class="text-sm font-semibold text-foreground">{{ $activity['label'] }}</span>
                            <p class="mt-0.5 max-w-xl text-xs leading-relaxed text-muted-foreground">{{ $activity['hint'] }}</p>
                        </dt>
                        <dd class="shrink-0">
                            <x-ui.money :amount="$activity['amount']" :currency="$cur" :negative="(float) $activity['amount'] < 0" size="lg" />
                        </dd>
                    </div>
                @endforeach
                <div class="flex items-center justify-between gap-4 border-t border-border px-5 py-4" style="background: {{ $net >= 0 ? 'var(--success-muted)' : 'var(--danger-muted)' }}">
                    <span class="text-base font-semibold">{{ __('erp.reports.net_cash_change') }}</span>
                    <span class="text-xl font-bold"><x-ui.money :amount="$report['net'] ?? '0'" :currency="$cur" :negative="$net < 0" /></span>
                </div>
            </dl>
        </div>

        {{-- Detail per cash / bank account --}}
        <x-ui.card :title="__('erp.reports.cash_movements')" :padding="false" flush>
            <x-ui.table flush class="border-t border-border">
                <thead><tr>
                    <th>{{ __('erp.reports.cash_account') }}</th>
                    <th>{{ __('erp.reports.activity_type') }}</th>
                    <th class="!text-end">{{ __('erp.reports.money_in') }}</th>
                    <th class="!text-end">{{ __('erp.reports.money_out') }}</th>
                    <th class="!text-end">{{ __('erp.reports.net_cash_change') }}</th>
                    <th class="!text-end">{{ __('erp.reports.movements_count') }}</th>
                </tr></thead>
                <tbody>
                    @foreach ($movements as $row)
                        <tr wire:key="cf-{{ $row['account'] }}-{{ $row['classification'] }}">
                            <td>
                                <span class="text-sm text-foreground">{{ $row['name'] }}</span>
                                <span class="ms-2 font-mono text-xs text-muted-foreground" dir="ltr">{{ $row['account'] }}</span>
                            </td>
                            <td>
                                <x-ui.badge :variant="match ($row['classification']) { 'investing' => 'info', 'financing' => 'warning', default => 'primary' }">
                                    {{ __('erp.reports.activity_'.$row['classification']) }}
                                </x-ui.badge>
                            </td>
                            <td class="text-end"><x-ui.money :amount="$row['in']" /></td>
                            <td class="text-end"><x-ui.money :amount="$row['out']" /></td>
                            <td class="text-end"><x-ui.money :amount="$row['net']" :negative="(float) $row['net'] < 0" /></td>
                            <td class="text-end text-muted-foreground tabular-nums">{{ $row['count'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </x-ui.table>
        </x-ui.card>
    @endif
</x-ui.report-shell>
</div>
