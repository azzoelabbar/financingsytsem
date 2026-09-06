<div>
    <x-ui.page-header :breadcrumbs="[['label' => __('erp.nav.ap')], ['label' => __('erp.nav.open_items')]]" :title="__('erp.open_items.ap_title')" :description="__('erp.open_items.ap_hint')">
        <x-slot:actions>
            <x-ui.searchable-select wire:model.live="supplierId" :block="false">
                <option value="">{{ __('erp.statement_page.select_supplier') }}</option>
                @foreach ($suppliers as $s)
                    <option value="{{ $s->id }}">{{ $s->code }} - {{ app()->getLocale() === 'ar' ? ($s->name_ar ?? $s->legal_name) : ($s->trading_name ?? $s->legal_name ?? $s->name_ar) }}</option>
                @endforeach
            </x-ui.searchable-select>
        </x-slot:actions>
    </x-ui.page-header>

    @if ($items === null)
        <x-ui.empty-state :title="__('erp.statement_page.pick_title')" :message="__('erp.statement_page.pick_supplier_hint')" />
    @elseif (empty($items))
        <x-ui.empty-state :message="__('erp.open_items.none')" />
    @else
        @include('livewire.partials.open-items-table', ['items' => $items, 'side' => 'ap'])
    @endif
</div>
