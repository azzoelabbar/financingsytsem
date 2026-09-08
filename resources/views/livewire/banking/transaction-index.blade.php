<div>
    <x-ui.page-header :breadcrumbs="[['label' => __('erp.nav.banking')], ['label' => __('erp.banking.transactions')]]" :title="__('erp.banking.transactions')" :description="__('erp.banking.transactions_hint')"><x-slot:actions><x-ui.export-button /><x-ui.button :href="route('banking.transactions.create')">{{ __('erp.banking.create_transaction') }}</x-ui.button></x-slot:actions></x-ui.page-header>

    <x-ui.toolbar :summary="$transactions ? trans_choice('erp.pagination.result_count', $transactions->total(), ['count' => number_format($transactions->total())]) : null" />

    @if ($transactions === null || $transactions->isEmpty())
        <x-ui.empty-state :title="__('erp.banking.tx_empty_title')" :message="__('erp.banking.tx_empty_hint')"><x-slot:actions><x-ui.button :href="route('banking.transactions.create')">{{ __('erp.banking.create_transaction') }}</x-ui.button></x-slot:actions></x-ui.empty-state>
    @else
        <x-ui.table>
            <thead><tr>
                <th>{{ __('erp.number') }}</th>
                <th>{{ __('erp.date') }}</th>
                <th>{{ __('erp.banking.account') }}</th>
                <th>{{ __('erp.banking.tx_type') }}</th>
                <th>{{ __('erp.document.reference') }}</th>
                <th class="!text-end">{{ __('erp.amount') }}</th>
                <th>{{ __('erp.status') }}</th>
            </tr></thead>
            <tbody>
                @foreach ($transactions as $tx)
                    <tr wire:key="tx-{{ $tx->id }}">
                        <td class="font-medium"><a href="{{ route('banking.transactions.show',$tx) }}" wire:navigate class="text-[var(--brand-600)]">{{ $tx->number ?? __('erp.sales_invoice.draft_number') }}</a></td>
                        <td class="tabular-nums text-muted-foreground" dir="ltr">{{ $tx->transaction_date?->format('Y-m-d') }}</td>
                        <td>{{ app()->getLocale() === 'ar' ? ($tx->treasuryAccount?->name_ar ?? $tx->treasuryAccount?->name_en) : ($tx->treasuryAccount?->name_en ?? $tx->treasuryAccount?->name_ar) }}</td>
                        <td class="text-muted-foreground">{{ __('erp.banking.tx_types.'.($tx->type?->value ?? $tx->type)) }}</td>
                        <td class="text-muted-foreground">{{ $tx->reference ?? '-' }}</td>
                        <td class="text-end"><x-ui.money :amount="$tx->amount" :currency="$tx->currency" :negative="$tx->direction === 'out'" /></td>
                        <td><x-ui.status-badge :status="$tx->status?->value ?? $tx->status" /></td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui.table>
        <x-ui.pagination :paginator="$transactions" />
    @endif
</div>
