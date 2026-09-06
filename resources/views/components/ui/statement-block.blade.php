@props([
    'title' => null,
])

{{--
    A financial statement body: section bands, indented line rows, and a
    subtotal rule. Used by the balance sheet, P&L, OCI and cash-flow pages so
    every statement in the product reads the same way.
--}}
<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-lg border border-border bg-card']) }}>
    @if ($title)
        <div class="border-b border-border bg-[var(--brand-50)] px-5 py-3">
            <h2 class="text-[0.875rem] font-semibold text-[var(--brand-900)]">{{ $title }}</h2>
        </div>
    @endif
    {{ $slot }}
</div>
