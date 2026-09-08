<div>
    <x-ui.page-header :breadcrumbs="[['label' => __('erp.nav.ap')], ['label' => __('erp.nav.supplier_payments')]]" :title="__('erp.nav.supplier_payments')" :description="__('erp.payment.hint')"><x-slot:actions><x-ui.import-button kind="payments" /><x-ui.export-button /><x-ui.button :href="route('ap.payments.create')">{{ __('erp.payment.create') }}</x-ui.button></x-slot:actions></x-ui.page-header>

    <x-ui.toolbar :summary="$payments ? trans_choice('erp.pagination.result_count', $payments->total(), ['count' => number_format($payments->total())]) : null" />

    @if ($payments === null || $payments->isEmpty())
        <x-ui.empty-state :title="__('erp.payment.empty_title')" :message="__('erp.payment.empty_hint')"><x-slot:actions><x-ui.button :href="route('ap.payments.create')">{{ __('erp.payment.create') }}</x-ui.button></x-slot:actions></x-ui.empty-state>
    @else
        <x-ui.table>
            <thead><tr>
                <th>{{ __('erp.number') }}</th>
                <th>{{ __('erp.supplier.title') }}</th>
                <th>{{ __('erp.date') }}</th>
                <th>{{ __('erp.currency') }}</th>
                <th class="!text-end">{{ __('erp.payment.amount') }}</th>
                <th class="!text-end">{{ __('erp.receipt.unallocated') }}</th>
                <th>{{ __('erp.status') }}</th>
            </tr></thead>
            <tbody>
                @foreach ($payments as $payment)
                    <tr wire:key="pay-{{ $payment->id }}">
                        <td><a href="{{ route('ap.payments.show', $payment->id) }}" wire:navigate class="font-medium text-[var(--brand-600)] hover:underline">{{ $payment->number ?? __('erp.sales_invoice.draft_number') }}</a></td>
                        <td>{{ app()->getLocale() === 'ar' ? ($payment->supplier?->name_ar ?? $payment->supplier?->legal_name) : ($payment->supplier?->trading_name ?? $payment->supplier?->legal_name) }}</td>
                        <td class="tabular-nums text-muted-foreground" dir="ltr">{{ $payment->payment_date?->format('Y-m-d') }}</td>
                        <td>{{ $payment->currency }}</td>
                        <td class="text-end"><x-ui.money :amount="$payment->amount" /></td>
                        <td class="text-end"><x-ui.money :amount="$payment->unallocated_amount" muted /></td>
                        <td><x-ui.status-badge :status="$payment->status?->value ?? $payment->status" /></td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui.table>
        <x-ui.pagination :paginator="$payments" />
    @endif
</div>
