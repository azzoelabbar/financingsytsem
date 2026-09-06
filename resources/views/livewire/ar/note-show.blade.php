@php
    $noteDate = $note->{$dateField} ?? null;
    $docTitle = $kind === 'credit' ? __('erp.credit_note.doc_title') : __('erp.debit_note.doc_title');
    $custName = app()->getLocale() === 'ar' ? ($note->customer?->name_ar ?? $note->customer?->name_en) : ($note->customer?->name_en ?? $note->customer?->name_ar);
@endphp
<x-doc.workspace
    :title="$note->number ?? $docTitle"
    :eyebrow="$docTitle"
    :subtitle="$custName"
    :code="$kind === 'credit' ? 'CN' : 'DN'"
    :status="$note->status"
    :backRoute="$kind === 'credit' ? route('ar.credit-notes') : route('ar.debit-notes')"
    :journalId="$note->journal_id"
    :bookCode="$note->book?->code"
    :postingDate="$note->journal?->posting_date?->format('Y-m-d')"
    :timeline="$timeline"
    :posted="$note->status->isPosted()"
    :breadcrumbs="[
        ['label' => __('erp.nav.ar')],
        ['label' => $docTitle, 'href' => $kind === 'credit' ? route('ar.credit-notes') : route('ar.debit-notes')],
        ['label' => $note->number ?? $docTitle],
    ]"
>
    @if ($note->status->isMutable())
        <x-slot:actions>
            <x-ui.confirm-action
                action="post"
                :label="__('erp.action.post')"
                :title="__('erp.document.post_note_confirm_title')"
                :message="__('erp.document.post_note_confirm_body')"
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
        <x-ui.metric :label="__('erp.document.total')">
            <x-ui.money :amount="$note->gross_total" :currency="$note->currency" />
        </x-ui.metric>
        <x-ui.metric :label="__('erp.document.subtotal')">
            <x-ui.money :amount="$note->net_total" />
        </x-ui.metric>
        <x-ui.metric :label="__('erp.document.tax')">
            <x-ui.money :amount="$note->tax_total" :muted="(float) $note->tax_total == 0" />
        </x-ui.metric>
        <x-ui.metric :label="__('erp.date')">
            <span class="tabular-nums" dir="ltr">{{ $noteDate?->format('Y-m-d') }}</span>
        </x-ui.metric>
    </x-slot:metrics>

    <x-ui.card :title="__('erp.form.parties')">
        <dl class="grid grid-cols-2 gap-x-6 gap-y-4 sm:grid-cols-3">
            <div><dt class="text-xs text-muted-foreground">{{ __('erp.sales_invoice.customer') }}</dt><dd class="mt-0.5 text-sm font-medium"><a href="{{ route('ar.customers.show', $note->customer_id) }}" class="text-[var(--brand-600)] hover:underline">{{ $custName }}</a></dd></div>
            <div><dt class="text-xs text-muted-foreground">{{ __('erp.date') }}</dt><dd class="mt-0.5 text-sm font-medium tabular-nums" dir="ltr">{{ $noteDate?->format('Y-m-d') }}</dd></div>
            <div><dt class="text-xs text-muted-foreground">{{ __('erp.document.currency') }}</dt><dd class="mt-0.5 text-sm font-medium">{{ $note->currency }}</dd></div>
            @if ($note->reason)
                <div><dt class="text-xs text-muted-foreground">{{ __('erp.credit_note.reason') }}</dt><dd class="mt-0.5 text-sm font-medium">{{ $note->reason }}</dd></div>
            @endif
            @if ($note->originalInvoice)
                <div><dt class="text-xs text-muted-foreground">{{ __('erp.document.original_invoice') }}</dt><dd class="mt-0.5 text-sm font-medium"><a href="{{ route('ar.invoices.show', $note->sales_invoice_id) }}" wire:navigate class="text-[var(--brand-600)] hover:underline">{{ $note->originalInvoice->number }}</a></dd></div>
            @endif
        </dl>
    </x-ui.card>

    @include('livewire.partials.note-lines', ['note' => $note])

    @if ($kind === 'credit' && $note->status->isPosted())
        <x-ui.card :title="__('erp.receipt.allocations')" :description="__('erp.note.allocations_hint')" flush>
            @if ((float) $note->openBalance() > 0)
                <form wire:submit="allocate" class="grid gap-3 border-b border-border bg-muted/20 p-4 md:grid-cols-[minmax(0,1fr)_12rem_auto] md:items-end">
                    <x-ui.field :label="__('erp.receipt.invoice')" for="credit-allocation-invoice" required :error="$errors->first('invoice_id')">
                        <select id="credit-allocation-invoice" wire:model="invoice_id" class="erp-control {{ $errors->has('invoice_id') ? 'erp-control-invalid' : '' }}" required>
                            <option value="">{{ __('erp.receipt.select_open_invoice') }}</option>
                            @foreach ($openInvoices as $invoice)
                                <option value="{{ $invoice->id }}">{{ $invoice->number }} — {{ number_format((float) $invoice->openBalance(), 2) }} {{ $invoice->currency }}</option>
                            @endforeach
                        </select>
                    </x-ui.field>
                    <x-ui.field :label="__('erp.receipt.alloc_amount')" for="credit-allocation-amount" required :error="$errors->first('allocation_amount')">
                        <input id="credit-allocation-amount" type="number" min="0.000001" step="0.000001" max="{{ $note->openBalance() }}" wire:model="allocation_amount" class="erp-control text-end tabular-nums {{ $errors->has('allocation_amount') ? 'erp-control-invalid' : '' }}" dir="ltr" required />
                    </x-ui.field>
                    <x-ui.button type="submit" wire:loading.attr="disabled" :disabled="$openInvoices->isEmpty()">{{ __('erp.receipt.allocate') }}</x-ui.button>
                </form>
            @endif

            @if ($note->allocations->isNotEmpty())
                <x-ui.table flush>
                    <thead><tr><th>{{ __('erp.receipt.invoice') }}</th><th>{{ __('erp.date') }}</th><th class="!text-end">{{ __('erp.receipt.alloc_amount') }}</th></tr></thead>
                    <tbody>
                        @foreach ($note->allocations as $allocation)
                            <tr wire:key="credit-allocation-{{ $allocation->id }}">
                                <td><a href="{{ route('ar.invoices.show', $allocation->sales_invoice_id) }}" wire:navigate class="font-medium text-[var(--brand-600)] hover:underline" dir="ltr">{{ $allocation->invoice?->number }}</a></td>
                                <td class="tabular-nums text-muted-foreground" dir="ltr">{{ $allocation->allocation_date?->format('Y-m-d') }}</td>
                                <td class="text-end"><x-ui.money :amount="$allocation->amount" :currency="$note->currency" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </x-ui.table>
            @else
                <x-ui.empty-state variant="panel" :message="__('erp.note.no_allocations')" />
            @endif
        </x-ui.card>
    @endif
</x-doc.workspace>
