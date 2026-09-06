@props([
    'label',
    'value' => null,
    'hint' => null,
    // Semantic reading of the figure: null (neutral) | 'brand' | 'success' | 'warning' | 'danger'
    'tone' => null,
    // Optional caption rendered under the value, e.g. a comparison.
    'trend' => null,
    'trendDirection' => null, // 'up' | 'down' | null
    'href' => null,
])

@php
    $accent = match ($tone) {
        'brand', 'primary', 'info' => 'var(--brand-600)',
        'success' => 'var(--success-color)',
        'warning' => 'var(--warning-color)',
        'danger' => 'var(--danger)',
        default => 'var(--border-strong)',
    };

    $trendClass = match ($trendDirection) {
        'up' => 'text-[var(--success-color)]',
        'down' => 'text-[var(--danger)]',
        default => 'text-muted-foreground',
    };

    $tag = $href ? 'a' : 'div';
    $interactive = $href ? ' transition-all duration-200 hover:border-border-strong' : '';
@endphp

{{-- A 3px inline-start rule carries the semantic colour; the tile itself stays neutral. --}}
<{{ $tag }}
    @if ($href) href="{{ $href }}" wire:navigate @endif
    {{ $attributes->merge(['class' => 'mizan-stat block border border-border bg-card'.$interactive]) }}
    style="--stat-accent: {{ $accent }};"
>
    <div class="flex items-start justify-between gap-2">
        <p class="erp-kpi-label">{{ $label }}</p>
        @isset($icon)
            <span class="text-muted-foreground/70">{{ $icon }}</span>
        @endisset
    </div>

    @if ($value !== null)
        <p class="erp-kpi-value">{{ $value }}</p>
    @else
        <div class="mt-1">{{ $slot }}</div>
    @endif

    @if ($trend)
        <p class="mt-1.5 flex items-center gap-1 text-xs font-medium {{ $trendClass }}">
            @if ($trendDirection === 'up')
                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 17a1 1 0 01-1-1V6.4L5.7 9.7a1 1 0 01-1.4-1.4l5-5a1 1 0 011.4 0l5 5a1 1 0 01-1.4 1.4L11 6.4V16a1 1 0 01-1 1z" clip-rule="evenodd"/></svg>
            @elseif ($trendDirection === 'down')
                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v9.6l3.3-3.3a1 1 0 011.4 1.4l-5 5a1 1 0 01-1.4 0l-5-5a1 1 0 011.4-1.4L9 13.6V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
            @endif
            {{ $trend }}
        </p>
    @endif

    @if ($hint)
        <p class="mt-1 text-xs leading-relaxed text-muted-foreground">{{ $hint }}</p>
    @endif
</{{ $tag }}>
