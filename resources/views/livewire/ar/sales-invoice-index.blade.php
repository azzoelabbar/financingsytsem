<div>
    <x-ui.page-header
        :title="__('erp.sales_invoice.title')"
        :description="__('erp.list.invoices_hint')"
        :breadcrumbs="[['label' => __('erp.nav.ar')], ['label' => __('erp.nav.sales_invoices')]]"
    >
        <x-slot:actions>
            <x-ui.import-button kind="sales" />
            <x-ui.export-button />
            <x-ui.button variant="secondary" :href="route('ar.aging')">{{ __('erp.nav.ar_aging') }}</x-ui.button>
            <x-ui.button :href="route('ar.invoices.create')">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 4a1 1 0 011 1v4h4a1 1 0 110 2h-4v4a1 1 0 11-2 0v-4H5a1 1 0 110-2h4V5a1 1 0 011-1z"/></svg>
                {{ __('erp.sales_invoice.create') }}
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @php
        $cur = $company?->functional_currency ?? '';
        $buckets = $aging['buckets'] ?? [];
        $overdue = 0.0;
        foreach (['1_30', '31_60', '61_90', '90_plus'] as $bucket) {
            $overdue += (float) ($buckets[$bucket] ?? 0);
        }
    @endphp

    @if ($aging !== null && (float) ($aging['total'] ?? 0) != 0)
        {{-- Receivables position: what this list adds up to, and how much of it is late. --}}
        <div class="mb-5 grid gap-3 sm:grid-cols-3">
            <x-ui.stat :label="__('erp.dashboard_kpi.ar_total')" tone="brand" :href="route('ar.aging')">
                <x-ui.money :amount="$aging['total'] ?? '0'" :currency="$cur" size="lg" />
            </x-ui.stat>
            <x-ui.stat :label="__('erp.aging.current')" tone="success">
                <x-ui.money :amount="$buckets['current'] ?? '0'" :currency="$cur" size="lg" />
            </x-ui.stat>
            <x-ui.stat :label="__('erp.aging.overdue_total')" :tone="$overdue > 0 ? 'danger' : 'success'">
                <x-ui.money :amount="(string) $overdue" :currency="$cur" size="lg" :tone="$overdue > 0 ? 'danger' : null" />
            </x-ui.stat>
        </div>
    @endif

    <x-ui.toolbar
        :placeholder="__('erp.sales_invoice.search_placeholder')"
        :summary="$invoices ? trans_choice('erp.pagination.result_count', $invoices->total(), ['count' => number_format($invoices->total())]) : null"
    >
        <x-slot:filters>
            <x-ui.searchable-select wire:model.live="statusFilter" :block="false" aria-label="{{ __('erp.status') }}">
                <option value="">{{ __('erp.filter.all_statuses') }}</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                @endforeach
            </x-ui.searchable-select>
        </x-slot:filters>
    </x-ui.toolbar>

    @if ($invoices === null || $invoices->isEmpty())
        <x-ui.empty-state
            :title="$search !== '' || $statusFilter !== '' ? __('erp.filter.no_matches_title') : __('erp.sales_invoice.empty_title')"
            :message="$search !== '' || $statusFilter !== '' ? __('erp.filter.no_matches_hint') : __('erp.sales_invoice.empty_description')"
        >
            <x-slot:actions>
                @if ($search !== '' || $statusFilter !== '')
                    <x-ui.button variant="secondary" x-on:click="$wire.$set('search', '', false); $wire.$set('statusFilter', '')">{{ __('erp.filter.clear') }}</x-ui.button>
                @else
                    <x-ui.button :href="route('ar.invoices.create')">{{ __('erp.sales_invoice.create') }}</x-ui.button>
                @endif
            </x-slot:actions>
        </x-ui.empty-state>
    @else
        <x-ui.table>
            <thead>
                <tr>
                    <th>{{ __('erp.number') }}</th>
                    <th>{{ __('erp.sales_invoice.customer') }}</th>
                    <th>{{ __('erp.date') }}</th>
                    <th>{{ __('erp.document.due_date') }}</th>
                    <th class="!text-end">{{ __('erp.total') }}</th>
                    <th class="!text-end">{{ __('erp.document.open_balance') }}</th>
                    <th>{{ __('erp.status') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoices as $invoice)
                    @php
                        $due = $invoice->due_date;
                        $isOverdue = $due !== null
                            && $invoice->status?->isPosted()
                            && (float) $invoice->openBalance() > 0
                            && $due->isPast();
                    @endphp
                    <tr wire:key="inv-{{ $invoice->id }}">
                        <td>
                            <a href="{{ route('ar.invoices.show', $invoice->id) }}" wire:navigate class="font-medium text-[var(--brand-600)] hover:underline" dir="ltr">
                                {{ $invoice->number ?? __('erp.sales_invoice.draft_number') }}
                            </a>
                        </td>
                        <td class="text-foreground">{{ app()->getLocale() === 'ar' ? ($invoice->customer?->name_ar ?? $invoice->customer?->name_en) : ($invoice->customer?->name_en ?? $invoice->customer?->name_ar) }}</td>
                        <td class="tabular-nums text-muted-foreground" dir="ltr">{{ $invoice->invoice_date?->format('Y-m-d') }}</td>
                        <td dir="ltr">
                            @if ($due === null)
                                <span class="text-muted-foreground">-</span>
                            @elseif ($isOverdue)
                                <span class="inline-flex items-center gap-1.5 font-medium text-[var(--danger)] tabular-nums">
                                    {{ $due->format('Y-m-d') }}
                                    <x-ui.badge variant="danger">{{ __('erp.aging.overdue') }}</x-ui.badge>
                                </span>
                            @else
                                <span class="tabular-nums text-muted-foreground">{{ $due->format('Y-m-d') }}</span>
                            @endif
                        </td>
                        <td class="text-end"><x-ui.money :amount="$invoice->gross_total" :currency="$invoice->currency" /></td>
                        <td class="text-end"><x-ui.money :amount="$invoice->openBalance()" :muted="(float) $invoice->openBalance() == 0" /></td>
                        <td><x-ui.status-badge :status="$invoice->status" /></td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui.table>

        <x-ui.pagination :paginator="$invoices" />
    @endif
</div>
