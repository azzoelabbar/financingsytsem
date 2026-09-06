<div>
    <x-ui.page-header
        :title="__('erp.customer.create')"
        :description="__('erp.customer.create_hint')"
        :breadcrumbs="[
            ['label' => __('erp.nav.ar')],
            ['label' => __('erp.nav.customers'), 'href' => route('ar.customers')],
            ['label' => __('erp.customer.create')],
        ]"
    />

    <form wire:submit="save" class="mx-auto max-w-4xl overflow-hidden rounded-lg border border-border bg-card">
        <x-ui.form-section :title="__('erp.form.identity')" :description="__('erp.customer.identity_hint')">
            <x-ui.field :label="__('erp.customer.code')" for="customer-code" required :error="$errors->first('code')" :hint="__('erp.customer.code_hint')">
                <input id="customer-code" type="text" wire:model="code" class="erp-control {{ $errors->has('code') ? 'erp-control-invalid' : '' }}" dir="ltr" required />
            </x-ui.field>

            <x-ui.field :label="__('erp.customer.name_ar')" for="customer-name" required :error="$errors->first('name_ar')">
                <input id="customer-name" type="text" wire:model="name_ar" class="erp-control {{ $errors->has('name_ar') ? 'erp-control-invalid' : '' }}" required />
            </x-ui.field>
        </x-ui.form-section>

        <x-ui.form-section :title="__('erp.form.financial')" :description="__('erp.customer.currency_hint')">
            <x-ui.field :label="__('erp.currency')" for="customer-currency" :error="$errors->first('currency')">
                <input id="customer-currency" type="text" wire:model="currency" maxlength="3" class="erp-control {{ $errors->has('currency') ? 'erp-control-invalid' : '' }} uppercase" dir="ltr" />
            </x-ui.field>
        </x-ui.form-section>

        <x-ui.form-actions :note="__('erp.form.required_note')">
            <x-ui.button variant="secondary" :href="route('ar.customers')">{{ __('erp.cancel') }}</x-ui.button>
            <x-ui.button type="submit" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="save">{{ __('erp.save') }}</span>
                <span wire:loading wire:target="save">{{ __('erp.saving') }}</span>
            </x-ui.button>
        </x-ui.form-actions>
    </form>
</div>
