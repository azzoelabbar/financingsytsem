<div>
    <x-ui.page-header
        :breadcrumbs="[['label' => __('erp.nav.ap')], ['label' => __('erp.nav.suppliers')]]"
        :title="__('erp.supplier.title')"
        :description="__('erp.list.suppliers_hint')"
    >
        <x-slot:actions>
            <x-ui.import-button kind="suppliers" />
            <x-ui.export-button />
            <x-ui.button :href="route('ap.suppliers.create')">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 4a1 1 0 011 1v4h4a1 1 0 110 2h-4v4a1 1 0 11-2 0v-4H5a1 1 0 110-2h4V5a1 1 0 011-1z"/></svg>
                {{ __('erp.supplier.create') }}
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.toolbar :summary="$suppliers ? trans_choice('erp.pagination.result_count', $suppliers->total(), ['count' => number_format($suppliers->total())]) : null" />

    @if ($suppliers === null || $suppliers->isEmpty())
        <x-ui.empty-state
            :title="$search !== '' ? __('erp.filter.no_matches_title') : __('erp.supplier.empty_title')"
            :message="$search !== '' ? __('erp.filter.no_matches_hint') : __('erp.supplier.empty_description')"
        >
            <x-slot:actions>
                @if ($search !== '')
                    <x-ui.button variant="secondary" wire:click="$set('search', '')">{{ __('erp.filter.clear') }}</x-ui.button>
                @else
                    <x-ui.button :href="route('ap.suppliers.create')">{{ __('erp.supplier.create') }}</x-ui.button>
                @endif
            </x-slot:actions>
        </x-ui.empty-state>
    @else
        <x-ui.table>
            <thead>
                <tr>
                    <th>{{ __('erp.code') }}</th>
                    <th>{{ __('erp.supplier.legal_name') }}</th>
                    <th>{{ __('erp.currency') }}</th>
                    <th>{{ __('erp.status') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($suppliers as $supplier)
                    <tr wire:key="supplier-{{ $supplier->id }}">
                        <td class="font-mono text-xs font-semibold text-foreground" dir="ltr">{{ $supplier->code }}</td>
                        <td class="font-medium text-foreground">{{ $supplier->legal_name }}</td>
                        <td class="text-muted-foreground" dir="ltr">{{ $supplier->currency }}</td>
                        <td>
                            <x-ui.badge :variant="($supplier->is_active ?? true) ? 'success' : 'outline'">
                                {{ ($supplier->is_active ?? true) ? __('erp.active') : __('erp.inactive') }}
                            </x-ui.badge>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui.table>

        <x-ui.pagination :paginator="$suppliers" />
    @endif
</div>
