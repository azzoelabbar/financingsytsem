<div>
    <x-ui.page-header
        :title="__('erp.supplier.create')"
        :description="__('erp.supplier.create_hint')"
        :breadcrumbs="[
            ['label' => __('erp.nav.ap')],
            ['label' => __('erp.nav.suppliers'), 'href' => route('ap.suppliers')],
            ['label' => __('erp.supplier.create')],
        ]"
    />

    <form wire:submit="save" class="mx-auto max-w-4xl overflow-hidden rounded-lg border border-border bg-card">
        <x-ui.form-section :title="__('erp.form.identity')" :description="__('erp.supplier.identity_hint')">
            <x-ui.field :label="__('erp.code')" for="supplier-code" required :error="$errors->first('code')" :hint="__('erp.supplier.code_hint')">
                <input id="supplier-code" type="text" wire:model="code" class="erp-control {{ $errors->has('code') ? 'erp-control-invalid' : '' }}" dir="ltr" required />
            </x-ui.field>

            <x-ui.field :label="__('erp.supplier.legal_name')" for="supplier-name" required :error="$errors->first('legal_name')">
                <input id="supplier-name" type="text" wire:model="legal_name" class="erp-control {{ $errors->has('legal_name') ? 'erp-control-invalid' : '' }}" required />
            </x-ui.field>
        </x-ui.form-section>

        <x-ui.form-section :title="__('erp.form.financial')" :description="__('erp.supplier.currency_hint')">
            <x-ui.field :label="__('erp.currency')" for="supplier-currency" :error="$errors->first('currency')">
                <input id="supplier-currency" type="text" wire:model="currency" maxlength="3" class="erp-control {{ $errors->has('currency') ? 'erp-control-invalid' : '' }} uppercase" dir="ltr" />
            </x-ui.field>
        </x-ui.form-section>

        <x-ui.form-actions :note="__('erp.form.required_note')">
            <x-ui.button variant="secondary" :href="route('ap.suppliers')">{{ __('erp.cancel') }}</x-ui.button>
            <x-ui.button type="submit" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="save">{{ __('erp.save') }}</span>
                <span wire:loading wire:target="save">{{ __('erp.saving') }}</span>
            </x-ui.button>
        </x-ui.form-actions>
    </form>
</div>
