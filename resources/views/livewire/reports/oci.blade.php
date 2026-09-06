<div>
    <x-ui.report-shell
        :title="__('erp.reports.oci')"
        :description="__('erp.reports.oci_hint')"
        :breadcrumbs="[['label' => __('erp.nav.reports')], ['label' => __('erp.nav.oci')]]"
        :company="$company"
        :book="$book"
        :period="$period"
        :as-of="$asOf"
    >
        <x-slot:filters>
            <x-ui.date-filter model="asOf" />
        </x-slot:filters>



    @php $lines = $report['lines'] ?? []; $cur = $company?->functional_currency ?? ''; @endphp

    @if ($lines === [])
        <x-ui.empty-state :title="__('erp.reports.oci_empty_title')" :message="__('erp.reports.oci_empty_hint')" />
    @else
        <div class="mx-auto max-w-3xl overflow-hidden rounded-lg border border-border bg-card">
            <div class="bg-surface-sunken px-5 py-2.5">
                <h3 class="text-sm font-semibold uppercase tracking-wide">{{ __('erp.reports.oci') }}</h3>
            </div>
            <dl>
                @foreach ($lines as $line)
                    <div class="flex items-center justify-between gap-4 px-5 py-2">
                        <dt class="flex min-w-0 items-baseline gap-2.5">
                            <span class="shrink-0 font-mono text-xs text-muted-foreground" dir="ltr">{{ $line['code'] }}</span>
                            <span class="truncate text-sm">{{ $line['name'] }}</span>
                        </dt>
                        <dd><x-ui.money :amount="$line['amount']" :negative="(float) $line['amount'] < 0" /></dd>
                    </div>
                @endforeach
                <div class="flex items-center justify-between border-t border-border bg-surface-sunken/50 px-5 py-3 text-base font-semibold">
                    <dt>{{ __('erp.reports.total_oci') }}</dt>
                    <dd><x-ui.money :amount="$report['total'] ?? '0'" :currency="$cur" :negative="(float) ($report['total'] ?? 0) < 0" /></dd>
                </div>
            </dl>
        </div>
    @endif
</x-ui.report-shell>
</div>
