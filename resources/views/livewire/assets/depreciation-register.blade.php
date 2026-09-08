<div>
    <x-ui.page-header :breadcrumbs="[['label' => __('erp.nav.fixed_assets')], ['label' => __('erp.nav.depreciation')]]" :title="__('erp.assets.depreciation')" :description="__('erp.assets.depreciation_hint')">
        <x-slot:actions>
            <x-ui.export-button />
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.toolbar :summary="$assets ? trans_choice('erp.pagination.result_count', $assets->total(), ['count' => number_format($assets->total())]) : null" />

    @if ($assets === null || $assets->isEmpty())
        <x-ui.empty-state :title="__('erp.assets.empty_title')" :message="__('erp.assets.empty_hint')" />
    @else
        <x-ui.table>
            <thead><tr>
                <th>{{ __('erp.code') }}</th>
                <th>{{ __('erp.assets.name') }}</th>
                <th class="!text-end">{{ __('erp.assets.cost') }}</th>
                <th class="!text-end">{{ __('erp.assets.accum_dep') }}</th>
                <th class="!text-end">{{ __('erp.assets.nbv') }}</th>
                <th>{{ __('erp.status') }}</th>
            </tr></thead>
            <tbody>
                @foreach ($assets as $asset)
                    <tr wire:key="dep-{{ $asset->id }}">
                        <td><a href="{{ route('assets.show', $asset->id) }}" wire:navigate class="font-medium text-[var(--brand-600)] hover:underline">{{ $asset->code }}</a></td>
                        <td>{{ $asset->name }}</td>
                        <td class="text-end"><x-ui.money :amount="$asset->cost" /></td>
                        <td class="text-end"><x-ui.money :amount="$asset->accum_depreciation" muted /></td>
                        <td class="text-end"><x-ui.money :amount="$asset->netBookValue()" /></td>
                        <td><x-ui.status-badge :status="$asset->status" /></td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="border-t-2 border-border-strong font-semibold [&_td]:py-2.5">
                    <td colspan="2" class="px-4">{{ __('erp.total') }}</td>
                    <td class="px-4 text-end"><x-ui.money :amount="$totals['cost']" /></td>
                    <td class="px-4 text-end"><x-ui.money :amount="$totals['accum']" /></td>
                    <td class="px-4 text-end"><x-ui.money :amount="$totals['nbv']" /></td>
                    <td></td>
                </tr>
            </tfoot>
        </x-ui.table>
        <x-ui.pagination :paginator="$assets" />
    @endif
</div>
