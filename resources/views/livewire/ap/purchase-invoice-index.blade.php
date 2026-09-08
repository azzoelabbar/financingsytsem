<div>
    <x-ui.page-header
        :breadcrumbs="[['label' => __('erp.nav.ap')], ['label' => __('erp.nav.purchase_invoices')]]"
        :title="__('erp.purchase_invoice.title')"
        :description="__('erp.list.purchase_invoices_hint')"
    >
        <x-slot:actions>
            <x-ui.import-button kind="purchases" />
            <x-ui.export-button />
            <x-ui.button variant="secondary" :href="route('ap.aging')">{{ __('erp.nav.ap_aging') }}</x-ui.button>
            <x-ui.button :href="route('ap.invoices.create')">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 4a1 1 0 011 1v4h4a1 1 0 110 2h-4v4a1 1 0 11-2 0v-4H5a1 1 0 110-2h4V5a1 1 0 011-1z"/></svg>
                {{ __('erp.purchase_invoice.create') }}
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @php
        $cur = $company?->functional_currency ?? '';
        $buckets = $aging['buckets'] ?? [];
        $overdue = 0.0;
        foreach (['1_30', '31_60', '61_90', '91_120', '120_plus'] as $bucket) {
            $overdue += (float) ($buckets[$bucket] ?? 0);
        }
    @endphp

    @if ($aging !== null && (float) ($aging['total'] ?? 0) != 0)
        {{-- Payables position: what this list adds up to, and how much of it is late. --}}
        <div class="mb-5 grid gap-3 sm:grid-cols-3">
            <x-ui.stat :label="__('erp.dashboard_kpi.ap_total')" tone="brand" :href="route('ap.aging')">
                <x-ui.money :amount="$aging['total'] ?? '0'" :currency="$cur" size="lg" />
            </x-ui.stat>
            <x-ui.stat :label="__('erp.aging.current')" tone="success">
                <x-ui.money :amount="$buckets['current'] ?? '0'" :currency="$cur" size="lg" />
            </x-ui.stat>
            <x-ui.stat :label="__('erp.aging.overdue_total')" :tone="$overdue > 0 ? 'warning' : 'success'">
                <x-ui.money :amount="(string) $overdue" :currency="$cur" size="lg" :tone="$overdue > 0 ? 'warning' : null" />
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
            :title="$search !== '' || $statusFilter !== '' ? __('erp.filter.no_matches_title') : __('erp.purchase_invoice.empty_title')"
            :message="$search !== '' || $statusFilter !== '' ? __('erp.filter.no_matches_hint') : __('erp.purchase_invoice.empty_description')"
        >
            <x-slot:actions>
                @if ($search !== '' || $statusFilter !== '')
                    <x-ui.button variant="secondary" x-on:click="$wire.$set('search', '', false); $wire.$set('statusFilter', '')">{{ __('erp.filter.clear') }}</x-ui.button>
                @else
                    <x-ui.button :href="route('ap.invoices.create')">{{ __('erp.purchase_invoice.create') }}</x-ui.button>
                @endif
            </x-slot:actions>
        </x-ui.empty-state>
    @else
        <x-ui.table>
            <thead>
                <tr>
                    <th>{{ __('erp.number') }}</th>
                    <th>{{ __('erp.purchase_invoice.supplier') }}</th>
                    <th>{{ __('erp.date') }}</th>
                    <th>{{ __('erp.document.due_date') }}</th>
                    <th class="!text-end">{{ __('erp.total') }}</th>
                    <th class="!text-end">{{ __('erp.document.open_balance') }}</th>
                    <th>{{ __('erp.status') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoices as $invoice)
                    <tr wire:key="pi-{{ $invoice->id }}">
                        <td class="font-medium text-foreground" dir="ltr"><a href="{{ route('ap.invoices.show',$invoice->id) }}" wire:navigate class="text-[var(--brand-600)] hover:underline">{{ $invoice->number ?? __('erp.sales_invoice.draft_number') }}</a></td>
                        <td>{{ $invoice->supplier?->legal_name }}</td>
                        <td class="tabular-nums text-muted-foreground" dir="ltr">{{ $invoice->invoice_date?->format('Y-m-d') }}</td>
                        <td class="tabular-nums text-muted-foreground" dir="ltr">{{ $invoice->due_date?->format('Y-m-d') ?? '-' }}</td>
                        <td class="text-end"><x-ui.money :amount="$invoice->gross_total ?? '0'" :currency="$invoice->currency" /></td>
                        <td class="text-end"><x-ui.money :amount="$invoice->openBalance()" :muted="(float) $invoice->openBalance() == 0" /></td>
                        <td><x-ui.status-badge :status="$invoice->status" /></td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui.table>

        <x-ui.pagination :paginator="$invoices" />
    @endif
</div>
