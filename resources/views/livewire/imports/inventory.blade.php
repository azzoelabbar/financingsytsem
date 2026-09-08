<div>
    <x-ui.page-header :title="__('imports.kinds.items')" :description="__('imports.inventory_hint')"><x-slot:actions><x-ui.export-button /><x-ui.import-button kind="items" /><x-ui.import-button kind="inventory-report" /></x-slot:actions></x-ui.page-header>
    <x-ui.toolbar />
    <x-ui.table><thead><tr><th>{{ __('imports.item_code') }}</th><th>{{ __('imports.item_name') }}</th><th>{{ __('imports.quantity') }}</th><th>{{ __('imports.value') }}</th></tr></thead><tbody>
        @forelse ($items as $item)<tr><td>{{ $item->code }}</td><td>{{ $item->name }}</td><td class="tabular-nums">{{ $item->quantity }}</td><td><x-ui.money :amount="$item->value" :currency="$this->company()?->functional_currency" /></td></tr>@empty<tr><td colspan="4">{{ __('imports.items_empty') }}</td></tr>@endforelse
    </tbody></x-ui.table>
    <div class="mt-4">{{ $items->links() }}</div>
</div>
