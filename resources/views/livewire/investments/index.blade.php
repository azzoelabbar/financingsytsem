<div>
    <x-ui.page-header :breadcrumbs="[['label' => __('erp.nav.investments')]]" :title="__('erp.investment.title')" :description="__('erp.investment.hint')"><x-slot:actions><x-ui.export-button /><x-ui.button :href="route('investments.create')">{{ __('erp.investment.create') }}</x-ui.button></x-slot:actions></x-ui.page-header>

    <x-ui.toolbar :summary="$investments ? trans_choice('erp.pagination.result_count', $investments->total(), ['count' => number_format($investments->total())]) : null" />

    @if ($investments === null || $investments->isEmpty())
        <x-ui.empty-state :title="__('erp.investment.empty_title')" :message="__('erp.investment.empty_hint')" />
    @else
        <x-ui.table>
            <thead><tr>
                <th>{{ __('erp.code') }}</th>
                <th>{{ __('erp.investment.name') }}</th>
                <th>{{ __('erp.investment.classification') }}</th>
                <th class="!text-end">{{ __('erp.investment.cost') }}</th>
                <th class="!text-end">{{ __('erp.investment.carrying') }}</th>
                <th class="!text-end">{{ __('erp.investment.fair_value') }}</th>
                <th>{{ __('erp.status') }}</th>
            </tr></thead>
            <tbody>
                @foreach ($investments as $inv)
                    <tr wire:key="inv-{{ $inv->id }}">
                        <td><a href="{{ route('investments.show', $inv->id) }}" wire:navigate class="font-medium text-[var(--brand-600)] hover:underline">{{ $inv->code }}</a></td>
                        <td>{{ $inv->name }}</td>
                        <td><x-ui.badge variant="outline">{{ __('erp.investment.classifications.'.($inv->classification?->value ?? $inv->classification)) }}</x-ui.badge></td>
                        <td class="text-end"><x-ui.money :amount="$inv->cost ?? $inv->acquisition_cost ?? '0'" /></td>
                        <td class="text-end"><x-ui.money :amount="$inv->carrying_amount ?? $inv->carrying ?? '0'" /></td>
                        <td class="text-end"><x-ui.money :amount="$inv->fair_value ?? '0'" muted /></td>
                        <td><x-ui.status-badge :status="$inv->status?->value ?? $inv->status" /></td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui.table>
        <x-ui.pagination :paginator="$investments" />
    @endif
</div>
