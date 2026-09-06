{{--
    Shared aging presentation for receivables and payables.

    Expects: $report (buckets + total), $bucketKeys, $title, $hint,
    $moduleLabel, $totalLabel, $statementRoute, and the component's $asOf.
--}}
@php
    $buckets = $report['buckets'] ?? [];
    $total = (float) ($report['total'] ?? 0);

    // Current is healthy, everything past due gets progressively more urgent.
    $bucketTone = [
        'current' => ['bar' => 'var(--success-color)', 'text' => 'text-[var(--success-color)]'],
        '1_30' => ['bar' => 'var(--warning-soft)', 'text' => 'text-[var(--warning-color)]'],
        '31_60' => ['bar' => 'var(--warning-color)', 'text' => 'text-[var(--warning-color)]'],
        '61_90' => ['bar' => 'var(--warning-strong)', 'text' => 'text-[var(--warning-color)]'],
        '90_plus' => ['bar' => 'var(--danger)', 'text' => 'text-[var(--danger)]'],
        '91_120' => ['bar' => 'var(--warning-strong)', 'text' => 'text-[var(--warning-color)]'],
        '120_plus' => ['bar' => 'var(--danger)', 'text' => 'text-[var(--danger)]'],
        'credits' => ['bar' => 'var(--brand-400)', 'text' => 'text-[var(--brand-600)]'],
    ];

    $overdue = 0.0;
    foreach ($bucketKeys as $key) {
        if (! in_array($key, ['current', 'credits'], true)) {
            $overdue += (float) ($buckets[$key] ?? 0);
        }
    }

    $barKeys = array_values(array_filter($bucketKeys, fn (string $k): bool => $k !== 'credits'));
    $barTotal = 0.0;
    foreach ($barKeys as $key) {
        $barTotal += max(0.0, (float) ($buckets[$key] ?? 0));
    }
@endphp

<x-ui.page-header
    :title="$title"
    :description="$hint"
    :breadcrumbs="[['label' => $moduleLabel], ['label' => $title]]"
>
    <x-slot:actions>
        <x-ui.date-filter :label="__('erp.aging.as_of')" model="asOf" />
        <x-ui.button variant="secondary" :href="$statementRoute">{{ __('erp.aging_page.open_statement') }}</x-ui.button>
    </x-slot:actions>
</x-ui.page-header>

@if ($buckets === [] && $total == 0.0)
    <x-ui.empty-state :title="__('erp.aging_page.empty_title')" :message="__('erp.aging_page.empty_hint')" />
@else
    <div class="mb-5 grid gap-3 sm:grid-cols-3">
        <x-ui.stat :label="$totalLabel" tone="brand">
            <x-ui.money :amount="$report['total'] ?? '0'" size="lg" />
        </x-ui.stat>
        <x-ui.stat :label="__('erp.aging.current')" tone="success">
            <x-ui.money :amount="$buckets['current'] ?? '0'" size="lg" />
        </x-ui.stat>
        <x-ui.stat :label="__('erp.aging.overdue_total')" :tone="$overdue > 0 ? 'danger' : 'success'">
            <x-ui.money :amount="(string) $overdue" size="lg" :tone="$overdue > 0 ? 'danger' : null" />
        </x-ui.stat>
    </div>

    <x-ui.card :title="__('erp.aging_page.distribution')" :description="__('erp.aging_page.distribution_hint')">
        @if ($barTotal > 0)
            <div class="mb-5 flex h-3 overflow-hidden rounded-full bg-muted" role="img" aria-label="{{ __('erp.aging_page.distribution') }}">
                @foreach ($barKeys as $key)
                    @php
                        $amount = max(0.0, (float) ($buckets[$key] ?? 0));
                        $width = $barTotal > 0 ? $amount / $barTotal * 100 : 0;
                    @endphp
                    @if ($width > 0)
                        <div class="h-full" style="width: {{ $width }}%; background: {{ $bucketTone[$key]['bar'] ?? 'var(--border-strong)' }}" title="{{ __('erp.aging.'.$key) }}"></div>
                    @endif
                @endforeach
            </div>
        @endif

        <dl class="divide-y divide-border">
            @foreach ($bucketKeys as $key)
                @php $amount = (float) ($buckets[$key] ?? 0); @endphp
                <div class="flex items-center justify-between gap-4 py-2">
                    <dt class="flex items-center gap-2.5 text-[0.8125rem]">
                        <span class="h-2.5 w-2.5 rounded-sm" style="background: {{ $bucketTone[$key]['bar'] ?? 'var(--border-strong)' }}"></span>
                        <span class="{{ $key === 'current' ? 'font-medium text-foreground' : 'text-muted-foreground' }}">{{ __('erp.aging.'.$key) }}</span>
                    </dt>
                    <dd><x-ui.money :amount="$buckets[$key] ?? '0'" :muted="$amount == 0.0" /></dd>
                </div>
            @endforeach
            <div class="flex items-center justify-between gap-4 border-t border-border-strong pt-3">
                <dt class="text-[0.8125rem] font-semibold">{{ __('erp.total') }}</dt>
                <dd><x-ui.money :amount="$report['total'] ?? '0'" size="lg" /></dd>
            </div>
        </dl>
    </x-ui.card>
@endif
