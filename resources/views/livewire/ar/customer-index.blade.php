<div>
    <x-ui.page-header
        :title="__('erp.customer.title')"
        :description="__('erp.list.customers_hint')"
        :breadcrumbs="[['label' => __('erp.nav.ar')], ['label' => __('erp.nav.customers')]]"
    >
        <x-slot:actions>
            <x-ui.import-button kind="customers" />
            <x-ui.export-button />
            <x-ui.button :href="route('ar.customers.create')">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 4a1 1 0 011 1v4h4a1 1 0 110 2h-4v4a1 1 0 11-2 0v-4H5a1 1 0 110-2h4V5a1 1 0 011-1z"/></svg>
                {{ __('erp.customer.create') }}
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.toolbar :summary="$customers ? trans_choice('erp.pagination.result_count', $customers->total(), ['count' => number_format($customers->total())]) : null" />

    @if ($customers === null || $customers->isEmpty())
        <x-ui.empty-state
            :title="$search !== '' ? __('erp.filter.no_matches_title') : __('erp.customer.empty_title')"
            :message="$search !== '' ? __('erp.filter.no_matches_hint') : __('erp.customer.empty_description')"
        >
            <x-slot:actions>
                @if ($search !== '')
                    <x-ui.button variant="secondary" wire:click="$set('search', '')">{{ __('erp.filter.clear') }}</x-ui.button>
                @else
                    <x-ui.button :href="route('ar.customers.create')">{{ __('erp.customer.create') }}</x-ui.button>
                @endif
            </x-slot:actions>
        </x-ui.empty-state>
    @else
        <x-ui.table>
            <thead>
                <tr>
                    <th>{{ __('erp.code') }}</th>
                    <th>{{ __('erp.name') }}</th>
                    <th>{{ __('erp.currency') }}</th>
                    <th>{{ __('erp.status') }}</th>
                    <th class="w-px"><span class="sr-only">{{ __('erp.actions') }}</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($customers as $customer)
                    <tr wire:key="customer-{{ $customer->id }}">
                        <td>
                            <a href="{{ route('ar.customers.show', $customer) }}" wire:navigate class="font-mono text-xs font-semibold text-[var(--brand-600)] hover:underline" dir="ltr">{{ $customer->code }}</a>
                        </td>
                        <td class="font-medium text-foreground">{{ $customer->name_ar }}</td>
                        <td class="text-muted-foreground" dir="ltr">{{ $customer->currency }}</td>
                        <td>
                            <x-ui.badge :variant="$customer->is_active ? 'success' : 'outline'">
                                {{ $customer->is_active ? __('erp.active') : __('erp.inactive') }}
                            </x-ui.badge>
                        </td>
                        <td class="text-end">
                            <x-ui.button variant="secondary" size="sm" :href="route('ar.customers.show', $customer)">{{ __('erp.overview') }}</x-ui.button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui.table>

        <x-ui.pagination :paginator="$customers" />
    @endif
</div>
