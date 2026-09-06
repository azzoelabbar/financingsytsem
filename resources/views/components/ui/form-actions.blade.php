@props([
    // Explanatory text on the leading edge, e.g. what saving will do.
    'note' => null,
])

{{-- Sticky action bar: the primary action stays reachable on long forms. --}}
<div {{ $attributes->merge(['class' => 'mizan-form-actions sticky bottom-0 z-10 flex flex-wrap items-center justify-between gap-3 border-t border-border']) }}>
    <p class="text-xs text-muted-foreground">{{ $note }}</p>
    <div class="flex flex-wrap items-center gap-2">
        {{ $slot }}
    </div>
</div>
