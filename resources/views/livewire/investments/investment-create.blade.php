<div>
    <x-ui.page-header :title="__('erp.investment.create')" :description="__('erp.investment.create_hint')" :breadcrumbs="[['label'=>__('erp.nav.investments'),'href'=>route('investments.index')],['label'=>__('erp.investment.create')]]" />
    <form wire:submit="save" class="mx-auto max-w-5xl overflow-hidden rounded-lg border border-border bg-card">
        @error('form') <div class="m-5"><x-ui.alert variant="danger">{{ $message }}</x-ui.alert></div> @enderror
        <x-ui.form-section :title="__('erp.investment.identity')">
            <x-ui.field :label="__('erp.code')" for="inv-code" required :error="$errors->first('code')"><input id="inv-code" wire:model="code" class="erp-control" dir="ltr" /></x-ui.field>
            <x-ui.field :label="__('erp.investment.name')" for="inv-name" required :error="$errors->first('name')"><input id="inv-name" wire:model="name" class="erp-control" /></x-ui.field>
            <x-ui.field :label="__('erp.investment.classification')" for="inv-class" required :error="$errors->first('classification')"><select id="inv-class" wire:model="classification" class="erp-control">@foreach($classifications as $class)<option value="{{ $class->value }}">{{ __('erp.investment.classifications.'.$class->value) }}</option>@endforeach</select></x-ui.field>
            <x-ui.field :label="__('erp.investment.instrument_type')" for="inv-type" required :error="$errors->first('instrument_type')"><input id="inv-type" wire:model="instrument_type" class="erp-control" /></x-ui.field>
        </x-ui.form-section>
        <x-ui.form-section :title="__('erp.investment.acquisition')">
            <x-ui.field :label="__('erp.date')" for="inv-date" required :error="$errors->first('date')"><input id="inv-date" type="date" wire:model="date" class="erp-control" dir="ltr" /></x-ui.field>
            <x-ui.field :label="__('erp.currency')" for="inv-currency" required :error="$errors->first('currency')"><input id="inv-currency" wire:model="currency" maxlength="3" class="erp-control uppercase" dir="ltr" /></x-ui.field>
            <x-ui.field :label="__('erp.investment.quantity')" for="inv-quantity" required :error="$errors->first('quantity')"><input id="inv-quantity" type="number" min="0.000001" step="0.000001" wire:model="quantity" class="erp-control text-end tabular-nums" dir="ltr" /></x-ui.field>
            <x-ui.field :label="__('erp.investment.cost')" for="inv-cost" required :error="$errors->first('cost')"><input id="inv-cost" type="number" min="0.000001" step="0.000001" wire:model="cost" class="erp-control text-end tabular-nums" dir="ltr" /></x-ui.field>
        </x-ui.form-section>
        <x-ui.form-actions><x-ui.button variant="secondary" :href="route('investments.index')">{{ __('erp.cancel') }}</x-ui.button><x-ui.button type="submit">{{ __('erp.investment.acquire') }}</x-ui.button></x-ui.form-actions>
    </form>
</div>
