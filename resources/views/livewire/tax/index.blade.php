<div>
    <x-ui.page-header :breadcrumbs="[['label' => __('erp.nav.tax')], ['label' => __('erp.nav.tax_codes')]]" :title="__('erp.tax.codes_title')" :description="__('erp.tax.codes_hint')"><x-slot:actions><x-ui.button :href="route('tax.create')">{{ __('erp.tax.create_code') }}</x-ui.button></x-slot:actions></x-ui.page-header>

    <x-ui.toolbar :summary="$taxCodes ? trans_choice('erp.pagination.result_count', $taxCodes->total(), ['count' => number_format($taxCodes->total())]) : null" />

    @if ($taxCodes === null || $taxCodes->isEmpty())
        <x-ui.empty-state :title="__('erp.tax.codes_empty_title')" :message="__('erp.tax.codes_empty_hint')" />
    @else
        <x-ui.table>
            <thead><tr>
                <th>{{ __('erp.code') }}</th>
                <th>{{ __('erp.name') }}</th>
                <th>{{ __('erp.tax.kind') }}</th>
                <th>{{ __('erp.tax.gl_account') }}</th>
                <th>{{ __('erp.status') }}</th>
            </tr></thead>
            <tbody>
                @foreach ($taxCodes as $code)
                    <tr wire:key="tc-{{ $code->id }}">
                        <td class="font-mono text-xs" dir="ltr">{{ $code->code }}</td>
                        <td class="font-medium">{{ $code->name }}</td>
                        <td><x-ui.badge variant="outline">{{ __('erp.tax.kinds.'.$code->kind) }}</x-ui.badge></td>
                        <td class="font-mono text-xs text-muted-foreground" dir="ltr">{{ $code->gl_account_code }}</td>
                        <td><x-ui.status-badge :status="$code->is_active ? 'active' : 'void'" /></td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui.table>
        <x-ui.pagination :paginator="$taxCodes" />
    @endif
</div>
