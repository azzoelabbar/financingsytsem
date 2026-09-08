<div>
    <x-ui.page-header :breadcrumbs="[['label' => __('erp.nav.expenses')]]" :title="__('erp.expense.title')" :description="__('erp.expense.hint')"><x-slot:actions><x-ui.import-button kind="expenses" /><x-ui.export-button /><x-ui.button :href="route('expenses.create')">{{ __('erp.expense.create') }}</x-ui.button></x-slot:actions></x-ui.page-header>

    <x-ui.toolbar :summary="$expenses ? trans_choice('erp.pagination.result_count', $expenses->total(), ['count' => number_format($expenses->total())]) : null" />

    @if ($expenses === null || $expenses->isEmpty())
        <x-ui.empty-state :title="__('erp.expense.empty_title')" :message="__('erp.expense.empty_hint')"><x-slot:actions><x-ui.button :href="route('expenses.create')">{{ __('erp.expense.create') }}</x-ui.button></x-slot:actions></x-ui.empty-state>
    @else
        <x-ui.table>
            <thead><tr>
                <th>{{ __('erp.number') }}</th>
                <th>{{ __('erp.expense.employee') }}</th>
                <th>{{ __('erp.date') }}</th>
                <th class="!text-end">{{ __('erp.amount') }}</th>
                <th>{{ __('erp.status') }}</th>
            </tr></thead>
            <tbody>
                @foreach ($expenses as $expense)
                    <tr wire:key="exp-{{ $expense->id }}">
                        <td><a href="{{ route('expenses.show', $expense->id) }}" wire:navigate class="font-medium text-[var(--brand-600)] hover:underline">{{ $expense->number ?? __('erp.expense.draft') }}</a></td>
                        <td>{{ $expense->employee_ref ?? '-' }}</td>
                        <td class="tabular-nums text-muted-foreground" dir="ltr">{{ $expense->claim_date?->format('Y-m-d') }}</td>
                        <td class="text-end"><x-ui.money :amount="$expense->amount" /></td>
                        <td><x-ui.status-badge :status="$expense->status" /></td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui.table>
        <x-ui.pagination :paginator="$expenses" />
    @endif
</div>
