<div>
    @php
        $aging = $statement['aging'] ?? [];
        $buckets = $aging['buckets'] ?? [];
        $openItems = $statement['open_items'] ?? [];
        $balance = $statement['balance'] ?? '0';

        $overdue = 0.0;
        foreach (['1_30', '31_60', '61_90', '90_plus'] as $bucket) {
            $overdue += (float) ($buckets[$bucket] ?? 0);
        }

        $displayName = app()->getLocale() === 'ar'
            ? ($customer->name_ar ?? $customer->name_en)
            : ($customer->name_en ?? $customer->name_ar);
    @endphp

    <x-ui.entity-header
        :title="$displayName"
        :eyebrow="__('erp.customer.entity')"
        :subtitle="__('erp.customer.code').': '.$customer->code"
        :code="$customer->code"
        :breadcrumbs="[
            ['label' => __('erp.nav.ar')],
            ['label' => __('erp.nav.customers'), 'href' => route('ar.customers')],
            ['label' => $customer->code],
        ]"
    >
        <x-slot:badges>
            <x-ui.badge :variant="$customer->is_active ? 'success' : 'outline'" size="md">
                {{ $customer->is_active ? __('erp.active') : __('erp.inactive') }}
            </x-ui.badge>
        </x-slot:badges>

        <x-slot:actions>
            <x-ui.button variant="secondary" :href="route('ar.statement')">{{ __('erp.nav.customer_statement') }}</x-ui.button>
            <x-ui.button :href="route('ar.invoices.create')">{{ __('erp.sales_invoice.create') }}</x-ui.button>
        </x-slot:actions>

        <x-slot:metrics>
            <x-ui.metric :label="__('erp.customer.balance_due')">
                <x-ui.money :amount="$balance" :currency="$customer->currency" />
            </x-ui.metric>
            <x-ui.metric :label="__('erp.aging.current')">
                <x-ui.money :amount="$buckets['current'] ?? '0'" />
            </x-ui.metric>
            <x-ui.metric :label="__('erp.aging.overdue_total')">
                <x-ui.money :amount="(string) $overdue" :tone="$overdue > 0 ? 'danger' : null" />
            </x-ui.metric>
            <x-ui.metric :label="__('erp.open_items.count_label')">
                <span class="tabular-nums">{{ count($openItems) }}</span>
            </x-ui.metric>
        </x-slot:metrics>
    </x-ui.entity-header>

    <x-ui.tabs :tabs="[
        ['key' => 'overview', 'label' => __('erp.workspace.overview')],
        ['key' => 'open', 'label' => __('erp.nav.open_items'), 'count' => count($openItems)],
        ['key' => 'invoices', 'label' => __('erp.invoices'), 'count' => $invoices?->total()],
        ['key' => 'receipts', 'label' => __('erp.receipts'), 'count' => $receipts?->total()],
    ]">
        {{-- Overview: who this customer is, and how their balance is aged. --}}
        <div x-show="tab === 'overview'" class="grid gap-5 lg:grid-cols-3">
            <x-ui.card class="lg:col-span-1" :title="__('erp.customer.details')">
                <x-ui.detail-list :rows="[
                    ['label' => __('erp.code'), 'value' => $customer->code, 'mono' => true, 'dir' => 'ltr'],
                    ['label' => __('erp.customer.name_ar'), 'value' => $customer->name_ar],
                    ['label' => __('erp.name'), 'value' => $customer->name_en],
                    ['label' => __('erp.currency'), 'value' => $customer->currency, 'dir' => 'ltr'],
                    ['label' => __('erp.status'), 'value' => $customer->is_active ? __('erp.active') : __('erp.inactive')],
                ]" />
            </x-ui.card>

            <x-ui.card class="lg:col-span-2" :title="__('erp.aging_page.distribution')" :description="__('erp.customer.aging_hint')">
                @if ($buckets === [])
                    <x-ui.empty-state variant="panel" :message="__('erp.customer.no_balance')" />
                @else
                    <dl class="divide-y divide-border">
                        @foreach (['current', '1_30', '31_60', '61_90', '90_plus', 'credits'] as $bucket)
                            @php $amount = (float) ($buckets[$bucket] ?? 0); @endphp
                            <div class="flex items-center justify-between gap-4 py-2">
                                <dt class="text-[0.8125rem] {{ $bucket === 'current' ? 'font-medium text-foreground' : 'text-muted-foreground' }}">{{ __('erp.aging.'.$bucket) }}</dt>
                                <dd>
                                    <x-ui.money
                                        :amount="$buckets[$bucket] ?? '0'"
                                        :muted="$amount == 0.0"
                                        :tone="$amount > 0 && in_array($bucket, ['61_90', '90_plus'], true) ? 'danger' : null"
                                    />
                                </dd>
                            </div>
                        @endforeach
                        <div class="flex items-center justify-between gap-4 border-t border-border-strong pt-3">
                            <dt class="text-[0.8125rem] font-semibold">{{ __('erp.customer.balance_due') }}</dt>
                            <dd><x-ui.money :amount="$balance" :currency="$customer->currency" size="lg" /></dd>
                        </div>
                    </dl>
                @endif
            </x-ui.card>
        </div>

        {{-- Open items: what this customer still owes, document by document. --}}
        <div x-show="tab === 'open'" x-cloak>
            @if ($openItems === [])
                <x-ui.empty-state :title="__('erp.customer.no_open_title')" :message="__('erp.customer.no_open_hint')" />
            @else
                <x-ui.card :title="__('erp.nav.open_items')" flush>
                    @include('livewire.partials.open-items-table', ['items' => $openItems, 'side' => 'ar', 'flush' => true])
                </x-ui.card>
            @endif
        </div>

        <div x-show="tab === 'invoices'" x-cloak>
            @if ($invoices === null || $invoices->isEmpty())
                <x-ui.empty-state :title="__('erp.sales_invoice.empty_title')" :message="__('erp.customer.no_invoices_hint')">
                    <x-slot:actions>
                        <x-ui.button :href="route('ar.invoices.create')">{{ __('erp.sales_invoice.create') }}</x-ui.button>
                    </x-slot:actions>
                </x-ui.empty-state>
            @else
                <x-ui.table>
                    <thead>
                        <tr>
                            <th>{{ __('erp.number') }}</th>
                            <th>{{ __('erp.date') }}</th>
                            <th class="!text-end">{{ __('erp.total') }}</th>
                            <th class="!text-end">{{ __('erp.document.open_balance') }}</th>
                            <th>{{ __('erp.status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invoices as $invoice)
                            <tr wire:key="cust-inv-{{ $invoice->id }}">
                                <td>
                                    <a href="{{ route('ar.invoices.show', $invoice->id) }}" wire:navigate class="font-medium text-[var(--brand-600)] hover:underline" dir="ltr">{{ $invoice->number ?? __('erp.sales_invoice.draft_number') }}</a>
                                </td>
                                <td class="tabular-nums text-muted-foreground" dir="ltr">{{ $invoice->invoice_date?->format('Y-m-d') }}</td>
                                <td class="text-end"><x-ui.money :amount="$invoice->gross_total" /></td>
                                <td class="text-end"><x-ui.money :amount="$invoice->openBalance()" :muted="(float) $invoice->openBalance() == 0" /></td>
                                <td><x-ui.status-badge :status="$invoice->status" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </x-ui.table>
            @endif
        </div>

        <div x-show="tab === 'receipts'" x-cloak>
            @if ($receipts === null || $receipts->isEmpty())
                <x-ui.empty-state :title="__('erp.receipt.empty_title')" :message="__('erp.customer.no_receipts_hint')" />
            @else
                <x-ui.table>
                    <thead>
                        <tr>
                            <th>{{ __('erp.number') }}</th>
                            <th>{{ __('erp.date') }}</th>
                            <th class="!text-end">{{ __('erp.receipt.amount') }}</th>
                            <th class="!text-end">{{ __('erp.receipt.unallocated') }}</th>
                            <th>{{ __('erp.status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($receipts as $receipt)
                            <tr wire:key="cust-rcp-{{ $receipt->id }}">
                                <td>
                                    <a href="{{ route('ar.receipts.show', $receipt->id) }}" wire:navigate class="font-medium text-[var(--brand-600)] hover:underline" dir="ltr">{{ $receipt->number ?? __('erp.sales_invoice.draft_number') }}</a>
                                </td>
                                <td class="tabular-nums text-muted-foreground" dir="ltr">{{ $receipt->receipt_date?->format('Y-m-d') }}</td>
                                <td class="text-end"><x-ui.money :amount="$receipt->amount" positive /></td>
                                <td class="text-end"><x-ui.money :amount="$receipt->unallocated_amount" muted /></td>
                                <td><x-ui.status-badge :status="$receipt->status" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </x-ui.table>
            @endif
        </div>
    </x-ui.tabs>
</div>
