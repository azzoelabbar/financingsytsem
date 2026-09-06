@php
    $isCredit = $kind === 'credit';
    $title = $isCredit ? __('erp.credit_note.create') : __('erp.debit_note.create');
    $listRoute = $isCredit ? route('ar.credit-notes') : route('ar.debit-notes');
@endphp
<div>
    <x-ui.page-header
        :title="$title"
        :description="$isCredit ? __('erp.credit_note.create_hint') : __('erp.debit_note.create_hint')"
        :breadcrumbs="[
            ['label' => __('erp.nav.ar')],
            ['label' => $isCredit ? __('erp.nav.credit_notes') : __('erp.nav.debit_notes'), 'href' => $listRoute],
            ['label' => $title],
        ]"
    />

    <form wire:submit="save" class="mx-auto max-w-5xl overflow-hidden rounded-lg border border-border bg-card">
        <x-ui.form-section :title="__('erp.form.parties')" :description="__('erp.note.parties_hint')">
            <x-ui.field :label="__('erp.sales_invoice.customer')" for="note-customer" required :error="$errors->first('customer_id')">
                <select id="note-customer" wire:model.live="customer_id" class="erp-control {{ $errors->has('customer_id') ? 'erp-control-invalid' : '' }}" required>
                    <option value="">{{ __('erp.sales_invoice.select_customer') }}</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->code }} — {{ app()->getLocale() === 'ar' ? ($customer->name_ar ?? $customer->name_en) : ($customer->name_en ?? $customer->name_ar) }}</option>
                    @endforeach
                </select>
            </x-ui.field>

            <x-ui.field :label="__('erp.note.date')" for="note-date" required :error="$errors->first('document_date')">
                <input id="note-date" type="date" wire:model="document_date" class="erp-control {{ $errors->has('document_date') ? 'erp-control-invalid' : '' }}" dir="ltr" required />
            </x-ui.field>

            <x-ui.field :label="__('erp.document.original_invoice')" for="note-invoice" :error="$errors->first('sales_invoice_id')" :hint="__('erp.note.invoice_hint')">
                <select id="note-invoice" wire:model="sales_invoice_id" class="erp-control {{ $errors->has('sales_invoice_id') ? 'erp-control-invalid' : '' }}">
                    <option value="">{{ __('erp.note.no_related_invoice') }}</option>
                    @foreach ($invoices as $invoice)
                        <option value="{{ $invoice->id }}">{{ $invoice->number }} — {{ number_format((float) $invoice->openBalance(), 2) }} {{ $invoice->currency }}</option>
                    @endforeach
                </select>
            </x-ui.field>

            <x-ui.field :label="__('erp.credit_note.reason')" for="note-reason" :error="$errors->first('reason')">
                <input id="note-reason" wire:model="reason" class="erp-control {{ $errors->has('reason') ? 'erp-control-invalid' : '' }}" />
            </x-ui.field>

            <x-ui.field :label="__('erp.document.reference')" for="note-reference" :error="$errors->first('reference')">
                <input id="note-reference" wire:model="reference" class="erp-control {{ $errors->has('reference') ? 'erp-control-invalid' : '' }}" />
            </x-ui.field>
        </x-ui.form-section>

        <x-ui.form-section :title="__('erp.form.lines')" :description="__('erp.form.lines_hint')" :columns="1">
            @include('livewire.partials.document-line-editor', [
                'lines' => $lines,
                'accountField' => 'revenue_account',
                'accountLabel' => __('erp.sales_invoice.revenue_account'),
                'accountHint' => __('erp.sales_invoice.revenue_account_hint'),
            ])
        </x-ui.form-section>

        <x-ui.form-actions :note="__('erp.form.draft_note')">
            <x-ui.button variant="secondary" :href="$listRoute">{{ __('erp.cancel') }}</x-ui.button>
            <x-ui.button type="submit" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="save">{{ __('erp.note.save_draft') }}</span>
                <span wire:loading wire:target="save">{{ __('erp.saving') }}</span>
            </x-ui.button>
        </x-ui.form-actions>
    </form>
</div>
