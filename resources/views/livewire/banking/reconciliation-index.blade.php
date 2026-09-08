<div>
    <x-ui.page-header :breadcrumbs="[['label' => __('erp.nav.banking')], ['label' => __('erp.banking.reconciliation')]]" :title="__('erp.banking.reconciliation')" :description="__('erp.banking.reconciliation_hint')"><x-slot:actions><x-ui.export-button /><x-ui.button :href="route('banking.reconciliation.create')">{{ __('erp.banking.new_reconciliation') }}</x-ui.button></x-slot:actions></x-ui.page-header>

    <x-ui.toolbar
        :placeholder="__('erp.banking.recon_search_placeholder')"
        :summary="$reconciliations ? trans_choice('erp.pagination.result_count', $reconciliations->total(), ['count' => number_format($reconciliations->total())]) : null"
    />

    @if ($reconciliations === null || $reconciliations->isEmpty())
        <x-ui.empty-state
            :title="$search !== '' ? __('erp.filter.no_matches_title') : __('erp.banking.recon_empty_title')"
            :message="$search !== '' ? __('erp.filter.no_matches_hint') : __('erp.banking.recon_empty_hint')"
        >
            <x-slot:actions>
                @if ($search !== '')
                    <x-ui.button variant="secondary" wire:click="$set('search', '')">{{ __('erp.filter.clear') }}</x-ui.button>
                @else
                    <x-ui.button :href="route('banking.reconciliation.create')">{{ __('erp.banking.new_reconciliation') }}</x-ui.button>
                @endif
            </x-slot:actions>
        </x-ui.empty-state>
    @else
        <div class="space-y-3">
            @foreach ($reconciliations as $rec)
                @php $matched = abs((float) $rec->difference) < 0.0000001; @endphp
                <div wire:key="rec-{{ $rec->id }}" class="overflow-hidden rounded-lg border border-border bg-card">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-border px-5 py-3">
                        <div>
                            <p class="text-sm font-semibold"><a href="{{ route('banking.reconciliation.show',$rec) }}" wire:navigate class="text-[var(--brand-600)]">{{ app()->getLocale() === 'ar' ? ($rec->treasuryAccount?->name_ar ?? $rec->treasuryAccount?->name_en) : ($rec->treasuryAccount?->name_en ?? $rec->treasuryAccount?->name_ar) }}</a></p>
                            <p class="text-xs text-muted-foreground tabular-nums" dir="ltr">{{ $rec->as_of_date?->format('Y-m-d') }}</p>
                        </div>
                        <x-ui.badge :variant="$matched ? 'success' : 'danger'">{{ $matched ? __('erp.reconciliation.matched') : __('erp.reconciliation.difference') }}</x-ui.badge>
                    </div>
                    <dl class="grid grid-cols-2 divide-x divide-border rtl:divide-x-reverse sm:grid-cols-4 [&>div]:px-5 [&>div]:py-3">
                        <div><dt class="text-xs text-muted-foreground">{{ __('erp.banking.statement_balance') }}</dt><dd class="mt-0.5 font-medium"><x-ui.money :amount="$rec->statement_balance" /></dd></div>
                        <div><dt class="text-xs text-muted-foreground">{{ __('erp.banking.book_balance') }}</dt><dd class="mt-0.5 font-medium"><x-ui.money :amount="$rec->book_balance" /></dd></div>
                        <div><dt class="text-xs text-muted-foreground">{{ __('erp.reconciliation.difference') }}</dt><dd class="mt-0.5 font-medium"><x-ui.money :amount="$rec->difference" :muted="$matched" /></dd></div>
                        <div><dt class="text-xs text-muted-foreground">{{ __('erp.status') }}</dt><dd class="mt-0.5"><x-ui.status-badge :status="$rec->status?->value ?? $rec->status" /></dd></div>
                    </dl>
                </div>
            @endforeach
        </div>
        <x-ui.pagination :paginator="$reconciliations" />
    @endif
</div>
