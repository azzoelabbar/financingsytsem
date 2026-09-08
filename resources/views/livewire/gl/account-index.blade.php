<div>
    <x-ui.page-header :breadcrumbs="[['label' => __('erp.nav.gl')], ['label' => __('erp.nav.accounts')]]" :title="__('erp.nav.accounts')" :description="__('erp.accounts.description')">
        <x-slot:actions>
            <x-ui.export-button />
        </x-slot:actions>
    </x-ui.page-header>
    <x-ui.toolbar :summary="$accounts ? trans_choice('erp.pagination.result_count', $accounts->total(), ['count' => number_format($accounts->total())]) : null" />

    @if ($accounts === null || $accounts->isEmpty())
        <x-ui.empty-state :title="__('erp.accounts.empty_title')" :message="__('erp.accounts.empty_description')" />
    @else
        <x-ui.table>
            <thead>
                <tr>
                    <th>{{ __('erp.code') }}</th>
                    <th>{{ __('erp.name') }}</th>
                    <th>{{ __('erp.accounts.type') }}</th>
                    <th>{{ __('erp.accounts.posting') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($accounts as $account)
                    <tr wire:key="account-{{ $account->id }}">
                        <td class="font-mono text-xs font-medium">{{ $account->code }}</td>
                        <td>{{ $account->name_ar }}</td>
                        <td>
                            <x-ui.badge variant="outline">{{ $account->account_type }}</x-ui.badge>
                        </td>
                        <td>
                            <x-ui.badge :variant="$account->is_posting ? 'success' : 'outline'">
                                {{ $account->is_posting ? __('erp.accounts.postable') : __('erp.accounts.header') }}
                            </x-ui.badge>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui.table>
        <x-ui.pagination :paginator="$accounts" />
    @endif
</div>
