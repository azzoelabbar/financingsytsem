<div>
    <x-ui.page-header :breadcrumbs="[['label' => __('erp.nav.ar')], ['label' => __('erp.nav.debit_notes')]]" :title="__('erp.debit_note.ar_title')" :description="__('erp.debit_note.ar_hint')">
        <x-slot:actions><x-ui.export-button /><x-ui.button :href="route('ar.debit-notes.create')">{{ __('erp.debit_note.create') }}</x-ui.button></x-slot:actions>
    </x-ui.page-header>

    <x-ui.toolbar :summary="$notes ? trans_choice('erp.pagination.result_count', $notes->total(), ['count' => number_format($notes->total())]) : null" />

    @if ($notes === null || $notes->isEmpty())
        <x-ui.empty-state :title="__('erp.debit_note.empty_title')" :message="__('erp.debit_note.empty_hint')">
            <x-slot:actions><x-ui.button :href="route('ar.debit-notes.create')">{{ __('erp.debit_note.create') }}</x-ui.button></x-slot:actions>
        </x-ui.empty-state>
    @else
        <x-ui.table>
            <thead><tr>
                <th>{{ __('erp.number') }}</th>
                <th>{{ __('erp.sales_invoice.customer') }}</th>
                <th>{{ __('erp.date') }}</th>
                <th class="!text-end">{{ __('erp.total') }}</th>
                <th>{{ __('erp.status') }}</th>
            </tr></thead>
            <tbody>
                @foreach ($notes as $note)
                    <tr wire:key="ardn-{{ $note->id }}">
                        <td><a href="{{ route('ar.debit-notes.show', $note->id) }}" wire:navigate class="font-medium text-[var(--brand-600)] hover:underline">{{ $note->number ?? __('erp.sales_invoice.draft_number') }}</a></td>
                        <td>{{ app()->getLocale() === 'ar' ? ($note->customer?->name_ar ?? $note->customer?->name_en) : ($note->customer?->name_en ?? $note->customer?->name_ar) }}</td>
                        <td class="tabular-nums text-muted-foreground" dir="ltr">{{ $note->debit_note_date?->format('Y-m-d') }}</td>
                        <td class="text-end"><x-ui.money :amount="$note->gross_total" :currency="$note->currency" /></td>
                        <td><x-ui.status-badge :status="$note->status?->value ?? $note->status" /></td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui.table>
        <x-ui.pagination :paginator="$notes" />
    @endif
</div>
