@props([
    'title' => null,
    'description' => null,
    'padding' => true,
    'flush' => false,
    // Tints the header band: null | 'brand' | 'success' | 'warning' | 'danger'
    'tone' => null,
])

@php
    $headerTone = match ($tone) {
        'brand' => 'bg-[var(--brand-50)] border-[color:var(--info-line)]',
        'success' => 'bg-[var(--success-muted)] border-[color:var(--success-line)]',
        'warning' => 'bg-[var(--warning-muted)] border-[color:var(--warning-line)]',
        'danger' => 'bg-[var(--danger-muted)] border-[color:var(--danger-line)]',
        default => 'bg-card border-border',
    };
@endphp

<div {{ $attributes->merge(['class' => 'mizan-card flex min-w-0 flex-col overflow-hidden bg-card text-card-foreground']) }}>
    @if ($title || $description || isset($header))
        <div class="mizan-card-header flex flex-wrap items-center justify-between gap-3 border-b px-5 {{ $headerTone }}">
            @isset($header)
                {{ $header }}
            @else
                <div class="min-w-0">
                    @if ($title)
                        <h3 class="text-[0.875rem] font-semibold text-foreground">{{ $title }}</h3>
                    @endif
                    @if ($description)
                        <p class="mt-0.5 text-xs leading-relaxed text-muted-foreground">{{ $description }}</p>
                    @endif
                </div>
            @endisset
            @isset($actions)
                <div class="flex shrink-0 items-center gap-2">{{ $actions }}</div>
            @endisset
        </div>
    @endif

    <div @class(['flex-1', 'px-5 py-4' => $padding && ! $flush])>
        {{ $slot }}
    </div>

    @isset($footer)
        <div class="border-t border-border bg-surface-sunken px-5 py-3">{{ $footer }}</div>
    @endisset
</div>
