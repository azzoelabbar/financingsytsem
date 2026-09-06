<div>
    <x-ui.page-header :breadcrumbs="[['label' => __('erp.nav.ap')], ['label' => __('erp.nav.supplier_statement')]]" :title="__('erp.statement_page.ap_title')" :description="__('erp.statement_page.ap_hint')">
        <x-slot:actions>
            <x-ui.searchable-select wire:model.live="supplierId" :block="false">
                <option value="">{{ __('erp.statement_page.select_supplier') }}</option>
                @foreach ($suppliers as $s)
                    <option value="{{ $s->id }}">{{ $s->code }} - {{ app()->getLocale() === 'ar' ? ($s->name_ar ?? $s->legal_name) : ($s->trading_name ?? $s->legal_name ?? $s->name_ar) }}</option>
                @endforeach
            </x-ui.searchable-select>
        </x-slot:actions>
    </x-ui.page-header>

    @if ($statement === null)
        <x-ui.empty-state :title="__('erp.statement_page.pick_title')" :message="__('erp.statement_page.pick_supplier_hint')" />
    @else
        @php $cur = $statement['supplier']['currency'] ?? ($statement['customer']['currency'] ?? ''); @endphp
        <div class="mb-6 grid gap-4 sm:grid-cols-3">
            <x-ui.stat tone="brand" :label="__('erp.statement_page.balance_owed')">
                <x-ui.money :amount="$statement['balance'] ?? '0'" :currency="$cur" size="hero" />
            </x-ui.stat>
            @if (! empty($statement['aging']['buckets']))
                <x-ui.card class="sm:col-span-2" :title="__('erp.statement_page.aging')" :padding="false">
                    <div class="grid grid-cols-2 gap-x-6 gap-y-2 p-4 sm:grid-cols-5">
                        @foreach (['current', '1_30', '31_60', '61_90', '90_plus'] as $bucket)
                            <div>
                                <p class="text-xs text-muted-foreground">{{ __('erp.aging.'.$bucket) }}</p>
                                <p class="mt-0.5 text-sm font-medium tabular-nums"><x-ui.money :amount="$statement['aging']['buckets'][$bucket] ?? '0'" /></p>
                            </div>
                        @endforeach
                    </div>
                </x-ui.card>
            @endif
        </div>

        @if (empty($statement['open_items']))
            <x-ui.empty-state :message="__('erp.statement_page.no_open')" />
        @else
            @include('livewire.partials.open-items-table', ['items' => $statement['open_items'], 'side' => 'ap'])
        @endif
    @endif
</div>
