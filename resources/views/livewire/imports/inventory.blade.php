<div>
    <x-ui.page-header :title="__('imports.kinds.items')" :description="__('imports.inventory_hint')"><x-slot:actions><x-ui.export-button /><x-ui.import-button kind="items" /><x-ui.import-button kind="inventory-report" /></x-slot:actions></x-ui.page-header>
    <x-ui.toolbar />
    <x-ui.table>
        <thead><tr>
            <th>{{ __('imports.item_code') }}</th><th>{{ __('imports.item_name') }}</th><th>{{ __('imports.category') }}</th><th>{{ __('imports.unit') }}</th>
            <th>{{ __('imports.standard_cost') }}</th><th>{{ __('imports.sale_price') }}</th><th>{{ __('imports.reorder_level') }}</th>
            <th>{{ __('imports.quantity') }}</th><th>{{ __('imports.value') }}</th>
        </tr></thead>
        <tbody>
        @forelse ($items as $item)
            <tr>
                <td>{{ $item->code }}</td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->category ?: __('imports.not_set') }}</td>
                <td>{{ $item->unit ?: __('imports.not_set') }}</td>
                <td>@if ($item->standard_cost !== null)<x-ui.money :amount="$item->standard_cost" :currency="$this->company()?->functional_currency" />@else {{ __('imports.not_set') }} @endif</td>
                <td>@if ($item->sale_price !== null)<x-ui.money :amount="$item->sale_price" :currency="$this->company()?->functional_currency" />@else {{ __('imports.not_set') }} @endif</td>
                <td class="tabular-nums">{{ $item->reorder_level !== null ? rtrim(rtrim((string) $item->reorder_level, '0'), '.') : __('imports.not_set') }}</td>
                <td class="tabular-nums">{{ $item->quantity }}</td>
                <td><x-ui.money :amount="$item->value" :currency="$this->company()?->functional_currency" /></td>
            </tr>
        @empty
            <tr><td colspan="9">{{ __('imports.items_empty') }}</td></tr>
        @endforelse
        </tbody>
    </x-ui.table>
    <div class="mt-4">{{ $items->links() }}</div>
</div>
