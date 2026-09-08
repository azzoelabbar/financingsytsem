<div>
    <x-ui.page-header :breadcrumbs="[['label' => __('erp.nav.ar')], ['label' => __('erp.nav.customer_statement')]]" :title="__('erp.statement_page.ar_title')" :description="__('erp.statement_page.ar_hint')">
        <x-slot:actions>
            <x-ui.import-button kind="customer-report" />
            <x-ui.export-button />
            <x-ui.searchable-select wire:model.live="customerId" :block="false">
                <option value="">{{ __('erp.statement_page.select_customer') }}</option>
                @foreach ($customers as $c)
                    <option value="{{ $c->id }}">{{ $c->code }} - {{ app()->getLocale() === 'ar' ? ($c->name_ar ?? $c->name_en) : ($c->name_en ?? $c->name_ar) }}</option>
                @endforeach
            </x-ui.searchable-select>
        </x-slot:actions>
    </x-ui.page-header>

    @if ($statement === null)
        <x-ui.empty-state :title="__('erp.statement_page.pick_title')" :message="__('erp.statement_page.pick_customer_hint')" />
    @else
        @php $cur = $statement['customer']['currency'] ?? ''; @endphp
        <div class="mb-6 grid gap-4 sm:grid-cols-3">
            <x-ui.stat tone="brand" :label="__('erp.statement_page.balance_due')">
                <x-ui.money :amount="$statement['balance'] ?? '0'" :currency="$cur" size="hero" />
            </x-ui.stat>
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
        </div>

        @if (empty($statement['open_items']))
            <x-ui.empty-state :message="__('erp.statement_page.no_open')" />
        @else
            @include('livewire.partials.open-items-table', ['items' => $statement['open_items'], 'side' => 'ar'])
        @endif
    @endif
</div>
