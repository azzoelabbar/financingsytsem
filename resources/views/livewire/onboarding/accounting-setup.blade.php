<div>
    <div class="mb-6 text-center">
        <h1 class="text-xl font-semibold tracking-tight">{{ __('erp.onboarding.accounting_title') }}</h1>
        <p class="mt-1.5 text-sm text-muted-foreground">{{ __('erp.onboarding.accounting_subtitle') }}</p>
    </div>

    <x-ui.card>
        <form wire:submit="finish" class="space-y-5">
            <div class="grid gap-5 sm:grid-cols-2">
                <x-ui.searchable-select id="functional_currency" wire:model="functional_currency" :label="__('erp.onboarding.field_functional_currency')" :hint="__('erp.onboarding.functional_hint')" :error="$errors->first('functional_currency')" required>
                    @foreach ($currencies as $currency)
                        <option value="{{ $currency->code }}">{{ $currency->code }} — {{ app()->getLocale() === 'ar' ? $currency->name_ar : ($currency->name_en ?? $currency->name_ar) }}</option>
                    @endforeach
                </x-ui.searchable-select>

                <x-ui.searchable-select id="presentation_currency" wire:model="presentation_currency" :label="__('erp.onboarding.field_presentation_currency')" :error="$errors->first('presentation_currency')" required>
                    @foreach ($currencies as $currency)
                        <option value="{{ $currency->code }}">{{ $currency->code }} — {{ app()->getLocale() === 'ar' ? $currency->name_ar : ($currency->name_en ?? $currency->name_ar) }}</option>
                    @endforeach
                </x-ui.searchable-select>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <x-ui.searchable-select id="accounting_framework" wire:model="accounting_framework" :label="__('erp.onboarding.field_framework')" :error="$errors->first('accounting_framework')" required>
                    @foreach ($frameworks as $framework)
                        <option value="{{ $framework->value }}">{{ __('erp.onboarding.framework.'.$framework->value) }}</option>
                    @endforeach
                </x-ui.searchable-select>

                <x-ui.input id="fiscal_year" type="number" wire:model="fiscal_year" :label="__('erp.onboarding.field_fiscal_year')" :hint="__('erp.onboarding.fiscal_year_hint')" :error="$errors->first('fiscal_year')" dir="ltr" required />
            </div>

            {{-- What will be set up --}}
            <div class="rounded-md border border-border bg-surface-sunken p-4">
                <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-muted-foreground">{{ __('erp.onboarding.will_create') }}</p>
                <ul class="space-y-1.5 text-sm text-foreground/90">
                    @foreach (['books', 'calendar', 'chart', 'dimensions'] as $item)
                        <li class="flex items-center gap-2">
                            <svg class="h-4 w-4 shrink-0 text-[var(--success-color)]" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 011.4-1.4l3.3 3.3 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
                            {{ __('erp.onboarding.will_create_'.$item) }}
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="flex items-center justify-between gap-3 border-t border-border pt-5">
                <x-ui.button type="button" variant="ghost" :href="route('onboarding.company')" wire:navigate>
                    <svg class="h-4 w-4 rtl:rotate-180" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.7 4.3a1 1 0 010 1.4L8.4 10l4.3 4.3a1 1 0 01-1.4 1.4l-5-5a1 1 0 010-1.4l5-5a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
                    {{ __('erp.onboarding.back') }}
                </x-ui.button>

                <x-ui.button type="submit" wire:loading.attr="disabled" wire:target="finish">
                    <span wire:loading.remove wire:target="finish">{{ __('erp.onboarding.finish') }}</span>
                    <span wire:loading wire:target="finish" class="flex items-center gap-2">
                        <span class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
                        {{ __('erp.onboarding.provisioning') }}
                    </span>
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>

    <p class="mt-4 text-center text-xs text-muted-foreground">{{ __('erp.onboarding.step_indicator', ['current' => 2, 'total' => 2]) }}</p>
</div>
