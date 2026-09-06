@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
    'icon' => false,
])

@php
    $base = 'mizan-button inline-flex items-center justify-center gap-2 rounded-md font-medium whitespace-nowrap focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/40 focus-visible:ring-offset-1 focus-visible:ring-offset-background disabled:pointer-events-none disabled:opacity-50';

    $variants = [
        // The one blue button on a screen is the thing to do next.
        'primary' => 'mizan-button-primary text-white',
        'secondary' => 'border border-border-strong bg-card text-foreground shadow-elevation-xs hover:bg-surface-sunken',
        'ghost' => 'text-muted-foreground hover:bg-surface-sunken hover:text-foreground',
        'outline' => 'border border-border-strong bg-transparent text-foreground hover:bg-surface-sunken',
        'accent' => 'bg-[var(--brand-600)] text-white hover:bg-[var(--brand-700)]',
        'danger' => 'bg-[var(--danger)] text-white shadow-elevation-xs hover:bg-[var(--danger-strong)]',
        'success' => 'bg-[var(--success-color)] text-white shadow-elevation-xs hover:brightness-95',
        // Quiet destructive: for actions that must be reachable but never inviting.
        'danger-ghost' => 'border border-[color:var(--danger-line)] bg-card text-[var(--danger)] hover:bg-[var(--danger-muted)]',
        'link' => 'text-[var(--brand-600)] underline-offset-4 hover:underline',
    ];

    $sizes = $icon
        ? ['sm' => 'h-8 w-8', 'md' => 'h-9 w-9', 'lg' => 'h-10 w-10']
        : ['sm' => 'h-8 px-3 text-xs', 'md' => 'h-10 px-4 text-[0.8125rem]', 'lg' => 'h-11 px-5 text-sm'];

    $classes = implode(' ', [
        $base,
        $variants[$variant] ?? $variants['primary'],
        $sizes[$size] ?? $sizes['md'],
    ]);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
