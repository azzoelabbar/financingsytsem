<div>
    <x-ui.page-header
        :title="__('erp.receipt.create')"
        :description="__('erp.receipt.create_hint')"
        :breadcrumbs="[
            ['label' => __('erp.nav.ar')],
            ['label' => __('erp.nav.receipts'), 'href' => route('ar.receipts')],
            ['label' => __('erp.receipt.create')],
        ]"
    />

    <form wire:submit="save" class="mx-auto max-w-5xl overflow-hidden rounded-lg border border-border bg-card">
        <x-ui.form-section :title="__('erp.form.parties')" :description="__('erp.receipt.parties_hint')">
            <x-ui.searchable-select :label="__('erp.sales_invoice.customer')" required :error="$errors->first('customer_id')" id="receipt-customer" wire:model.live="customer_id" required>
                    <option value="">{{ __('erp.sales_invoice.select_customer') }}</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->code }} — {{ app()->getLocale() === 'ar' ? ($customer->name_ar ?? $customer->name_en) : ($customer->name_en ?? $customer->name_ar) }}</option>
                    @endforeach
                </x-ui.searchable-select>

            <x-ui.field :label="__('erp.receipt.date')" for="receipt-date" required :error="$errors->first('receipt_date')">
                <input id="receipt-date" type="date" wire:model="receipt_date" class="erp-control {{ $errors->has('receipt_date') ? 'erp-control-invalid' : '' }}" dir="ltr" required />
            </x-ui.field>
        </x-ui.form-section>

        <x-ui.form-section :title="__('erp.receipt.payment_details')" :description="__('erp.receipt.payment_hint')">
            <x-ui.field :label="__('erp.receipt.amount')" for="receipt-amount" required :error="$errors->first('amount')">
                <input id="receipt-amount" type="number" min="0.000001" step="0.000001" wire:model="amount" class="erp-control text-end tabular-nums {{ $errors->has('amount') ? 'erp-control-invalid' : '' }}" dir="ltr" required />
            </x-ui.field>

            <x-ui.field :label="__('erp.currency')" for="receipt-currency" required :error="$errors->first('currency')">
                <input id="receipt-currency" wire:model="currency" maxlength="3" class="erp-control uppercase {{ $errors->has('currency') ? 'erp-control-invalid' : '' }}" dir="ltr" required />
            </x-ui.field>

            <x-ui.searchable-select id="receipt-method" wire:model="method" :label="__('erp.receipt.method')" required :error="$errors->first('method')">
                <option value="bank">{{ __('erp.receipt.method_bank') }}</option>
                <option value="cash">{{ __('erp.receipt.method_cash') }}</option>
            </x-ui.searchable-select>

            <x-ui.searchable-select :label="__('erp.receipt.cash_bank_account')" required :error="$errors->first('cash_bank_account')" id="receipt-account" wire:model="cash_bank_account" required>
                    @foreach ($accounts as $account)
                        <option value="{{ $account->code }}">{{ $account->code }} — {{ app()->getLocale() === 'ar' ? $account->name_ar : ($account->name_en ?? $account->name_ar) }}</option>
                    @endforeach
                </x-ui.searchable-select>

            <x-ui.field :label="__('erp.document.exchange_rate')" for="receipt-rate" required :error="$errors->first('exchange_rate')">
                <input id="receipt-rate" type="number" min="0.0000000001" step="0.0000000001" wire:model="exchange_rate" class="erp-control text-end tabular-nums {{ $errors->has('exchange_rate') ? 'erp-control-invalid' : '' }}" dir="ltr" required />
            </x-ui.field>

            <x-ui.field :label="__('erp.document.reference')" for="receipt-reference" :error="$errors->first('reference')">
                <input id="receipt-reference" wire:model="reference" class="erp-control {{ $errors->has('reference') ? 'erp-control-invalid' : '' }}" />
            </x-ui.field>
        </x-ui.form-section>

        <x-ui.form-actions :note="__('erp.form.draft_note')">
            <x-ui.button variant="secondary" :href="route('ar.receipts')">{{ __('erp.cancel') }}</x-ui.button>
            <x-ui.button type="submit" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="save">{{ __('erp.receipt.save_draft') }}</span>
                <span wire:loading wire:target="save">{{ __('erp.saving') }}</span>
            </x-ui.button>
        </x-ui.form-actions>
    </form>
</div>
