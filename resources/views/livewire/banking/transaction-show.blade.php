<x-doc.workspace
    :title="$tx->number ?? __('erp.sales_invoice.draft_number')"
    :eyebrow="__('erp.banking.transaction')"
    :subtitle="__('erp.banking.tx_types.'.$tx->type->value)"
    code="TX"
    :status="$tx->status"
    :backRoute="route('banking.transactions')"
    :journalId="$tx->journal_id"
    :bookCode="$tx->book?->code"
    :postingDate="$tx->journal?->posting_date?->format('Y-m-d')"
    :timeline="$timeline"
    :posted="$tx->status->isPosted()"
    :breadcrumbs="[['label'=>__('erp.nav.banking')],['label'=>__('erp.banking.transactions'),'href'=>route('banking.transactions')],['label'=>$tx->number ?? __('erp.sales_invoice.draft_number')]]"
>
    <x-slot:actions>
        @if ($tx->status->isMutable())
            <x-ui.confirm-action action="post" :label="__('erp.action.post')" :title="__('erp.banking.tx_post_title')" :message="__('erp.banking.tx_post_body')" :confirm-label="__('erp.action.confirm_post')" />
        @endif
    </x-slot:actions>
    <x-slot:metrics>
        <x-ui.metric :label="__('erp.amount')"><x-ui.money :amount="$tx->amount" :currency="$tx->currency" /></x-ui.metric>
        <x-ui.metric :label="__('erp.banking.account')">{{ $tx->treasuryAccount?->code }}</x-ui.metric>
        <x-ui.metric :label="__('erp.date')"><span dir="ltr">{{ $tx->transaction_date?->format('Y-m-d') }}</span></x-ui.metric>
        <x-ui.metric :label="__('erp.status')"><x-ui.status-badge :status="$tx->is_cleared ? 'reconciled' : 'unreconciled'" /></x-ui.metric>
    </x-slot:metrics>
    @error('posting')<x-ui.alert variant="danger">{{ $message }}</x-ui.alert>@enderror
    <x-ui.card :title="__('erp.banking.tx_details')">
        <x-ui.detail-list :rows="[['label'=>__('erp.banking.tx_type'),'value'=>__('erp.banking.tx_types.'.$tx->type->value)],['label'=>__('erp.banking.counter_account'),'value'=>$tx->counterTreasuryAccount?->code ?? $tx->counter_account_code ?? '-','dir'=>'ltr'],['label'=>__('erp.document.reference'),'value'=>$tx->reference ?? '-'],['label'=>__('erp.description'),'value'=>$tx->description ?? '-']]" />
    </x-ui.card>
</x-doc.workspace>
