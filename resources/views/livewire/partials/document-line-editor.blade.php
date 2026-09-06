{{--
    Line-item editor shared by the sales and purchase invoice drafts.

    Expects: $lines, $accountField (the per-line account property name),
    $accountLabel, and $accountHint.
--}}
@php($hasTaxCodes = isset($taxCodes))
<div class="mizan-line-editor space-y-3">
    {{-- Column headings, shown once, so each row stays a compact input strip. --}}
    <div @class([
        'mizan-line-head text-[0.6875rem] font-semibold text-muted-foreground',
        'mizan-line-with-tax' => $hasTaxCodes,
    ])>
        <span>{{ __('erp.form.line_number') }}</span>
        <span>{{ __('erp.sales_invoice.line_description') }}</span>
        <span>{{ $accountLabel }}</span>
        @if ($hasTaxCodes)<span>{{ __('erp.tax.tax_code') }}</span>@endif
        <span>{{ __('erp.sales_invoice.quantity') }}</span>
        <span>{{ __('erp.sales_invoice.unit_price') }}</span>
        <span class="sr-only">{{ __('erp.form.remove_line') }}</span>
    </div>

    @foreach ($lines as $i => $line)
        <div @class([
            'mizan-line-row',
            'mizan-line-with-tax' => $hasTaxCodes,
        ]) wire:key="line-{{ $i }}">
            <span class="mizan-line-number text-xs font-semibold tabular-nums text-muted-foreground">{{ $i + 1 }}</span>

            <label class="mizan-line-description">
                <span class="mizan-line-label">{{ __('erp.sales_invoice.line_description') }}</span>
                <input type="text" wire:model="lines.{{ $i }}.description" placeholder="{{ __('erp.sales_invoice.line_description') }}" class="erp-control {{ $errors->has("lines.$i.description") ? 'erp-control-invalid' : '' }}" />
                @if ($errors->has("lines.$i.description"))
                    <span class="mt-1 block text-xs text-[var(--danger)]">{{ $errors->first("lines.$i.description") }}</span>
                @endif
            </label>

            <label class="mizan-line-account">
                <span class="mizan-line-label">{{ $accountLabel }}</span>
                <input type="text" wire:model="lines.{{ $i }}.{{ $accountField }}" class="erp-control font-mono text-xs {{ $errors->has("lines.$i.$accountField") ? 'erp-control-invalid' : '' }}" dir="ltr" required />
                @if ($errors->has("lines.$i.$accountField"))
                    <span class="mt-1 block text-xs text-[var(--danger)]">{{ $errors->first("lines.$i.$accountField") }}</span>
                @endif
            </label>

            @if ($hasTaxCodes)
                <label class="mizan-line-tax">
                    <span class="mizan-line-label">{{ __('erp.tax.tax_code') }}</span>
                    <select wire:model="lines.{{ $i }}.tax_code" class="erp-control {{ $errors->has("lines.$i.tax_code") ? 'erp-control-invalid' : '' }}">
                        <option value="">{{ __('erp.tax.no_tax') }}</option>
                        @foreach ($taxCodes as $taxCode)
                            <option value="{{ $taxCode->code }}">{{ $taxCode->code }} — {{ $taxCode->name }}</option>
                        @endforeach
                    </select>
                    @if ($errors->has("lines.$i.tax_code"))
                        <span class="mt-1 block text-xs text-[var(--danger)]">{{ $errors->first("lines.$i.tax_code") }}</span>
                    @endif
                </label>
            @endif

            <label class="mizan-line-quantity">
                <span class="mizan-line-label">{{ __('erp.sales_invoice.quantity') }}</span>
                <input type="number" step="0.01" wire:model="lines.{{ $i }}.quantity" class="erp-control text-end {{ $errors->has("lines.$i.quantity") ? 'erp-control-invalid' : '' }}" dir="ltr" />
                @if ($errors->has("lines.$i.quantity"))
                    <span class="mt-1 block text-xs text-[var(--danger)]">{{ $errors->first("lines.$i.quantity") }}</span>
                @endif
            </label>

            <label class="mizan-line-price">
                <span class="mizan-line-label">{{ __('erp.sales_invoice.unit_price') }}</span>
                <input type="number" step="0.01" wire:model="lines.{{ $i }}.unit_price" class="erp-control text-end {{ $errors->has("lines.$i.unit_price") ? 'erp-control-invalid' : '' }}" dir="ltr" />
                @if ($errors->has("lines.$i.unit_price"))
                    <span class="mt-1 block text-xs text-[var(--danger)]">{{ $errors->first("lines.$i.unit_price") }}</span>
                @endif
            </label>

            <button
                type="button"
                wire:click="removeLine({{ $i }})"
                @disabled(count($lines) <= 1)
                class="mizan-line-remove flex h-9 w-9 items-center justify-center rounded-md border border-border text-muted-foreground transition-colors hover:border-[color:var(--danger-line)] hover:bg-[var(--danger-muted)] hover:text-[var(--danger)] disabled:pointer-events-none disabled:opacity-40"
                aria-label="{{ __('erp.form.remove_line') }}"
            >
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linecap="round" d="M6 6l8 8M14 6l-8 8" /></svg>
            </button>
        </div>
    @endforeach

    <button type="button" wire:click="addLine" class="inline-flex items-center gap-1.5 rounded-md border border-dashed border-border-strong px-3 py-2 text-[0.8125rem] font-medium text-[var(--brand-600)] transition-colors hover:border-[var(--brand-600)] hover:bg-[var(--brand-50)]">
        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 4a1 1 0 011 1v4h4a1 1 0 110 2h-4v4a1 1 0 11-2 0v-4H5a1 1 0 110-2h4V5a1 1 0 011-1z"/></svg>
        {{ __('erp.form.add_line') }}
    </button>

    <p class="text-xs text-muted-foreground">{{ $accountHint }}</p>
</div>
