<div>
    <x-ui.page-header :breadcrumbs="[['label' => __('erp.nav.ap')], ['label' => __('erp.nav.credit_notes')]]" :title="__('erp.credit_note.ap_title')" :description="__('erp.credit_note.ap_hint')"><x-slot:actions><x-ui.button :href="route('ap.credit-notes.create')">{{ __('erp.credit_note.ap_create') }}</x-ui.button></x-slot:actions></x-ui.page-header>

    <x-ui.toolbar :summary="$notes ? trans_choice('erp.pagination.result_count', $notes->total(), ['count' => number_format($notes->total())]) : null" />

    @if ($notes === null || $notes->isEmpty())
        <x-ui.empty-state :title="__('erp.credit_note.empty_title')" :message="__('erp.credit_note.empty_hint')"><x-slot:actions><x-ui.button :href="route('ap.credit-notes.create')">{{ __('erp.credit_note.ap_create') }}</x-ui.button></x-slot:actions></x-ui.empty-state>
    @else
        <x-ui.table>
            <thead><tr>
                <th>{{ __('erp.number') }}</th>
                <th>{{ __('erp.purchase_invoice.supplier') }}</th>
                <th>{{ __('erp.date') }}</th>
                <th class="!text-end">{{ __('erp.total') }}</th>
                <th>{{ __('erp.status') }}</th>
            </tr></thead>
            <tbody>
                @foreach ($notes as $note)
                    <tr wire:key="apcn-{{ $note->id }}">
                        <td><a href="{{ route('ap.credit-notes.show', $note->id) }}" wire:navigate class="font-medium text-[var(--brand-600)] hover:underline">{{ $note->number ?? __('erp.sales_invoice.draft_number') }}</a></td>
                        <td>{{ app()->getLocale() === 'ar' ? ($note->supplier?->name_ar ?? $note->supplier?->name_en ?? $note->supplier?->legal_name) : ($note->supplier?->name_en ?? $note->supplier?->legal_name ?? $note->supplier?->name_ar) }}</td>
                        <td class="tabular-nums text-muted-foreground" dir="ltr">{{ $note->credit_note_date?->format('Y-m-d') }}</td>
                        <td class="text-end"><x-ui.money :amount="$note->gross_total" :currency="$note->currency" /></td>
                        <td><x-ui.status-badge :status="$note->status?->value ?? $note->status" /></td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui.table>
        <x-ui.pagination :paginator="$notes" />
    @endif
</div>
