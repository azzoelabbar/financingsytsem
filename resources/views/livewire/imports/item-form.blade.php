<div>
    <x-ui.page-header
        :title="$editing ? __('imports.item_edit') : __('imports.item_create')"
        :description="__('imports.item_form_hint')"
        :breadcrumbs="[
            ['label' => __('imports.kinds.items'), 'href' => route('imports.inventory')],
            ['label' => $editing ? __('imports.item_edit') : __('imports.item_create')],
        ]"
    />

    <form wire:submit="save" class="mx-auto max-w-4xl overflow-hidden rounded-lg border border-border bg-card">
        <x-ui.form-section :title="__('erp.form.identity')" :description="__('imports.item_identity_hint')">
            <x-ui.field :label="__('imports.item_code')" for="item-code" :required="! $codeLocked" :error="$errors->first('code')" :hint="$codeLocked ? __('imports.code_locked') : null">
                <input id="item-code" type="text" wire:model="code" @disabled($codeLocked) class="erp-control {{ $errors->has('code') ? 'erp-control-invalid' : '' }}" dir="ltr" />
            </x-ui.field>

            <x-ui.field :label="__('imports.item_name')" for="item-name" required :error="$errors->first('name')">
                <input id="item-name" type="text" wire:model="name" class="erp-control {{ $errors->has('name') ? 'erp-control-invalid' : '' }}" required />
            </x-ui.field>

            <x-ui.field :label="__('imports.category')" for="item-category" :error="$errors->first('category')">
                <input id="item-category" type="text" wire:model="category" maxlength="100" class="erp-control {{ $errors->has('category') ? 'erp-control-invalid' : '' }}" />
            </x-ui.field>

            <x-ui.field :label="__('imports.unit')" for="item-unit" :error="$errors->first('unit')">
                <input id="item-unit" type="text" wire:model="unit" maxlength="50" class="erp-control {{ $errors->has('unit') ? 'erp-control-invalid' : '' }}" />
            </x-ui.field>
        </x-ui.form-section>

        <x-ui.form-section :title="__('erp.form.financial')" :description="__('imports.item_accounts_hint')">
            <x-ui.field :label="__('imports.inventory_account')" for="item-gl" required :error="$errors->first('gl_account_code')" :hint="__('imports.field_hints.inventory_account')">
                @if ($inventoryAccounts->isEmpty())
                    <x-ui.alert variant="warning">{{ __('imports.no_accounts') }}</x-ui.alert>
                @else
                    <select id="item-gl" wire:model="gl_account_code" class="erp-control {{ $errors->has('gl_account_code') ? 'erp-control-invalid' : '' }}">
                        <option value="">{{ __('imports.select_account') }}</option>
                        @foreach ($inventoryAccounts as $account)
                            <option value="{{ $account->code }}">{{ $account->code }} — {{ app()->getLocale() === 'ar' ? $account->name_ar : ($account->name_en ?? $account->name_ar) }}</option>
                        @endforeach
                    </select>
                @endif
            </x-ui.field>

            <x-ui.field :label="__('imports.cogs_account')" for="item-cogs" required :error="$errors->first('cogs_account_code')" :hint="__('imports.field_hints.cogs_account')">
                @if ($cogsAccounts->isEmpty())
                    <x-ui.alert variant="warning">{{ __('imports.no_accounts') }}</x-ui.alert>
                @else
                    <select id="item-cogs" wire:model="cogs_account_code" class="erp-control {{ $errors->has('cogs_account_code') ? 'erp-control-invalid' : '' }}">
                        <option value="">{{ __('imports.select_account') }}</option>
                        @foreach ($cogsAccounts as $account)
                            <option value="{{ $account->code }}">{{ $account->code }} — {{ app()->getLocale() === 'ar' ? $account->name_ar : ($account->name_en ?? $account->name_ar) }}</option>
                        @endforeach
                    </select>
                @endif
            </x-ui.field>
        </x-ui.form-section>

        <x-ui.form-section :title="__('imports.item_catalog')" :description="__('imports.item_catalog_hint')">
            <x-ui.field :label="__('imports.standard_cost')" for="item-cost" :error="$errors->first('standard_cost')">
                <input id="item-cost" type="number" min="0" step="0.000001" wire:model="standard_cost" class="erp-control text-end tabular-nums {{ $errors->has('standard_cost') ? 'erp-control-invalid' : '' }}" dir="ltr" />
            </x-ui.field>

            <x-ui.field :label="__('imports.sale_price')" for="item-price" :error="$errors->first('sale_price')">
                <input id="item-price" type="number" min="0" step="0.000001" wire:model="sale_price" class="erp-control text-end tabular-nums {{ $errors->has('sale_price') ? 'erp-control-invalid' : '' }}" dir="ltr" />
            </x-ui.field>

            <x-ui.field :label="__('imports.reorder_level')" for="item-reorder" :error="$errors->first('reorder_level')">
                <input id="item-reorder" type="number" min="0" step="0.000001" wire:model="reorder_level" class="erp-control text-end tabular-nums {{ $errors->has('reorder_level') ? 'erp-control-invalid' : '' }}" dir="ltr" />
            </x-ui.field>
        </x-ui.form-section>

        <x-ui.form-actions :note="__('erp.form.required_note')">
            <x-ui.button variant="secondary" :href="route('imports.inventory')">{{ __('erp.cancel') }}</x-ui.button>
            <x-ui.button type="submit" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="save">{{ __('erp.save') }}</span>
                <span wire:loading wire:target="save">{{ __('erp.saving') }}</span>
            </x-ui.button>
        </x-ui.form-actions>
    </form>
</div>
