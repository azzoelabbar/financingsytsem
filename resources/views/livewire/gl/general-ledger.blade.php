<div>
    <x-ui.page-header :breadcrumbs="[['label' => __('erp.nav.gl')], ['label' => __('erp.nav.gl_ledger')]]" :title="__('erp.nav.gl_ledger')" :description="__('erp.gl_ledger.hint')">
        <x-slot:actions>
            <x-ui.export-button />
        </x-slot:actions>
    </x-ui.page-header>

    {{-- Filters --}}
    <div class="mb-4 flex flex-wrap items-end gap-3 rounded-lg border border-border bg-card p-3">
        <div class="w-40">
            <label class="mb-1 block text-xs font-medium text-muted-foreground">{{ __('erp.gl_ledger.account_code') }}</label>
            <input type="text" wire:model.live.debounce.400ms="accountCode" placeholder="{{ __('erp.gl_ledger.account_placeholder') }}" class="h-9 w-full rounded-md border border-input bg-card px-3 text-sm focus-visible:outline-none focus-visible:border-ring focus-visible:ring-2 focus-visible:ring-ring/25" dir="ltr" />
        </div>
        <div class="w-40">
            <label class="mb-1 block text-xs font-medium text-muted-foreground">{{ __('erp.gl_ledger.from') }}</label>
            <input type="date" wire:model.live="from" class="h-9 w-full rounded-md border border-input bg-card px-3 text-sm focus-visible:outline-none focus-visible:border-ring focus-visible:ring-2 focus-visible:ring-ring/25" dir="ltr" />
        </div>
        <div class="w-40">
            <label class="mb-1 block text-xs font-medium text-muted-foreground">{{ __('erp.gl_ledger.to') }}</label>
            <input type="date" wire:model.live="to" class="h-9 w-full rounded-md border border-input bg-card px-3 text-sm focus-visible:outline-none focus-visible:border-ring focus-visible:ring-2 focus-visible:ring-ring/25" dir="ltr" />
        </div>
        @if ($accountCode || $from || $to)
            <button type="button" wire:click="$set('accountCode', ''); $set('from', ''); $set('to', '')" class="h-9 rounded-md px-3 text-sm text-muted-foreground hover:bg-muted hover:text-foreground">{{ __('erp.gl_ledger.clear') }}</button>
        @endif
    </div>

    @if ($lines === null || $lines->isEmpty())
        <x-ui.empty-state :title="__('erp.gl_ledger.empty_title')" :message="__('erp.gl_ledger.empty_hint')" />
    @else
        <x-ui.table>
            <thead><tr>
                <th>{{ __('erp.date') }}</th>
                <th>{{ __('erp.journal.title') }}</th>
                <th>{{ __('erp.journal.account') }}</th>
                <th>{{ __('erp.journal.description') }}</th>
                <th class="!text-end">{{ __('erp.debit') }}</th>
                <th class="!text-end">{{ __('erp.credit') }}</th>
            </tr></thead>
            <tbody>
                @foreach ($lines as $line)
                    <tr wire:key="gl-{{ $line->id }}">
                        <td class="tabular-nums text-muted-foreground" dir="ltr">{{ $line->journal?->journal_date?->format('Y-m-d') }}</td>
                        <td>
                            <a href="{{ route('gl.journals.show', $line->journal_id) }}" wire:navigate class="font-medium text-[var(--brand-600)] hover:underline">{{ $line->journal?->number }}</a>
                        </td>
                        <td><span class="font-mono text-xs text-muted-foreground" dir="ltr">{{ $line->account?->code }}</span> {{ $line->account?->name_ar }}</td>
                        <td class="text-muted-foreground">{{ $line->description ?? $line->memo ?? '-' }}</td>
                        <td class="text-end"><x-ui.money :amount="$line->debit" muted /></td>
                        <td class="text-end"><x-ui.money :amount="$line->credit" muted /></td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui.table>
        <x-ui.pagination :paginator="$lines" />
    @endif
</div>
