<div>
    <x-ui.page-header :breadcrumbs="[['label' => __('erp.nav.gl')], ['label' => __('erp.nav.account_balances')]]" :title="__('erp.nav.account_balances')" :description="__('erp.account_balances.hint')" />

    <x-ui.toolbar />

    @if ($rows === [])
        <x-ui.empty-state :message="__('erp.no_data')" />
    @else
        @php
            $totalDebit = '0'; $totalCredit = '0';
            foreach ($rows as $r) {
                $totalDebit = \App\Services\Accounting\Support\Decimal::add($totalDebit, $r['debit']);
                $totalCredit = \App\Services\Accounting\Support\Decimal::add($totalCredit, $r['credit']);
            }
        @endphp
        <x-ui.table>
            <thead><tr>
                <th>{{ __('erp.code') }}</th>
                <th>{{ __('erp.name') }}</th>
                <th class="!text-end">{{ __('erp.debit') }}</th>
                <th class="!text-end">{{ __('erp.credit') }}</th>
                <th class="!text-end">{{ __('erp.balance') }}</th>
            </tr></thead>
            <tbody>
                @foreach ($rows as $row)
                    <tr wire:key="ab-{{ $row['code'] }}">
                        <td class="font-mono text-xs text-muted-foreground" dir="ltr">{{ $row['code'] }}</td>
                        <td>{{ $row['name_ar'] }}</td>
                        <td class="text-end"><x-ui.money :amount="$row['debit']" muted /></td>
                        <td class="text-end"><x-ui.money :amount="$row['credit']" muted /></td>
                        <td class="text-end"><x-ui.money :amount="$row['balance']" /></td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="border-t-2 border-border-strong font-semibold [&_td]:py-2.5">
                    <td colspan="2" class="px-4">{{ __('erp.total') }}</td>
                    <td class="px-4 text-end"><x-ui.money :amount="$totalDebit" /></td>
                    <td class="px-4 text-end"><x-ui.money :amount="$totalCredit" /></td>
                    <td></td>
                </tr>
            </tfoot>
        </x-ui.table>
    @endif
</div>
