<div><x-ui.page-header :title="$type==='bank'?__('erp.banking.create_bank_account'):__('erp.banking.create_cash_account')" :description="__('erp.banking.account_create_hint')" :breadcrumbs="[['label'=>__('erp.nav.banking')],['label'=>$type==='bank'?__('erp.banking.bank_accounts'):__('erp.banking.cash_accounts')]]" />
<form wire:submit="save" class="mx-auto max-w-5xl overflow-hidden rounded-lg border border-border bg-card">
@error('account')<x-ui.alert variant="danger" class="m-5">{{ $message }}</x-ui.alert>@enderror
<x-ui.form-section :title="__('erp.banking.account_identity')" :description="__('erp.banking.account_identity_hint')">
<x-ui.field :label="__('erp.code')" for="treasury-code" required :error="$errors->first('code')"><input id="treasury-code" wire:model="code" class="erp-control" dir="ltr" /></x-ui.field>
<x-ui.field :label="__('erp.name_ar')" for="treasury-name-ar" required :error="$errors->first('name_ar')"><input id="treasury-name-ar" wire:model="name_ar" class="erp-control" dir="rtl" /></x-ui.field>
<x-ui.field :label="__('erp.name_en')" for="treasury-name-en"><input id="treasury-name-en" wire:model="name_en" class="erp-control" dir="ltr" /></x-ui.field>
<x-ui.field :label="__('erp.currency')" for="treasury-currency" required><input id="treasury-currency" wire:model="currency" maxlength="3" class="erp-control uppercase" dir="ltr" /></x-ui.field>
<x-ui.searchable-select :label="__('erp.banking.gl_account')" required :error="$errors->first('gl_account_code')" id="treasury-gl" wire:model="gl_account_code">@foreach($glAccounts as $gl)<option value="{{ $gl->code }}">{{ $gl->code }} — {{ app()->getLocale()==='ar'?$gl->name_ar:($gl->name_en??$gl->name_ar) }}</option>@endforeach</x-ui.searchable-select>
</x-ui.form-section>
@if($type==='bank')<x-ui.form-section :title="__('erp.banking.bank_details')">
<x-ui.field :label="__('erp.banking.bank_code')" for="bank-code" required :error="$errors->first('bank_code')"><input id="bank-code" wire:model="bank_code" class="erp-control" dir="ltr" /></x-ui.field>
<x-ui.field :label="__('erp.banking.bank_name_ar')" for="bank-name-ar" required><input id="bank-name-ar" wire:model="bank_name_ar" class="erp-control" dir="rtl" /></x-ui.field>
<x-ui.field :label="__('erp.banking.bank_name_en')" for="bank-name-en"><input id="bank-name-en" wire:model="bank_name_en" class="erp-control" dir="ltr" /></x-ui.field>
<x-ui.field :label="__('erp.banking.account_number')" for="account-number"><input id="account-number" wire:model="account_number" class="erp-control" dir="ltr" /></x-ui.field>
<x-ui.field label="IBAN" for="account-iban"><input id="account-iban" wire:model="iban" class="erp-control" dir="ltr" /></x-ui.field>
<x-ui.field label="SWIFT" for="bank-swift"><input id="bank-swift" wire:model="swift" class="erp-control" dir="ltr" /></x-ui.field>
</x-ui.form-section>@endif
<x-ui.form-actions><x-ui.button variant="secondary" :href="$type==='bank'?route('banking.bank-accounts'):route('banking.cash-accounts')">{{ __('erp.cancel') }}</x-ui.button><x-ui.button type="submit">{{ __('erp.save') }}</x-ui.button></x-ui.form-actions></form></div>
