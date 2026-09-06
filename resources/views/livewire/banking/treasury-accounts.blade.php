<div>
    <x-ui.page-header :breadcrumbs="[['label' => __('erp.nav.banking')]]"
        :title="$type === 'cash' ? __('erp.banking.cash_accounts') : __('erp.banking.bank_accounts')"
        :description="$type === 'cash' ? __('erp.banking.cash_hint') : __('erp.banking.bank_hint')"
    ><x-slot:actions><x-ui.button :href="$type==='cash'?route('banking.cash-accounts.create'):route('banking.bank-accounts.create')">{{ $type==='cash'?__('erp.banking.create_cash_account'):__('erp.banking.create_bank_account') }}</x-ui.button></x-slot:actions></x-ui.page-header>

    <x-ui.toolbar :summary="$accounts ? trans_choice('erp.pagination.result_count', $accounts->total(), ['count' => number_format($accounts->total())]) : null" />

    @if ($accounts === null || $accounts->isEmpty())
        <x-ui.empty-state :title="__('erp.banking.empty_title')" :message="__('erp.banking.empty_hint')"><x-slot:actions><x-ui.button :href="$type==='cash'?route('banking.cash-accounts.create'):route('banking.bank-accounts.create')">{{ $type==='cash'?__('erp.banking.create_cash_account'):__('erp.banking.create_bank_account') }}</x-ui.button></x-slot:actions></x-ui.empty-state>
    @else
        <x-ui.table>
            <thead><tr>
                <th>{{ __('erp.code') }}</th>
                <th>{{ __('erp.name') }}</th>
                @if ($type === 'bank')
                    <th>{{ __('erp.banking.bank') }}</th>
                    <th>{{ __('erp.banking.account_number') }}</th>
                @endif
                <th>{{ __('erp.currency') }}</th>
                <th class="!text-end">{{ __('erp.banking.opening_balance') }}</th>
                <th>{{ __('erp.status') }}</th>
            </tr></thead>
            <tbody>
                @foreach ($accounts as $acc)
                    <tr wire:key="ta-{{ $acc->id }}">
                        <td class="font-mono text-xs text-muted-foreground" dir="ltr"><a href="{{ route('banking.accounts.show',$acc) }}" wire:navigate class="text-[var(--brand-600)]">{{ $acc->code }}</a></td>
                        <td class="font-medium">{{ app()->getLocale() === 'ar' ? ($acc->name_ar ?? $acc->name_en) : ($acc->name_en ?? $acc->name_ar) }}</td>
                        @if ($type === 'bank')
                            <td>{{ app()->getLocale() === 'ar' ? ($acc->bank?->name_ar ?? $acc->bank?->name_en) : ($acc->bank?->name_en ?? $acc->bank?->name_ar) }}</td>
                            <td class="font-mono text-xs text-muted-foreground" dir="ltr">{{ $acc->account_number ?? $acc->iban ?? '-' }}</td>
                        @endif
                        <td>{{ $acc->currency }}</td>
                        <td class="text-end"><x-ui.money :amount="$acc->opening_balance" :currency="$acc->currency" /></td>
                        <td><x-ui.status-badge :status="$acc->is_active ? 'active' : 'void'" /></td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui.table>
        <x-ui.pagination :paginator="$accounts" />
    @endif
</div>
