{{-- Expects: $items (list of {type,id,number,date,currency,amount,open}); optional $side ('ar'|'ap') for drill links --}}
@php($side = $side ?? null)
<x-ui.table :flush="$flush ?? false">
    <thead><tr>
        <th>{{ __('erp.open_items.document') }}</th>
        <th>{{ __('erp.open_items.doc_type') }}</th>
        <th>{{ __('erp.date') }}</th>
        <th>{{ __('erp.currency') }}</th>
        <th class="!text-end">{{ __('erp.open_items.original') }}</th>
        <th class="!text-end">{{ __('erp.open_items.open') }}</th>
    </tr></thead>
    <tbody>
        @foreach ($items as $item)
            <tr wire:key="oi-{{ $item['type'] }}-{{ $item['id'] }}">
                <td class="font-medium">
                    @if ($side === 'ar' && $item['type'] === 'invoice')
                        <a href="{{ route('ar.invoices.show', $item['id']) }}" wire:navigate class="text-[var(--brand-600)] hover:underline">{{ $item['number'] ?? '-' }}</a>
                    @else
                        {{ $item['number'] ?? '-' }}
                    @endif
                </td>
                <td class="text-muted-foreground">{{ __('erp.open_items.types.'.$item['type']) }}</td>
                <td class="tabular-nums text-muted-foreground" dir="ltr">{{ $item['date'] }}</td>
                <td>{{ $item['currency'] }}</td>
                <td class="text-end"><x-ui.money :amount="$item['amount']" /></td>
                <td class="text-end"><x-ui.money :amount="$item['open']" /></td>
            </tr>
        @endforeach
    </tbody>
</x-ui.table>
