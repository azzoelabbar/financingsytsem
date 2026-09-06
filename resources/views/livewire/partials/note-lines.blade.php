{{-- Expects: $note with ->lines, ->net_total, ->tax_total, ->gross_total, ->currency --}}
<x-ui.card :title="__('erp.document.lines')" flush>
    <x-ui.table flush>
        <thead>
            <tr>
                <th class="w-10">#</th>
                <th>{{ __('erp.sales_invoice.line_description') }}</th>
                <th class="!text-end">{{ __('erp.document.tax') }}</th>
                <th class="!text-end">{{ __('erp.document.amount') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($note->lines as $line)
                <tr wire:key="note-line-{{ $line->id }}">
                    <td class="tabular-nums text-muted-foreground">{{ $line->line_no }}</td>
                    <td class="text-foreground">{{ $line->description ?? '-' }}</td>
                    <td class="text-end"><x-ui.money :amount="$line->tax_amount" muted /></td>
                    <td class="text-end"><x-ui.money :amount="$line->net_amount" /></td>
                </tr>
            @empty
                <tr><td colspan="4" class="!py-6 text-center text-muted-foreground">{{ __('erp.no_data') }}</td></tr>
            @endforelse
        </tbody>
    </x-ui.table>

    <div class="flex justify-end border-t border-border bg-surface-sunken px-5 py-4">
        <dl class="w-full max-w-xs space-y-2 text-[0.8125rem]">
            <div class="flex justify-between">
                <dt class="text-muted-foreground">{{ __('erp.document.subtotal') }}</dt>
                <dd><x-ui.money :amount="$note->net_total" /></dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-muted-foreground">{{ __('erp.document.tax') }}</dt>
                <dd><x-ui.money :amount="$note->tax_total" :muted="(float) $note->tax_total == 0" /></dd>
            </div>
            <div class="flex items-baseline justify-between border-t border-border-strong pt-2">
                <dt class="font-semibold">{{ __('erp.document.total') }}</dt>
                <dd><x-ui.money :amount="$note->gross_total" :currency="$note->currency" size="lg" /></dd>
            </div>
        </dl>
    </div>
</x-ui.card>
