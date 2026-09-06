@php $isCredit=$kind==='credit'; $title=$isCredit?__('erp.credit_note.ap_create'):__('erp.debit_note.ap_create'); $listRoute=$isCredit?route('ap.credit-notes'):route('ap.debit-notes'); @endphp
<div>
    <x-ui.page-header :title="$title" :description="$isCredit?__('erp.credit_note.ap_create_hint'):__('erp.debit_note.ap_create_hint')" :breadcrumbs="[['label'=>__('erp.nav.ap')],['label'=>$isCredit?__('erp.nav.credit_notes'):__('erp.nav.debit_notes'),'href'=>$listRoute],['label'=>$title]]" />
    <form wire:submit="save" class="mx-auto max-w-5xl overflow-hidden rounded-lg border border-border bg-card">
        <x-ui.form-section :title="__('erp.form.parties')" :description="__('erp.note.ap_parties_hint')">
            <x-ui.searchable-select :label="__('erp.purchase_invoice.supplier')" required :error="$errors->first('supplier_id')" id="ap-note-supplier" wire:model.live="supplier_id" required><option value="">{{ __('erp.purchase_invoice.select_supplier') }}</option>@foreach($suppliers as $supplier)<option value="{{ $supplier->id }}">{{ $supplier->code }} — {{ $supplier->legal_name }}</option>@endforeach</x-ui.searchable-select>
            <x-ui.field :label="__('erp.note.date')" for="ap-note-date" required><input id="ap-note-date" type="date" wire:model="document_date" class="erp-control" dir="ltr" /></x-ui.field>
            <x-ui.searchable-select :label="__('erp.document.original_invoice')" :error="$errors->first('purchase_invoice_id')" id="ap-note-invoice" wire:model="purchase_invoice_id"><option value="">{{ __('erp.note.no_related_invoice') }}</option>@foreach($invoices as $invoice)<option value="{{ $invoice->id }}">{{ $invoice->number }} — {{ number_format((float)$invoice->openBalance(),2) }} {{ $invoice->currency }}</option>@endforeach</x-ui.searchable-select>
            <x-ui.field :label="__('erp.credit_note.reason')" for="ap-note-reason"><input id="ap-note-reason" wire:model="reason" class="erp-control" /></x-ui.field>
            <x-ui.field :label="__('erp.document.reference')" for="ap-note-reference"><input id="ap-note-reference" wire:model="reference" class="erp-control" /></x-ui.field>
        </x-ui.form-section>
        <x-ui.form-section :title="__('erp.form.lines')" :description="__('erp.form.lines_hint')" :columns="1">@include('livewire.partials.document-line-editor',['lines'=>$lines,'accountField'=>'expense_account','accountLabel'=>__('erp.purchase_invoice.expense_account'),'accountHint'=>__('erp.purchase_invoice.expense_account_hint')])</x-ui.form-section>
        <x-ui.form-actions :note="__('erp.form.draft_note')"><x-ui.button variant="secondary" :href="$listRoute">{{ __('erp.cancel') }}</x-ui.button><x-ui.button type="submit">{{ __('erp.note.save_draft') }}</x-ui.button></x-ui.form-actions>
    </form>
</div>
