<div>
    <x-ui.page-header :breadcrumbs="[['label' => __('erp.nav.ar')], ['label' => __('erp.nav.open_items')]]" :title="__('erp.open_items.ar_title')" :description="__('erp.open_items.ar_hint')">
        <x-slot:actions>
            <select wire:model.live="customerId" class="erp-control w-auto min-w-[16rem] appearance-none pe-9">
                <option value="">{{ __('erp.statement_page.select_customer') }}</option>
                @foreach ($customers as $c)
                    <option value="{{ $c->id }}">{{ $c->code }} - {{ app()->getLocale() === 'ar' ? ($c->name_ar ?? $c->name_en) : ($c->name_en ?? $c->name_ar) }}</option>
                @endforeach
            </select>
        </x-slot:actions>
    </x-ui.page-header>

    @if ($items === null)
        <x-ui.empty-state :title="__('erp.statement_page.pick_title')" :message="__('erp.statement_page.pick_customer_hint')" />
    @elseif (empty($items))
        <x-ui.empty-state :message="__('erp.open_items.none')" />
    @else
        @include('livewire.partials.open-items-table', ['items' => $items, 'side' => 'ar'])
    @endif
</div>
