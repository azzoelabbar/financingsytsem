<div>
    <x-ui.page-header
        :title="__('erp.sales_invoice.create')"
        :description="__('erp.sales_invoice.create_hint')"
        :breadcrumbs="[
            ['label' => __('erp.nav.ar')],
            ['label' => __('erp.nav.sales_invoices'), 'href' => route('ar.invoices')],
            ['label' => __('erp.sales_invoice.create')],
        ]"
    />

    <form wire:submit="save" class="mizan-document-form mx-auto max-w-6xl">
        <x-ui.draft-journey />
        <x-ui.form-section :title="__('erp.form.parties')" :description="__('erp.sales_invoice.parties_hint')">
            <x-ui.searchable-select id="invoice-customer" wire:model="customer_id" :label="__('erp.sales_invoice.customer')" required :error="$errors->first('customer_id')">
                <option value="">{{ __('erp.sales_invoice.select_customer') }}</option>
                @foreach ($customers as $c)
                    <option value="{{ $c->id }}">{{ $c->code }} - {{ app()->getLocale() === 'ar' ? $c->name_ar : ($c->name_en ?: $c->name_ar) }}</option>
                @endforeach
            </x-ui.searchable-select>

            <x-ui.field :label="__('erp.sales_invoice.invoice_date')" for="invoice-date" required :error="$errors->first('invoice_date')" :hint="__('erp.sales_invoice.date_hint')">
                <input id="invoice-date" type="date" wire:model="invoice_date" class="erp-control {{ $errors->has('invoice_date') ? 'erp-control-invalid' : '' }}" dir="ltr" required />
            </x-ui.field>
        </x-ui.form-section>

        <x-ui.form-section :title="__('erp.form.lines')" :description="__('erp.form.lines_hint')" :columns="1">
            @include('livewire.partials.document-line-editor', [
                'lines' => $lines,
                'accountField' => 'revenue_account',
                'accountLabel' => __('erp.sales_invoice.revenue_account'),
                'accountHint' => __('erp.sales_invoice.revenue_account_hint'),
                'taxCodes' => $taxCodes,
            ])
            @error('lines')
                <p class="text-xs text-[var(--danger)]">{{ $message }}</p>
            @enderror
        </x-ui.form-section>

        <div class="px-5 pb-5">
            <div class="mizan-draft-guidance">
                <x-ui.icon name="document" class="h-7 w-7" />
                <div><p class="text-sm font-semibold">{{ __('erp.mizan.draft_heading') }}</p><p class="mt-1 text-xs leading-relaxed">{{ __('erp.mizan.draft_guidance') }}</p></div>
            </div>
        </div>
        <x-ui.form-actions :note="__('erp.form.draft_note')">
            <x-ui.button variant="secondary" :href="route('ar.invoices')">{{ __('erp.cancel') }}</x-ui.button>
            <x-ui.button type="submit" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="save">{{ __('erp.sales_invoice.save_draft') }}</span>
                <span wire:loading wire:target="save">{{ __('erp.saving') }}</span>
            </x-ui.button>
        </x-ui.form-actions>
    </form>
</div>
