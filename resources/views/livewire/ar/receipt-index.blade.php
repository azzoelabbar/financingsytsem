<div>
    <x-ui.page-header :breadcrumbs="[['label' => __('erp.nav.ar')], ['label' => __('erp.nav.receipts')]]" :title="__('erp.nav.receipts')" :description="__('erp.receipt.hint')">
        <x-slot:actions><x-ui.import-button kind="receipts" /><x-ui.export-button /><x-ui.button :href="route('ar.receipts.create')">{{ __('erp.receipt.create') }}</x-ui.button></x-slot:actions>
    </x-ui.page-header>

    <x-ui.toolbar :summary="$receipts ? trans_choice('erp.pagination.result_count', $receipts->total(), ['count' => number_format($receipts->total())]) : null" />

    @if ($receipts === null || $receipts->isEmpty())
        <x-ui.empty-state :title="__('erp.receipt.empty_title')" :message="__('erp.receipt.empty_hint')">
            <x-slot:actions><x-ui.button :href="route('ar.receipts.create')">{{ __('erp.receipt.create') }}</x-ui.button></x-slot:actions>
        </x-ui.empty-state>
    @else
        <x-ui.table>
            <thead><tr>
                <th>{{ __('erp.number') }}</th>
                <th>{{ __('erp.customer.title') }}</th>
                <th>{{ __('erp.date') }}</th>
                <th>{{ __('erp.currency') }}</th>
                <th class="!text-end">{{ __('erp.receipt.amount') }}</th>
                <th class="!text-end">{{ __('erp.receipt.unallocated') }}</th>
                <th>{{ __('erp.status') }}</th>
            </tr></thead>
            <tbody>
                @foreach ($receipts as $receipt)
                    <tr wire:key="rcp-{{ $receipt->id }}">
                        <td><a href="{{ route('ar.receipts.show', $receipt->id) }}" wire:navigate class="font-medium text-[var(--brand-600)] hover:underline">{{ $receipt->number ?? __('erp.sales_invoice.draft_number') }}</a></td>
                        <td>{{ app()->getLocale() === 'ar' ? ($receipt->customer?->name_ar ?? $receipt->customer?->name_en) : ($receipt->customer?->name_en ?? $receipt->customer?->name_ar) }}</td>
                        <td class="tabular-nums text-muted-foreground" dir="ltr">{{ $receipt->receipt_date?->format('Y-m-d') }}</td>
                        <td>{{ $receipt->currency }}</td>
                        <td class="text-end"><x-ui.money :amount="$receipt->amount" /></td>
                        <td class="text-end"><x-ui.money :amount="$receipt->unallocated_amount" muted /></td>
                        <td><x-ui.status-badge :status="$receipt->status?->value ?? $receipt->status" /></td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui.table>
        <x-ui.pagination :paginator="$receipts" />
    @endif
</div>
