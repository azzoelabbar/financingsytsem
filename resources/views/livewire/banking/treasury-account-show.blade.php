<div>
    <x-ui.entity-header
        :title="app()->getLocale() === 'ar' ? ($account->name_ar ?? $account->name_en) : ($account->name_en ?? $account->name_ar)"
        :eyebrow="$account->isBank() ? __('erp.banking.bank_account') : __('erp.banking.cash_account')"
        :subtitle="$account->code"
        :status="$account->is_active ? 'active' : 'inactive'"
        :code="$account->isBank() ? 'BANK' : 'CASH'"
        :breadcrumbs="[['label' => __('erp.nav.banking')], ['label' => $account->code]]"
    >
        <x-slot:actions>
            <x-ui.button variant="ghost" :href="$account->isBank() ? route('banking.bank-accounts') : route('banking.cash-accounts')">{{ __('erp.action.back') }}</x-ui.button>
            <x-ui.button :href="route('banking.transactions.create')"><x-ui.icon name="plus" class="h-4 w-4" />{{ __('erp.banking.create_transaction') }}</x-ui.button>
        </x-slot:actions>
        <x-slot:metrics>
            <x-ui.metric :label="__('erp.banking.book_balance')"><x-ui.money :amount="$bookBalance" :currency="$account->currency" /></x-ui.metric>
            <x-ui.metric :label="__('erp.currency')">{{ $account->currency }}</x-ui.metric>
            <x-ui.metric :label="__('erp.banking.gl_account')">{{ $account->gl_account_code }}</x-ui.metric>
            <x-ui.metric :label="__('erp.status')"><x-ui.status-badge :status="$account->is_active ? 'active' : 'inactive'" /></x-ui.metric>
        </x-slot:metrics>
    </x-ui.entity-header>

    <div class="grid items-start gap-5 xl:grid-cols-[minmax(0,1fr)_18rem]">
        <x-ui.card :title="__('erp.banking.transactions')" flush>
            @if ($account->transactions->isEmpty())
                <x-ui.empty-state variant="panel" :message="__('erp.banking.tx_empty_hint')" />
            @else
                <x-ui.table flush>
                    <thead>
                        <tr>
                            <th>{{ __('erp.number') }}</th>
                            <th>{{ __('erp.date') }}</th>
                            <th>{{ __('erp.banking.tx_type') }}</th>
                            <th class="!text-end">{{ __('erp.amount') }}</th>
                            <th>{{ __('erp.status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($account->transactions as $tx)
                            <tr wire:key="treasury-tx-{{ $tx->id }}">
                                <td><a href="{{ route('banking.transactions.show', $tx) }}" wire:navigate class="font-medium text-brand hover:underline" dir="ltr">{{ $tx->number ?? __('erp.sales_invoice.draft_number') }}</a></td>
                                <td class="whitespace-nowrap text-muted-foreground" dir="ltr">{{ $tx->transaction_date?->format('Y-m-d') }}</td>
                                <td>{{ __('erp.banking.tx_types.'.$tx->type->value) }}</td>
                                <td class="text-end"><x-ui.money :amount="$tx->amount" :currency="$tx->currency" /></td>
                                <td><x-ui.status-badge :status="$tx->status" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </x-ui.table>
            @endif
        </x-ui.card>

        <x-ui.card :title="__('erp.banking.account_details')">
            <x-ui.detail-list :rows="[
                ['label' => __('erp.code'), 'value' => $account->code, 'dir' => 'ltr'],
                ['label' => __('erp.banking.bank'), 'value' => app()->getLocale() === 'ar' ? ($account->bank?->name_ar ?? $account->bank?->name_en ?? '-') : ($account->bank?->name_en ?? $account->bank?->name_ar ?? '-')],
                ['label' => __('erp.banking.account_number'), 'value' => $account->account_number ?? '-', 'dir' => 'ltr'],
                ['label' => 'IBAN', 'value' => $account->iban ?? '-', 'dir' => 'ltr'],
            ]" />
        </x-ui.card>
    </div>
</div>
