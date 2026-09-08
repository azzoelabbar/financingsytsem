<div>
    @php
        $posted = $invoice->status->isPosted();
        $open = (float) $invoice->openBalance();
        $due = $invoice->due_date;
        $isOverdue = $posted && $open > 0 && $due !== null && $due->isPast();
        $customerName = app()->getLocale() === 'ar'
            ? ($invoice->customer?->name_ar ?? $invoice->customer?->name_en)
            : ($invoice->customer?->name_en ?? $invoice->customer?->name_ar);
    @endphp

    <x-ui.entity-header
        :title="$invoice->number ?? __('erp.sales_invoice.draft_number')"
        :eyebrow="__('erp.sales_invoice.entity')"
        :subtitle="$customerName"
        code="INV"
        :status="$invoice->status"
        :tone="$posted ? 'brand' : 'neutral'"
        :breadcrumbs="[
            ['label' => __('erp.nav.ar')],
            ['label' => __('erp.nav.sales_invoices'), 'href' => route('ar.invoices')],
            ['label' => $invoice->number ?? __('erp.sales_invoice.draft_number')],
        ]"
    >
        <x-slot:badges>
            @if ($isOverdue)
                <x-ui.badge variant="danger" size="md">{{ __('erp.aging.overdue') }}</x-ui.badge>
            @elseif ($posted && $open == 0.0)
                <x-ui.badge variant="success" size="md">{{ __('erp.doc_status.paid') }}</x-ui.badge>
            @endif
        </x-slot:badges>

        <x-slot:actions>
            <x-ui.export-button />
            <x-ui.button variant="ghost" :href="route('ar.invoices')">{{ __('erp.action.back') }}</x-ui.button>

            @if ($invoice->journal)
                <x-ui.button variant="secondary" :href="route('gl.journals.show', $invoice->journal_id)">
                    {{ __('erp.document.view_journal') }}
                </x-ui.button>
            @endif

            @if ($invoice->status->isMutable())
                <x-ui.confirm-action
                    action="post"
                    :label="__('erp.action.post')"
                    :title="__('erp.sales_invoice.post_confirm_title')"
                    :message="__('erp.sales_invoice.post_confirm_body')"
                    :confirm-label="__('erp.action.confirm_post')"
                />
            @endif
        </x-slot:actions>

        <x-slot:metrics>
            <x-ui.metric :label="__('erp.document.total')">
                <x-ui.money :amount="$invoice->gross_total" :currency="$invoice->currency" />
            </x-ui.metric>
            <x-ui.metric :label="__('erp.document.open_balance')">
                <x-ui.money :amount="$invoice->openBalance()" :muted="$open == 0.0" :tone="$isOverdue ? 'danger' : null" />
            </x-ui.metric>
            <x-ui.metric :label="__('erp.document.due_date')" :hint="$isOverdue ? __('erp.aging.overdue') : null">
                <span class="tabular-nums" dir="ltr">{{ $due?->format('Y-m-d') ?? '-' }}</span>
            </x-ui.metric>
            <x-ui.metric :label="__('erp.document.currency')">
                <span dir="ltr">{{ $invoice->currency }}</span>
            </x-ui.metric>
        </x-slot:metrics>
    </x-ui.entity-header>

    @error('posting')
        <x-ui.alert variant="danger" class="mb-5" :title="__('erp.action.post')">
            {{ $message }}
        </x-ui.alert>
    @enderror

    @if ($posted)
        <x-ui.posted-notice />
    @endif

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
        {{-- Primary column: what was sold and for how much. --}}
        <div class="space-y-5 lg:col-span-2">
            <x-ui.card :title="__('erp.form.parties')">
                <dl class="grid grid-cols-2 gap-x-6 gap-y-4 sm:grid-cols-3">
                    <div>
                        <dt class="erp-kpi-label">{{ __('erp.sales_invoice.customer') }}</dt>
                        <dd class="mt-1 text-[0.8125rem] font-medium">
                            <a href="{{ route('ar.customers.show', $invoice->customer_id) }}" wire:navigate class="text-[var(--brand-600)] hover:underline">
                                {{ $customerName }}
                            </a>
                        </dd>
                    </div>
                    <div>
                        <dt class="erp-kpi-label">{{ __('erp.sales_invoice.invoice_date') }}</dt>
                        <dd class="mt-1 text-[0.8125rem] font-medium tabular-nums" dir="ltr">{{ $invoice->invoice_date?->format('Y-m-d') }}</dd>
                    </div>
                    <div>
                        <dt class="erp-kpi-label">{{ __('erp.document.due_date') }}</dt>
                        <dd class="mt-1 text-[0.8125rem] font-medium tabular-nums {{ $isOverdue ? 'text-[var(--danger)]' : '' }}" dir="ltr">{{ $due?->format('Y-m-d') ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="erp-kpi-label">{{ __('erp.document.currency') }}</dt>
                        <dd class="mt-1 text-[0.8125rem] font-medium" dir="ltr">{{ $invoice->currency }}</dd>
                    </div>
                    <div>
                        <dt class="erp-kpi-label">{{ __('erp.document.exchange_rate') }}</dt>
                        <dd class="mt-1 text-[0.8125rem] font-medium tabular-nums" dir="ltr">{{ rtrim(rtrim((string) $invoice->exchange_rate, '0'), '.') }}</dd>
                    </div>
                    <div>
                        <dt class="erp-kpi-label">{{ __('erp.document.reference') }}</dt>
                        <dd class="mt-1 text-[0.8125rem] font-medium">{{ $invoice->reference ?? '-' }}</dd>
                    </div>
                </dl>
            </x-ui.card>

            <x-ui.card :title="__('erp.document.lines')" flush>
                <x-ui.table flush>
                    <thead>
                        <tr>
                            <th class="w-10">#</th>
                            <th>{{ __('erp.sales_invoice.line_description') }}</th>
                            <th class="!text-end">{{ __('erp.sales_invoice.quantity') }}</th>
                            <th class="!text-end">{{ __('erp.sales_invoice.unit_price') }}</th>
                            <th class="!text-end">{{ __('erp.document.tax') }}</th>
                            <th class="!text-end">{{ __('erp.document.amount') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invoice->lines as $line)
                            <tr wire:key="line-{{ $line->id }}">
                                <td class="tabular-nums text-muted-foreground">{{ $line->line_no }}</td>
                                <td class="text-foreground">{{ $line->description }}</td>
                                <td class="text-end tabular-nums" dir="ltr">{{ rtrim(rtrim((string) $line->quantity, '0'), '.') }}</td>
                                <td class="text-end"><x-ui.money :amount="$line->unit_price" /></td>
                                <td class="text-end"><x-ui.money :amount="$line->tax_amount" :muted="(float) $line->tax_amount == 0" /></td>
                                <td class="text-end"><x-ui.money :amount="$line->net_amount" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </x-ui.table>

                {{-- Totals ladder: subtotal, tax, total, then what is still open. --}}
                <div class="flex justify-end border-t border-border bg-surface-sunken px-5 py-4">
                    <dl class="w-full max-w-xs space-y-2 text-[0.8125rem]">
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">{{ __('erp.document.subtotal') }}</dt>
                            <dd><x-ui.money :amount="$invoice->net_total" /></dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">{{ __('erp.document.tax') }}</dt>
                            <dd><x-ui.money :amount="$invoice->tax_total" :muted="(float) $invoice->tax_total == 0" /></dd>
                        </div>
                        <div class="flex items-baseline justify-between border-t border-border-strong pt-2">
                            <dt class="font-semibold">{{ __('erp.document.total') }}</dt>
                            <dd><x-ui.money :amount="$invoice->gross_total" :currency="$invoice->currency" size="lg" /></dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">{{ __('erp.document.open_balance') }}</dt>
                            <dd><x-ui.money :amount="$invoice->openBalance()" :muted="$open == 0.0" :tone="$isOverdue ? 'danger' : null" /></dd>
                        </div>
                    </dl>
                </div>
            </x-ui.card>
        </div>

        {{-- Side column: how the document hit the ledger, and what happened to it. --}}
        <div class="space-y-5">
            <x-ui.card :title="__('erp.document.accounting')" :tone="$posted ? 'brand' : null">
                @if ($invoice->journal)
                    <x-ui.detail-list :rows="[
                        ['label' => __('erp.document.posting_date'), 'value' => $invoice->journal->posting_date?->format('Y-m-d'), 'dir' => 'ltr'],
                        ['label' => __('erp.book'), 'value' => $invoice->book?->code, 'dir' => 'ltr'],
                    ]">
                        <div class="flex items-baseline justify-between gap-3 py-2">
                            <dt class="text-[0.8125rem] text-muted-foreground">{{ __('erp.document.journal') }}</dt>
                            <dd>
                                <a href="{{ route('gl.journals.show', $invoice->journal_id) }}" wire:navigate class="text-[0.8125rem] font-medium text-[var(--brand-600)] hover:underline" dir="ltr">
                                    {{ $invoice->journal->number ?? '#'.$invoice->journal_id }}
                                </a>
                            </dd>
                        </div>
                    </x-ui.detail-list>
                @else
                    <p class="text-[0.8125rem] leading-relaxed text-muted-foreground">{{ __('erp.document.not_posted_yet') }}</p>
                @endif
            </x-ui.card>

            <x-ui.card :title="__('erp.audit.title')">
                <x-ui.audit-timeline :events="$timeline" />
            </x-ui.card>
        </div>
    </div>
</div>
