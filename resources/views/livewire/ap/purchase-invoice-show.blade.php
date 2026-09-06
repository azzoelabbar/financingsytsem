<div>
    @php
        $posted = $invoice->status->isPosted(); $open = (float) $invoice->openBalance(); $due = $invoice->due_date;
        $isOverdue = $posted && $open > 0 && $due !== null && $due->isPast();
        $supplierName = app()->getLocale() === 'ar' ? ($invoice->supplier?->name_ar ?? $invoice->supplier?->legal_name) : ($invoice->supplier?->trading_name ?? $invoice->supplier?->legal_name);
    @endphp
    <x-ui.entity-header :title="$invoice->number ?? __('erp.sales_invoice.draft_number')" :eyebrow="__('erp.purchase_invoice.entity')" :subtitle="$supplierName" code="PINV" :status="$invoice->status" :tone="$posted ? 'brand' : 'neutral'"
        :breadcrumbs="[['label'=>__('erp.nav.ap')],['label'=>__('erp.nav.purchase_invoices'),'href'=>route('ap.invoices')],['label'=>$invoice->number ?? __('erp.sales_invoice.draft_number')]]">
        <x-slot:badges>
            @if ($isOverdue)<x-ui.badge variant="danger" size="md">{{ __('erp.aging.overdue') }}</x-ui.badge>
            @elseif ($posted && $open == 0.0)<x-ui.badge variant="success" size="md">{{ __('erp.doc_status.paid') }}</x-ui.badge>@endif
        </x-slot:badges>
        <x-slot:actions>
            <x-ui.button variant="ghost" :href="route('ap.invoices')">{{ __('erp.action.back') }}</x-ui.button>
            @if ($invoice->journal)<x-ui.button variant="secondary" :href="route('gl.journals.show',$invoice->journal_id)">{{ __('erp.document.view_journal') }}</x-ui.button>@endif
            @if ($invoice->status->isMutable())<x-ui.confirm-action action="post" :label="__('erp.action.post')" :title="__('erp.purchase_invoice.post_confirm_title')" :message="__('erp.purchase_invoice.post_confirm_body')" :confirm-label="__('erp.action.confirm_post')" />@endif
        </x-slot:actions>
        <x-slot:metrics>
            <x-ui.metric :label="__('erp.document.total')"><x-ui.money :amount="$invoice->gross_total" :currency="$invoice->currency" /></x-ui.metric>
            <x-ui.metric :label="__('erp.document.open_balance')"><x-ui.money :amount="$invoice->openBalance()" :muted="$open == 0.0" /></x-ui.metric>
            <x-ui.metric :label="__('erp.document.due_date')"><span class="tabular-nums" dir="ltr">{{ $due?->format('Y-m-d') ?? '-' }}</span></x-ui.metric>
            <x-ui.metric :label="__('erp.document.currency')"><span dir="ltr">{{ $invoice->currency }}</span></x-ui.metric>
        </x-slot:metrics>
    </x-ui.entity-header>
    @error('posting')<x-ui.alert variant="danger" class="mb-5" :title="__('erp.action.post')">{{ $message }}</x-ui.alert>@enderror
    @if ($posted)<x-ui.posted-notice />@endif
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
        <div class="space-y-5 lg:col-span-2">
            <x-ui.card :title="__('erp.form.parties')"><dl class="grid grid-cols-2 gap-x-6 gap-y-4 sm:grid-cols-3">
                <div><dt class="erp-kpi-label">{{ __('erp.purchase_invoice.supplier') }}</dt><dd class="mt-1 text-[0.8125rem] font-medium">{{ $supplierName }}</dd></div>
                <div><dt class="erp-kpi-label">{{ __('erp.date') }}</dt><dd class="mt-1 text-[0.8125rem] font-medium tabular-nums" dir="ltr">{{ $invoice->invoice_date?->format('Y-m-d') }}</dd></div>
                <div><dt class="erp-kpi-label">{{ __('erp.document.due_date') }}</dt><dd class="mt-1 text-[0.8125rem] font-medium tabular-nums" dir="ltr">{{ $due?->format('Y-m-d') ?? '-' }}</dd></div>
                <div><dt class="erp-kpi-label">{{ __('erp.document.currency') }}</dt><dd class="mt-1 text-[0.8125rem] font-medium" dir="ltr">{{ $invoice->currency }}</dd></div>
                <div><dt class="erp-kpi-label">{{ __('erp.document.reference') }}</dt><dd class="mt-1 text-[0.8125rem] font-medium">{{ $invoice->supplier_invoice_number ?? $invoice->reference ?? '-' }}</dd></div>
            </dl></x-ui.card>
            <x-ui.card :title="__('erp.document.lines')" flush>
                <x-ui.table flush><thead><tr><th>#</th><th>{{ __('erp.sales_invoice.line_description') }}</th><th class="!text-end">{{ __('erp.sales_invoice.quantity') }}</th><th class="!text-end">{{ __('erp.sales_invoice.unit_price') }}</th><th class="!text-end">{{ __('erp.document.tax') }}</th><th class="!text-end">{{ __('erp.document.amount') }}</th></tr></thead>
                <tbody>@foreach($invoice->lines as $line)<tr><td>{{ $line->line_no }}</td><td>{{ $line->description }}</td><td class="text-end tabular-nums">{{ $line->quantity }}</td><td class="text-end"><x-ui.money :amount="$line->unit_price" /></td><td class="text-end"><x-ui.money :amount="$line->tax_amount" /></td><td class="text-end"><x-ui.money :amount="$line->net_amount" /></td></tr>@endforeach</tbody></x-ui.table>
                <div class="flex justify-end border-t border-border bg-surface-sunken px-5 py-4"><dl class="w-full max-w-xs space-y-2 text-[0.8125rem]">
                    <div class="flex justify-between"><dt>{{ __('erp.document.subtotal') }}</dt><dd><x-ui.money :amount="$invoice->net_total" /></dd></div>
                    <div class="flex justify-between"><dt>{{ __('erp.document.tax') }}</dt><dd><x-ui.money :amount="$invoice->tax_total" /></dd></div>
                    <div class="flex justify-between border-t border-border-strong pt-2 font-semibold"><dt>{{ __('erp.document.total') }}</dt><dd><x-ui.money :amount="$invoice->gross_total" :currency="$invoice->currency" size="lg" /></dd></div>
                    <div class="flex justify-between"><dt>{{ __('erp.document.open_balance') }}</dt><dd><x-ui.money :amount="$invoice->openBalance()" :muted="$open == 0.0" /></dd></div>
                </dl></div>
            </x-ui.card>
        </div>
        <div class="space-y-5"><x-ui.card :title="__('erp.document.accounting')" :tone="$posted ? 'brand' : null">
            @if($invoice->journal)<x-ui.detail-list :rows="[['label'=>__('erp.document.posting_date'),'value'=>$invoice->journal->posting_date?->format('Y-m-d'),'dir'=>'ltr'],['label'=>__('erp.book'),'value'=>$invoice->book?->code,'dir'=>'ltr']]">
                <div class="flex justify-between py-2"><dt>{{ __('erp.document.journal') }}</dt><dd><a href="{{ route('gl.journals.show',$invoice->journal_id) }}" wire:navigate class="text-[var(--brand-600)]">{{ $invoice->journal->number ?? '#'.$invoice->journal_id }}</a></dd></div>
            </x-ui.detail-list>@else<p class="text-sm text-muted-foreground">{{ __('erp.document.not_posted_yet') }}</p>@endif
        </x-ui.card><x-ui.card :title="__('erp.audit.title')"><x-ui.audit-timeline :events="$timeline" /></x-ui.card></div>
    </div>
</div>
