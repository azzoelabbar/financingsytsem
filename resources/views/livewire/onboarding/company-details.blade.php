<div>
    <div class="mb-6 text-center">
        <h1 class="text-xl font-semibold tracking-tight">{{ __('erp.onboarding.company_title') }}</h1>
        <p class="mt-1.5 text-sm text-muted-foreground">{{ __('erp.onboarding.company_subtitle') }}</p>
    </div>

    <x-ui.card>
        <form wire:submit="continue" class="space-y-5">
            <x-ui.input
                id="name_ar"
                wire:model.blur="name_ar"
                :label="__('erp.onboarding.field_name_ar')"
                :error="$errors->first('name_ar')"
                required
            />

            <x-ui.input
                id="name_en"
                wire:model.blur="name_en"
                :label="__('erp.onboarding.field_name_en')"
                :hint="__('erp.onboarding.optional')"
                :error="$errors->first('name_en')"
                dir="ltr"
            />

            <div class="grid gap-5 sm:grid-cols-2">
                <x-ui.input
                    id="code"
                    wire:model="code"
                    :label="__('erp.onboarding.field_code')"
                    :hint="__('erp.onboarding.code_hint')"
                    :error="$errors->first('code')"
                    dir="ltr"
                    required
                />

                <x-ui.select
                    id="country"
                    wire:model="country"
                    :label="__('erp.onboarding.field_country')"
                    :error="$errors->first('country')"
                    required
                >
                    @foreach ($countries as $iso => $name)
                        <option value="{{ $iso }}">{{ $name }}</option>
                    @endforeach
                </x-ui.select>
            </div>

            <x-ui.input
                id="tax_registration_number"
                wire:model.blur="tax_registration_number"
                :label="__('erp.onboarding.field_tax_number')"
                :hint="__('erp.onboarding.optional')"
                :error="$errors->first('tax_registration_number')"
                dir="ltr"
            />

            <div class="flex items-center justify-end gap-3 border-t border-border pt-5">
                <x-ui.button type="submit">
                    <span wire:loading.remove wire:target="continue">{{ __('erp.onboarding.continue') }}</span>
                    <span wire:loading wire:target="continue">{{ __('erp.onboarding.saving') }}</span>
                    <svg class="h-4 w-4 rtl:rotate-180" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.3 4.3a1 1 0 011.4 0l5 5a1 1 0 010 1.4l-5 5a1 1 0 01-1.4-1.4L11.6 10 7.3 5.7a1 1 0 010-1.4z" clip-rule="evenodd"/></svg>
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>

    <p class="mt-4 text-center text-xs text-muted-foreground">{{ __('erp.onboarding.step_indicator', ['current' => 1, 'total' => 2]) }}</p>
</div>
