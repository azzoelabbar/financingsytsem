@props([
    'amount' => '0',
    'currency' => null,
    'negative' => false,
    'muted' => false,
    // 'sm' | 'base' | 'lg' | 'hero'
    'size' => 'base',
    // Renders a positive figure in the success colour (use for settled/received amounts).
    'positive' => false,
    // Force a semantic colour without changing the sign: 'danger' | 'warning' | 'success'.
    // Use for figures that are bad news while still being positive amounts (overdue, for example).
    'tone' => null,
])

@php
    $numeric = (float) $amount;
    $isNeg = $negative || $numeric < 0;
    $isZero = abs($numeric) < 0.0000001;
    $formatted = number_format(abs($numeric), 2);
    $sign = $isNeg ? '−' : '';

    $toneClass = match ($tone) {
        'danger' => 'text-[var(--danger)]',
        'warning' => 'text-[var(--warning-color)]',
        'success' => 'text-[var(--success-color)]',
        default => null,
    };

    $colorClass = $isNeg
        ? 'text-[var(--danger)]'
        : ($isZero || $muted
            ? 'text-muted-foreground'
            : ($toneClass ?? ($positive ? 'text-[var(--success-color)]' : 'text-foreground')));

    $sizeClass = match ($size) {
        'sm' => 'text-xs',
        'lg' => 'text-base font-semibold',
        'hero' => 'text-[1.75rem] font-semibold leading-tight tracking-tight',
        default => '',
    };

    $currencyClass = $size === 'hero' ? 'ms-1.5 text-sm font-normal text-muted-foreground' : 'ms-1 text-[0.6875rem] font-normal text-muted-foreground';
@endphp

<span {{ $attributes->merge(['class' => trim('inline-flex items-baseline whitespace-nowrap tabular-nums font-medium '.$colorClass.' '.$sizeClass)]) }} dir="ltr" data-negative="{{ $isNeg ? 'true' : 'false' }}">
    {{ $sign }}{{ $formatted }}@if ($currency)<span class="{{ $currencyClass }}">{{ $currency }}</span>@endif
</span>
