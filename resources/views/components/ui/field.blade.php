@props([
    'label' => null,
    'for' => null,
    'hint' => null,
    'error' => null,
    'required' => false,
    // Span the full width of the section grid.
    'wide' => false,
])

{{--
    Label + control + message wrapper. Callers put any control inside (input,
    select, a Livewire widget), so validation and hint placement stay identical
    everywhere without wrapping every control type in its own component.
--}}
<div {{ $attributes->merge(['class' => 'min-w-0 space-y-1.5'.($wide ? ' sm:col-span-2 lg:col-span-3' : '')]) }}>
    @if ($label)
        <label @if ($for) for="{{ $for }}" @endif class="erp-field-label">
            {{ $label }}
            @if ($required)
                <span class="text-[var(--danger)]" aria-hidden="true">*</span>
            @endif
        </label>
    @endif

    {{ $slot }}

    @if ($error)
        <p class="flex items-start gap-1 text-xs text-[var(--danger)]">
            <svg class="mt-px h-3.5 w-3.5 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9 9a1 1 0 112 0v4a1 1 0 11-2 0V9zm1-5.5a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"/></svg>
            {{ $error }}
        </p>
    @elseif ($hint)
        <p class="text-xs leading-relaxed text-muted-foreground">{{ $hint }}</p>
    @endif
</div>
