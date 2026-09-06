@props([
    'variant' => 'default',
    // 'sm' keeps table rows tight; 'md' is for headers.
    'size' => 'sm',
])

@php
    $variants = [
        'default' => 'bg-[var(--neutral-muted)] text-muted-foreground ring-[color:var(--neutral-line)]',
        'primary' => 'bg-[var(--brand-50)] text-[var(--brand-700)] ring-[color:var(--info-line)]',
        'success' => 'bg-[var(--success-muted)] text-[var(--success-color)] ring-[color:var(--success-line)]',
        'warning' => 'bg-[var(--warning-muted)] text-[var(--warning-color)] ring-[color:var(--warning-line)]',
        'danger' => 'bg-[var(--danger-muted)] text-[var(--danger)] ring-[color:var(--danger-line)]',
        'info' => 'bg-[var(--brand-50)] text-[var(--brand-700)] ring-[color:var(--info-line)]',
        'outline' => 'bg-card text-muted-foreground ring-[color:var(--border-strong)]',
        'solid' => 'bg-[var(--brand-600)] text-white ring-[color:var(--brand-700)]',
    ];

    $sizes = [
        'sm' => 'px-2 py-0.5 text-[0.6875rem]',
        'md' => 'px-2.5 py-1 text-xs',
    ];

    $classes = 'inline-flex items-center gap-1 whitespace-nowrap rounded-full font-medium ring-1 ring-inset '
        .($variants[$variant] ?? $variants['default']).' '.($sizes[$size] ?? $sizes['sm']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>
