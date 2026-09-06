@props([
    'label',
    'hint' => null,
])

{{-- One cell of an <x-ui.entity-header> metrics strip. Sits on the 1px grid gap, so it paints its own background. --}}
<div {{ $attributes->merge(['class' => 'bg-card px-5 py-3']) }}>
    <dt class="erp-kpi-label">{{ $label }}</dt>
    <dd class="mt-1 text-base font-semibold tabular-nums text-foreground">{{ $slot }}</dd>
    @if ($hint)
        <p class="mt-0.5 text-xs text-muted-foreground">{{ $hint }}</p>
    @endif
</div>
