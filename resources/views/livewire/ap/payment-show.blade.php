@php
    use App\Services\Accounting\Support\Decimal;
    $allocated = Decimal::sub((string) $payment->amount, (string) $payment->unallocated_amount);
    $supName = app()->getLocale() === 'ar' ? ($payment->supplier?->name_ar ?? $payment->supplier?->legal_name) : ($payment->supplier?->trading_name ?? $payment->supplier?->legal_name ?? $payment->supplier?->name_ar);
@endphp
<x-doc.workspace
    :title="$payment->number ?? __('erp.payment.title')"
    :eyebrow="__('erp.payment.title')"
    :subtitle="$supName"
    code="PAY"
    :status="$payment->status"
    :backRoute="route('ap.payments')"
    :journalId="$payment->journal_id"
    :bookCode="$payment->book?->code"
    :postingDate="$payment->journal?->posting_date?->format('Y-m-d')"
    :timeline="$timeline"
    :posted="$payment->status->isPosted()"
    :breadcrumbs="[
        ['label' => __('erp.nav.ap')],
        ['label' => __('erp.nav.supplier_payments'), 'href' => route('ap.payments')],
        ['label' => $payment->number ?? __('erp.payment.title')],
    ]"
>
    @if ($payment->status->isMutable())
        <x-slot:actions>
            <x-ui.confirm-action
                action="post"
                :label="__('erp.action.post')"
                :title="__('erp.payment.post_confirm_title')"
                :message="__('erp.payment.post_confirm_body')"
                :confirm-label="__('erp.action.confirm_post')"
            />
        </x-slot:actions>
    @endif

    @error('posting')<x-ui.alert variant="danger" :title="__('erp.action.post')">{{ $message }}</x-ui.alert>@enderror
    @error('allocation')<x-ui.alert variant="danger" :title="__('erp.receipt.allocate')">{{ $message }}</x-ui.alert>@enderror

    <x-slot:metrics>
        <x-ui.metric :label="__('erp.payment.amount')">
            <x-ui.money :amount="$payment->amount" :currency="$payment->currency" />
        </x-ui.metric>
        <x-ui.metric :label="__('erp.receipt.allocated')">
            <x-ui.money :amount="$allocated" />
        </x-ui.metric>
        <x-ui.metric :label="__('erp.receipt.unallocated')">
            <x-ui.money :amount="$payment->unallocated_amount" :muted="(float) $payment->unallocated_amount == 0" />
        </x-ui.metric>
        <x-ui.metric :label="__('erp.date')">
            <span class="tabular-nums" dir="ltr">{{ $payment->payment_date?->format('Y-m-d') }}</span>
        </x-ui.metric>
    </x-slot:metrics>

    <x-ui.card :title="__('erp.form.parties')">
        <dl class="grid grid-cols-2 gap-x-6 gap-y-4 sm:grid-cols-3">
            <div>
                <dt class="erp-kpi-label">{{ __('erp.purchase_invoice.supplier') }}</dt>
                <dd class="mt-1 text-[0.8125rem] font-medium">{{ $supName }}</dd>
            </div>
            <div>
                <dt class="erp-kpi-label">{{ __('erp.date') }}</dt>
                <dd class="mt-1 text-[0.8125rem] font-medium tabular-nums" dir="ltr">{{ $payment->payment_date?->format('Y-m-d') }}</dd>
            </div>
            <div>
                <dt class="erp-kpi-label">{{ __('erp.document.currency') }}</dt>
                <dd class="mt-1 text-[0.8125rem] font-medium" dir="ltr">{{ $payment->currency }}</dd>
            </div>
            <div>
                <dt class="erp-kpi-label">{{ __('erp.document.exchange_rate') }}</dt>
                <dd class="mt-1 text-[0.8125rem] font-medium tabular-nums" dir="ltr">{{ rtrim(rtrim((string) $payment->exchange_rate, '0'), '.') }}</dd>
            </div>
        </dl>

        <div class="mt-5 flex justify-end border-t border-border pt-4">
            <dl class="w-full max-w-xs space-y-2 text-[0.8125rem]">
                <div class="flex justify-between">
                    <dt class="text-muted-foreground">{{ __('erp.payment.amount') }}</dt>
                    <dd><x-ui.money :amount="$payment->amount" :currency="$payment->currency" /></dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-muted-foreground">{{ __('erp.receipt.allocated') }}</dt>
                    <dd><x-ui.money :amount="$allocated" /></dd>
                </div>
                <div class="flex items-baseline justify-between border-t border-border-strong pt-2">
                    <dt class="font-semibold">{{ __('erp.receipt.unallocated') }}</dt>
                    <dd><x-ui.money :amount="$payment->unallocated_amount" :currency="$payment->currency" size="lg" /></dd>
                </div>
            </dl>
        </div>
    </x-ui.card>

    <x-ui.card :title="__('erp.receipt.allocations')" :description="__('erp.payment.allocations_hint')" flush>
        @if($payment->status->isPosted() && (float)$payment->unallocated_amount > 0)
            <form wire:submit="allocate" class="grid gap-3 border-b border-border bg-muted/20 p-4 md:grid-cols-[minmax(0,1fr)_12rem_auto] md:items-end">
                <x-ui.field :label="__('erp.payment.invoice')" for="payment-allocation-invoice" required :error="$errors->first('invoice_id')"><select id="payment-allocation-invoice" wire:model="invoice_id" class="erp-control" required><option value="">{{ __('erp.payment.select_open_invoice') }}</option>@foreach($openInvoices as $invoice)<option value="{{ $invoice->id }}">{{ $invoice->number }} — {{ number_format((float)$invoice->openBalance(),2) }} {{ $invoice->currency }}</option>@endforeach</select></x-ui.field>
                <x-ui.field :label="__('erp.receipt.alloc_amount')" for="payment-allocation-amount" required :error="$errors->first('allocation_amount')"><input id="payment-allocation-amount" type="number" min="0.000001" step="0.000001" max="{{ $payment->unallocated_amount }}" wire:model="allocation_amount" class="erp-control text-end tabular-nums" dir="ltr" required /></x-ui.field>
                <x-ui.button type="submit" :disabled="$openInvoices->isEmpty()">{{ __('erp.receipt.allocate') }}</x-ui.button>
            </form>
        @endif
        @if ($payment->allocations->isNotEmpty())
            <x-ui.table flush>
                <thead>
                    <tr>
                        <th>{{ __('erp.payment.invoice') }}</th>
                        <th>{{ __('erp.date') }}</th>
                        <th class="!text-end">{{ __('erp.receipt.alloc_amount') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($payment->allocations as $alloc)
                        <tr wire:key="pay-alloc-{{ $alloc->id }}">
                            <td class="font-medium" dir="ltr">@if($alloc->invoice)<a href="{{ route('ap.invoices.show',$alloc->purchase_invoice_id) }}" wire:navigate class="text-[var(--brand-600)] hover:underline">{{ $alloc->invoice->number }}</a>@else - @endif</td>
                            <td class="tabular-nums text-muted-foreground" dir="ltr">{{ $alloc->allocation_date?->format('Y-m-d') }}</td>
                            <td class="text-end"><x-ui.money :amount="$alloc->amount" /></td>
                        </tr>
                    @endforeach
                </tbody>
            </x-ui.table>
        @else
            <x-ui.empty-state variant="panel" :message="__('erp.receipt.no_allocations')" />
        @endif
    </x-ui.card>
</x-doc.workspace>
