<div>
    <x-ui.page-header :title="__('erp.tax.create_code')" :breadcrumbs="[['label'=>__('erp.nav.tax_codes'),'href'=>route('tax.index')],['label'=>__('erp.tax.create_code')]]" />
    <form wire:submit="save" class="mx-auto max-w-4xl overflow-hidden rounded-lg border border-border bg-card">
        <x-ui.form-section :title="__('erp.tax.code_details')">
            <x-ui.field :label="__('erp.code')" for="tax-code" required :error="$errors->first('code')"><input id="tax-code" wire:model="code" class="erp-control" dir="ltr" /></x-ui.field>
            <x-ui.field :label="__('erp.name')" for="tax-name" required :error="$errors->first('name')"><input id="tax-name" wire:model="name" class="erp-control" /></x-ui.field>
            <x-ui.field :label="__('erp.tax.kind')" for="tax-kind" required><select id="tax-kind" wire:model="kind" class="erp-control">@foreach(['output_vat','input_vat','wht','cit','deferred'] as $kind)<option value="{{ $kind }}">{{ __('erp.tax.kinds.'.$kind) }}</option>@endforeach</select></x-ui.field>
            <x-ui.field :label="__('erp.tax.gl_account')" for="tax-account" required :error="$errors->first('gl_account_code')"><select id="tax-account" wire:model="gl_account_code" class="erp-control"><option value="">{{ __('erp.select') }}</option>@foreach($accounts as $account)<option value="{{ $account->code }}">{{ $account->code }} — {{ app()->getLocale()==='ar'?$account->name_ar:($account->name_en??$account->name_ar) }}</option>@endforeach</select></x-ui.field>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="is_active" />{{ __('erp.tax.active') }}</label>
        </x-ui.form-section>
        <x-ui.form-actions><x-ui.button variant="secondary" :href="route('tax.index')">{{ __('erp.cancel') }}</x-ui.button><x-ui.button type="submit">{{ __('erp.create') }}</x-ui.button></x-ui.form-actions>
    </form>
</div>
