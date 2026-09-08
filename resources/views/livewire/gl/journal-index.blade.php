<div>
    <x-ui.page-header
        :title="__('erp.journal.title')"
        :description="__('erp.list.journals_hint')"
        :breadcrumbs="[['label' => __('erp.nav.gl')], ['label' => __('erp.nav.journals')]]"
    >
        <x-slot:actions>
            <x-ui.export-button />
            <x-ui.button variant="secondary" :href="route('gl.trial-balance')">{{ __('erp.nav.trial_balance') }}</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.toolbar
        :placeholder="__('erp.journal.search_placeholder')"
        :summary="$journals ? trans_choice('erp.pagination.result_count', $journals->total(), ['count' => number_format($journals->total())]) : null"
    />

    @if ($journals === null || $journals->isEmpty())
        <x-ui.empty-state :title="__('erp.journal.empty_title')" :message="__('erp.journal.empty_hint')" />
    @else
        <x-ui.table>
            <thead>
                <tr>
                    <th>{{ __('erp.number') }}</th>
                    <th>{{ __('erp.journal.journal_date') }}</th>
                    <th class="!text-end">{{ __('erp.debit') }}</th>
                    <th class="!text-end">{{ __('erp.credit') }}</th>
                    <th>{{ __('erp.status') }}</th>
                    <th class="w-px"><span class="sr-only">{{ __('erp.actions') }}</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($journals as $journal)
                    <tr wire:key="journal-{{ $journal->id }}">
                        <td>
                            <a href="{{ route('gl.journals.show', $journal) }}" wire:navigate class="font-medium text-[var(--brand-600)] hover:underline" dir="ltr">{{ $journal->number }}</a>
                        </td>
                        <td class="tabular-nums text-muted-foreground" dir="ltr">{{ $journal->journal_date?->format('Y-m-d') }}</td>
                        <td class="text-end"><x-ui.money :amount="$journal->total_debit ?? '0'" /></td>
                        <td class="text-end"><x-ui.money :amount="$journal->total_credit ?? '0'" /></td>
                        <td><x-ui.status-badge :status="$journal->status" /></td>
                        <td class="text-end">
                            <x-ui.button variant="secondary" size="sm" :href="route('gl.journals.show', $journal)">{{ __('erp.overview') }}</x-ui.button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui.table>

        <x-ui.pagination :paginator="$journals" />
    @endif
</div>
