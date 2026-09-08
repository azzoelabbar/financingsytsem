<div>
    <x-ui.page-header :breadcrumbs="[['label' => __('erp.nav.ap')], ['label' => __('erp.nav.ap_reconciliation')]]" :title="__('erp.nav.ap_reconciliation')" :description="__('erp.reconciliation.ap_hint')">
        <x-slot:actions>
            <x-ui.export-button />
            <label class="flex items-center gap-2 text-sm text-muted-foreground">
                {{ __('erp.aging.as_of') }}
                <input type="date" wire:model.live="asOf" class="erp-control w-auto" dir="ltr" />
            </label>
        </x-slot:actions>
    </x-ui.page-header>

    @if ($result === null)
        @include('livewire.partials.empty-state')
    @else
        @php
            $passed = (bool) ($result['passed'] ?? false);
            $glBalance = (string) ($result['expected'] ?? '0');
            $subledger = (string) ($result['actual'] ?? '0');
            $difference = (float) $glBalance - (float) $subledger;
        @endphp

        <div class="mx-auto max-w-2xl overflow-hidden rounded-lg border border-border bg-card">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-border px-5 py-4"
                 style="background: {{ $passed ? 'var(--success-muted)' : 'var(--danger-muted)' }}">
                <div class="min-w-0">
                    <p class="text-base font-semibold text-foreground">
                        {{ $passed ? __('erp.reconciliation.matched_title') : __('erp.reconciliation.diff_title') }}
                    </p>
                    <p class="mt-0.5 max-w-lg text-xs leading-relaxed text-muted-foreground">
                        {{ $passed ? __('erp.reconciliation.matched_hint') : __('erp.reconciliation.diff_hint') }}
                    </p>
                </div>
                <x-ui.badge :variant="$passed ? 'success' : 'danger'">
                    {{ $passed ? __('erp.reconciliation.matched') : __('erp.reconciliation.difference') }}
                </x-ui.badge>
            </div>

            <dl class="divide-y divide-border">
                <div class="flex items-center justify-between gap-4 px-5 py-3">
                    <dt class="text-sm text-muted-foreground">{{ __('erp.reconciliation.gl_balance_ap') }}</dt>
                    <dd><x-ui.money :amount="$glBalance" :negative="(float) $glBalance < 0" /></dd>
                </div>
                <div class="flex items-center justify-between gap-4 px-5 py-3">
                    <dt class="text-sm text-muted-foreground">{{ __('erp.reconciliation.subledger_ap') }}</dt>
                    <dd><x-ui.money :amount="$subledger" :negative="(float) $subledger < 0" /></dd>
                </div>
                <div class="flex items-center justify-between gap-4 border-t border-border bg-surface-sunken px-5 py-3.5">
                    <dt class="text-sm font-semibold">{{ __('erp.reconciliation.difference_amount') }}</dt>
                    <dd class="text-base font-bold"><x-ui.money :amount="$difference" :negative="$difference < 0" /></dd>
                </div>
            </dl>
        </div>
    @endif
</div>
