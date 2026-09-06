<div>
    <x-ui.report-shell
        :title="__('erp.reports.cash_forecast')"
        :description="__('erp.reports.cash_forecast_hint')"
        :breadcrumbs="[['label' => __('erp.nav.reports')], ['label' => __('erp.nav.cash_forecast')]]"
        :company="$company"
        :book="$book"
        :period="$period"
        :as-of="$asOf"
    >
        <x-slot:filters>
            <x-ui.date-filter model="asOf" />
        </x-slot:filters>



    @php $cur = $company?->functional_currency ?? ''; $net = (float) ($report['net'] ?? 0); @endphp

    <div class="mb-6 grid gap-3 sm:grid-cols-3">
        <x-ui.stat :label="__('erp.reports.expected_inflows')" tone="success">
            <x-ui.money :amount="$report['inflows'] ?? '0'" :currency="$cur" size="hero" />
        </x-ui.stat>
        <x-ui.stat :label="__('erp.reports.expected_outflows')" tone="warning">
            <x-ui.money :amount="$report['outflows'] ?? '0'" :currency="$cur" size="hero" />
        </x-ui.stat>
        <x-ui.stat :label="__('erp.reports.net_position')" :tone="$net >= 0 ? 'success' : 'danger'">
            <x-ui.money :amount="$report['net'] ?? '0'" :currency="$cur" :negative="$net < 0" size="hero" />
        </x-ui.stat>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-ui.card :title="__('erp.reports.expected_from_customers')">
            <p class="mb-3 text-xs text-muted-foreground">{{ __('erp.reports.forecast_window') }}</p>
            <dl class="space-y-2">
                @foreach (['current', '1_30'] as $bucket)
                    <div class="flex items-center justify-between text-sm">
                        <dt class="text-muted-foreground">{{ __('erp.aging.'.$bucket) }}</dt>
                        <dd><x-ui.money :amount="$report['ar']['buckets'][$bucket] ?? '0'" /></dd>
                    </div>
                @endforeach
            </dl>
        </x-ui.card>
        <x-ui.card :title="__('erp.reports.expected_to_suppliers')">
            <p class="mb-3 text-xs text-muted-foreground">{{ __('erp.reports.forecast_window') }}</p>
            <dl class="space-y-2">
                @foreach (['current', '1_30'] as $bucket)
                    <div class="flex items-center justify-between text-sm">
                        <dt class="text-muted-foreground">{{ __('erp.aging.'.$bucket) }}</dt>
                        <dd><x-ui.money :amount="$report['ap']['buckets'][$bucket] ?? '0'" /></dd>
                    </div>
                @endforeach
            </dl>
        </x-ui.card>
    </div>
</x-ui.report-shell>
</div>
