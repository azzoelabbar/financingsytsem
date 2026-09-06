<div>
    <x-ui.page-header :title="__('erp.assets.create')" :description="__('erp.assets.create_hint')" :breadcrumbs="[['label'=>__('erp.nav.assets'),'href'=>route('assets.index')],['label'=>__('erp.assets.create')]]" />
    <form wire:submit="save" class="mx-auto max-w-5xl overflow-hidden rounded-lg border border-border bg-card">
        @error('form') <div class="m-5"><x-ui.alert variant="danger">{{ $message }}</x-ui.alert></div> @enderror
        <x-ui.form-section :title="__('erp.assets.identity')">
            <x-ui.field :label="__('erp.code')" for="asset-code" required :error="$errors->first('code')"><input id="asset-code" wire:model="code" class="erp-control" dir="ltr" /></x-ui.field>
            <x-ui.field :label="__('erp.assets.name')" for="asset-name" required :error="$errors->first('name')"><input id="asset-name" wire:model="name" class="erp-control" /></x-ui.field>
            <x-ui.field :label="__('erp.assets.cost')" for="asset-cost" required :error="$errors->first('cost')"><input id="asset-cost" type="number" min="0.000001" step="0.000001" wire:model="cost" class="erp-control text-end tabular-nums" dir="ltr" /></x-ui.field>
            <x-ui.field :label="__('erp.assets.useful_life')" for="asset-life" required :error="$errors->first('useful_life_months')"><input id="asset-life" type="number" min="1" wire:model="useful_life_months" class="erp-control text-end tabular-nums" dir="ltr" /></x-ui.field>
            <x-ui.field :label="__('erp.assets.in_service')" for="asset-date" required :error="$errors->first('in_service_date')"><input id="asset-date" type="date" wire:model="in_service_date" class="erp-control" dir="ltr" /></x-ui.field>
            <x-ui.field :label="__('erp.assets.location')" for="asset-location" :error="$errors->first('location')"><input id="asset-location" wire:model="location" class="erp-control" /></x-ui.field>
        </x-ui.form-section>
        <x-ui.form-section :title="__('erp.document.accounting')">
            @foreach(['cost_account_code'=>__('erp.assets.cost_account'),'accum_account_code'=>__('erp.assets.accum_account'),'expense_account_code'=>__('erp.assets.expense_account'),'ap_account_code'=>__('erp.assets.ap_account')] as $field=>$label)
                <x-ui.field :label="$label" :for="'asset-'.$field" required :error="$errors->first($field)"><select id="asset-{{ $field }}" wire:model="{{ $field }}" class="erp-control">@foreach($field === 'ap_account_code' ? $settlementAccounts : $accounts as $account)<option value="{{ $account->code }}">{{ $account->code }} — {{ app()->getLocale()==='ar' ? $account->name_ar : ($account->name_en ?? $account->name_ar) }}</option>@endforeach</select></x-ui.field>
            @endforeach
        </x-ui.form-section>
        <x-ui.form-actions><x-ui.button variant="secondary" :href="route('assets.index')">{{ __('erp.cancel') }}</x-ui.button><x-ui.button type="submit">{{ __('erp.assets.acquire') }}</x-ui.button></x-ui.form-actions>
    </form>
</div>
