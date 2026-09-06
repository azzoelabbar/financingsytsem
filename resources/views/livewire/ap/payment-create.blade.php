<div>
    <x-ui.page-header :title="__('erp.payment.create')" :description="__('erp.payment.create_hint')" :breadcrumbs="[['label'=>__('erp.nav.ap')],['label'=>__('erp.nav.supplier_payments'),'href'=>route('ap.payments')],['label'=>__('erp.payment.create')]]" />
    <form wire:submit="save" class="mx-auto max-w-5xl overflow-hidden rounded-lg border border-border bg-card">
        <x-ui.form-section :title="__('erp.form.parties')" :description="__('erp.payment.parties_hint')">
            <x-ui.searchable-select :label="__('erp.purchase_invoice.supplier')" required :error="$errors->first('supplier_id')" id="payment-supplier" wire:model.live="supplier_id" required><option value="">{{ __('erp.purchase_invoice.select_supplier') }}</option>@foreach($suppliers as $supplier)<option value="{{ $supplier->id }}">{{ $supplier->code }} — {{ $supplier->legal_name }}</option>@endforeach</x-ui.searchable-select>
            <x-ui.field :label="__('erp.payment.date')" for="payment-date" required :error="$errors->first('payment_date')"><input id="payment-date" type="date" wire:model="payment_date" class="erp-control" dir="ltr" required /></x-ui.field>
        </x-ui.form-section>
        <x-ui.form-section :title="__('erp.payment.details')" :description="__('erp.payment.details_hint')">
            <x-ui.field :label="__('erp.payment.amount')" for="payment-amount" required :error="$errors->first('amount')"><input id="payment-amount" type="number" min="0.000001" step="0.000001" wire:model="amount" class="erp-control text-end tabular-nums" dir="ltr" required /></x-ui.field>
            <x-ui.field :label="__('erp.currency')" for="payment-currency" required :error="$errors->first('currency')"><input id="payment-currency" wire:model="currency" maxlength="3" class="erp-control uppercase" dir="ltr" /></x-ui.field>
            <x-ui.searchable-select id="payment-method" wire:model="method" :label="__('erp.payment.method')" required><option value="bank">{{ __('erp.receipt.method_bank') }}</option><option value="cash">{{ __('erp.receipt.method_cash') }}</option></x-ui.searchable-select>
            <x-ui.searchable-select :label="__('erp.receipt.cash_bank_account')" required :error="$errors->first('cash_bank_account')" id="payment-account" wire:model="cash_bank_account">@foreach($accounts as $account)<option value="{{ $account->code }}">{{ $account->code }} — {{ app()->getLocale()==='ar' ? $account->name_ar : ($account->name_en ?? $account->name_ar) }}</option>@endforeach</x-ui.searchable-select>
            <x-ui.field :label="__('erp.document.exchange_rate')" for="payment-rate" required><input id="payment-rate" type="number" min="0.0000000001" step="0.0000000001" wire:model="exchange_rate" class="erp-control text-end tabular-nums" dir="ltr" /></x-ui.field>
            <x-ui.field :label="__('erp.document.reference')" for="payment-reference"><input id="payment-reference" wire:model="reference" class="erp-control" /></x-ui.field>
        </x-ui.form-section>
        <x-ui.form-actions :note="__('erp.form.draft_note')"><x-ui.button variant="secondary" :href="route('ap.payments')">{{ __('erp.cancel') }}</x-ui.button><x-ui.button type="submit">{{ __('erp.payment.save_draft') }}</x-ui.button></x-ui.form-actions>
    </form>
</div>
