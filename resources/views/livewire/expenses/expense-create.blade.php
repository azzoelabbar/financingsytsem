<div>
    <x-ui.page-header :title="__('erp.expense.create')" :description="__('erp.expense.create_hint')" :breadcrumbs="[['label'=>__('erp.nav.expenses'),'href'=>route('expenses.index')],['label'=>__('erp.expense.create')]]" />
    <form wire:submit="save" class="mx-auto max-w-5xl overflow-hidden rounded-lg border border-border bg-card">
        @error('form') <div class="m-5"><x-ui.alert variant="danger">{{ $message }}</x-ui.alert></div> @enderror
        <x-ui.form-section :title="__('erp.expense.identity')">
            <x-ui.field :label="__('erp.number')" for="exp-number" required :error="$errors->first('number')"><input id="exp-number" wire:model="number" class="erp-control" dir="ltr" /></x-ui.field>
            <x-ui.field :label="__('erp.expense.employee')" for="exp-employee" required :error="$errors->first('employee_ref')"><input id="exp-employee" wire:model="employee_ref" class="erp-control" /></x-ui.field>
            <x-ui.field :label="__('erp.date')" for="exp-date" required :error="$errors->first('claim_date')"><input id="exp-date" type="date" wire:model="claim_date" class="erp-control" dir="ltr" /></x-ui.field>
            <x-ui.field :label="__('erp.currency')" for="exp-currency" required :error="$errors->first('currency')"><input id="exp-currency" wire:model="currency" maxlength="3" class="erp-control uppercase" dir="ltr" /></x-ui.field>
            <x-ui.field :label="__('erp.description')" for="exp-description" :error="$errors->first('description')"><input id="exp-description" wire:model="description" class="erp-control" /></x-ui.field>
        </x-ui.form-section>
        <x-ui.form-section :title="__('erp.expense.lines')">
            <x-ui.field :label="__('erp.account')" for="exp-account" required :error="$errors->first('expense_account')"><select id="exp-account" wire:model="expense_account" class="erp-control"><option value="">{{ __('erp.select') }}</option>@foreach($accounts as $account)<option value="{{ $account->code }}">{{ $account->code }} — {{ app()->getLocale()==='ar' ? $account->name_ar : ($account->name_en ?? $account->name_ar) }}</option>@endforeach</select></x-ui.field>
            <x-ui.field :label="__('erp.description')" for="exp-line-description" :error="$errors->first('line_description')"><input id="exp-line-description" wire:model="line_description" class="erp-control" /></x-ui.field>
            <x-ui.field :label="__('erp.amount')" for="exp-amount" required :error="$errors->first('amount')"><input id="exp-amount" type="number" min="0.000001" step="0.000001" wire:model="amount" class="erp-control text-end tabular-nums" dir="ltr" /></x-ui.field>
        </x-ui.form-section>
        <x-ui.form-actions :note="__('erp.form.draft_note')"><x-ui.button variant="secondary" :href="route('expenses.index')">{{ __('erp.cancel') }}</x-ui.button><x-ui.button type="submit" wire:loading.attr="disabled">{{ __('erp.expense.save_draft') }}</x-ui.button></x-ui.form-actions>
    </form>
</div>
