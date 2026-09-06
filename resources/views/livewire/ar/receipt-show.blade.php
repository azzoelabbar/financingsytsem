@php
    use App\Services\Accounting\Support\Decimal;
    $allocated = Decimal::sub((string) $receipt->amount, (string) $receipt->unallocated_amount);
    $custName = app()->getLocale() === 'ar' ? ($receipt->customer?->name_ar ?? $receipt->customer?->name_en) : ($receipt->customer?->name_en ?? $receipt->customer?->name_ar);
@endphp
<x-doc.workspace
    :title="$receipt->number ?? __('erp.receipt.title')"
    :eyebrow="__('erp.receipt.title')"
    :subtitle="$custName"
    code="RCP"
    :status="$receipt->status"
    :backRoute="route('ar.receipts')"
    :journalId="$receipt->journal_id"
    :bookCode="$receipt->book?->code"
    :postingDate="$receipt->journal?->posting_date?->format('Y-m-d')"
    :timeline="$timeline"
    :posted="$receipt->status->isPosted()"
    :breadcrumbs="[
        ['label' => __('erp.nav.ar')],
        ['label' => __('erp.nav.receipts'), 'href' => route('ar.receipts')],
        ['label' => $receipt->number ?? __('erp.receipt.title')],
    ]"
>
    @if ($receipt->status->isMutable())
        <x-slot:actions>
            <x-ui.confirm-action
                action="post"
                :label="__('erp.action.post')"
                :title="__('erp.receipt.post_confirm_title')"
                :message="__('erp.receipt.post_confirm_body')"
                :confirm-label="__('erp.action.confirm_post')"
            />
        </x-slot:actions>
    @endif

    @error('posting')
        <x-ui.alert variant="danger" :title="__('erp.action.post')">{{ $message }}</x-ui.alert>
    @enderror
    @error('allocation')
        <x-ui.alert variant="danger" :title="__('erp.receipt.allocate')">{{ $message }}</x-ui.alert>
    @enderror

    <x-slot:metrics>
        <x-ui.metric :label="__('erp.receipt.amount')">
            <x-ui.money :amount="$receipt->amount" :currency="$receipt->currency" positive />
        </x-ui.metric>
        <x-ui.metric :label="__('erp.receipt.allocated')">
            <x-ui.money :amount="$allocated" />
        </x-ui.metric>
        <x-ui.metric :label="__('erp.receipt.unallocated')" :hint="(float) $receipt->unallocated_amount > 0 ? __('erp.receipt.unallocated_hint') : null">
            <x-ui.money :amount="$receipt->unallocated_amount" :muted="(float) $receipt->unallocated_amount == 0" />
        </x-ui.metric>
        <x-ui.metric :label="__('erp.date')">
            <span class="tabular-nums" dir="ltr">{{ $receipt->receipt_date?->format('Y-m-d') }}</span>
        </x-ui.metric>
    </x-slot:metrics>

    <x-ui.card :title="__('erp.form.parties')">
        <dl class="grid grid-cols-2 gap-x-6 gap-y-4 sm:grid-cols-3">
            <div>
                <dt class="erp-kpi-label">{{ __('erp.sales_invoice.customer') }}</dt>
                <dd class="mt-1 text-[0.8125rem] font-medium"><a href="{{ route('ar.customers.show', $receipt->customer_id) }}" wire:navigate class="text-[var(--brand-600)] hover:underline">{{ $custName }}</a></dd>
            </div>
            <div>
                <dt class="erp-kpi-label">{{ __('erp.date') }}</dt>
                <dd class="mt-1 text-[0.8125rem] font-medium tabular-nums" dir="ltr">{{ $receipt->receipt_date?->format('Y-m-d') }}</dd>
            </div>
            <div>
                <dt class="erp-kpi-label">{{ __('erp.document.currency') }}</dt>
                <dd class="mt-1 text-[0.8125rem] font-medium" dir="ltr">{{ $receipt->currency }}</dd>
            </div>
            <div>
                <dt class="erp-kpi-label">{{ __('erp.document.exchange_rate') }}</dt>
                <dd class="mt-1 text-[0.8125rem] font-medium tabular-nums" dir="ltr">{{ rtrim(rtrim((string) $receipt->exchange_rate, '0'), '.') }}</dd>
            </div>
        </dl>

        <div class="mt-5 flex justify-end border-t border-border pt-4">
            <dl class="w-full max-w-xs space-y-2 text-[0.8125rem]">
                <div class="flex justify-between">
                    <dt class="text-muted-foreground">{{ __('erp.receipt.amount') }}</dt>
                    <dd><x-ui.money :amount="$receipt->amount" :currency="$receipt->currency" /></dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-muted-foreground">{{ __('erp.receipt.allocated') }}</dt>
                    <dd><x-ui.money :amount="$allocated" /></dd>
                </div>
                <div class="flex items-baseline justify-between border-t border-border-strong pt-2">
                    <dt class="font-semibold">{{ __('erp.receipt.unallocated') }}</dt>
                    <dd><x-ui.money :amount="$receipt->unallocated_amount" :currency="$receipt->currency" size="lg" /></dd>
                </div>
            </dl>
        </div>
    </x-ui.card>

    <x-ui.card :title="__('erp.receipt.allocations')" :description="__('erp.receipt.allocations_hint')" flush>
        @if ($receipt->status->isPosted() && (float) $receipt->unallocated_amount > 0)
            <form wire:submit="allocate" class="grid gap-3 border-b border-border bg-muted/20 p-4 md:grid-cols-[minmax(0,1fr)_12rem_auto] md:items-end">
                <x-ui.searchable-select :label="__('erp.receipt.invoice')" required :error="$errors->first('invoice_id')" id="receipt-allocation-invoice" wire:model="invoice_id" required>
                        <option value="">{{ __('erp.receipt.select_open_invoice') }}</option>
                        @foreach ($openInvoices as $invoice)
                            <option value="{{ $invoice->id }}">{{ $invoice->number }} — {{ number_format((float) $invoice->openBalance(), 2) }} {{ $invoice->currency }}</option>
                        @endforeach
                    </x-ui.searchable-select>
                <x-ui.field :label="__('erp.receipt.alloc_amount')" for="receipt-allocation-amount" required :error="$errors->first('allocation_amount')">
                    <input id="receipt-allocation-amount" type="number" min="0.000001" step="0.000001" max="{{ $receipt->unallocated_amount }}" wire:model="allocation_amount" class="erp-control text-end tabular-nums {{ $errors->has('allocation_amount') ? 'erp-control-invalid' : '' }}" dir="ltr" required />
                </x-ui.field>
                <x-ui.button type="submit" wire:loading.attr="disabled" :disabled="$openInvoices->isEmpty()">
                    <span wire:loading.remove wire:target="allocate">{{ __('erp.receipt.allocate') }}</span>
                    <span wire:loading wire:target="allocate">{{ __('erp.saving') }}</span>
                </x-ui.button>
            </form>
        @endif

        @if ($receipt->allocations->isNotEmpty())
            <x-ui.table flush>
                <thead>
                    <tr>
                        <th>{{ __('erp.receipt.invoice') }}</th>
                        <th>{{ __('erp.date') }}</th>
                        <th class="!text-end">{{ __('erp.receipt.alloc_amount') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($receipt->allocations as $alloc)
                        <tr wire:key="alloc-{{ $alloc->id }}">
                            <td>
                                @if ($alloc->invoice)
                                    <a href="{{ route('ar.invoices.show', $alloc->sales_invoice_id) }}" wire:navigate class="font-medium text-[var(--brand-600)] hover:underline" dir="ltr">{{ $alloc->invoice->number }}</a>
                                @else
                                    <span class="text-muted-foreground">-</span>
                                @endif
                            </td>
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
