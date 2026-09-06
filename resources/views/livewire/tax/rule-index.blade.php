<div>
    <x-ui.page-header :breadcrumbs="[['label' => __('erp.nav.tax')], ['label' => __('erp.nav.tax_rules')]]" :title="__('erp.tax.rules_title')" :description="__('erp.tax.rules_hint')"><x-slot:actions><x-ui.button :href="route('tax.rules.create')">{{ __('erp.tax.create_rate') }}</x-ui.button></x-slot:actions></x-ui.page-header>

    <x-ui.toolbar :summary="$rates ? trans_choice('erp.pagination.result_count', $rates->total(), ['count' => number_format($rates->total())]) : null" />

    @if ($rates === null || $rates->isEmpty())
        <x-ui.empty-state :title="__('erp.tax.rules_empty_title')" :message="__('erp.tax.rules_empty_hint')" />
    @else
        <x-ui.table>
            <thead><tr>
                <th>{{ __('erp.tax.tax_code') }}</th>
                <th class="!text-end">{{ __('erp.tax.rate') }}</th>
                <th>{{ __('erp.tax.effective_from') }}</th>
                <th>{{ __('erp.tax.effective_to') }}</th>
                <th>{{ __('erp.tax.legal_reference') }}</th>
                <th>{{ __('erp.status') }}</th>
            </tr></thead>
            <tbody>
                @foreach ($rates as $rate)
                    <tr wire:key="tr-{{ $rate->id }}">
                        <td class="font-medium"><span class="font-mono text-xs text-muted-foreground" dir="ltr">{{ $rate->taxCode?->code }}</span> {{ $rate->taxCode?->name }}</td>
                        <td class="text-end tabular-nums" dir="ltr">{{ rtrim(rtrim(number_format((float) $rate->rate, 4), '0'), '.') }}%</td>
                        <td class="tabular-nums text-muted-foreground" dir="ltr">{{ $rate->effective_from?->format('Y-m-d') }}</td>
                        <td class="tabular-nums text-muted-foreground" dir="ltr">{{ $rate->effective_to?->format('Y-m-d') ?? __('erp.tax.open_ended') }}</td>
                        <td class="text-muted-foreground">{{ $rate->legal_reference ?? '-' }}</td>
                        <td><x-ui.status-badge :status="$rate->status" /></td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui.table>
        <x-ui.pagination :paginator="$rates" />
    @endif
</div>
