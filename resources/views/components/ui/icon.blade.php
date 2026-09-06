@props(['name' => 'arrow'])
@php
    $path = match ($name) {
        'search' => 'm21 21-5-5m2-6a8 8 0 1 1-16 0 8 8 0 0 1 16 0',
        'plus' => 'M12 5v14M5 12h14',
        'wallet' => 'M20 8V5a2 2 0 0 0-2-2H5a3 3 0 0 0 0 6h15v11H5a3 3 0 0 1-3-3V6m18 7h-5v4h5',
        'check' => 'm5 12 4 4L19 6',
        'document' => 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zm0 0v6h6M8 13h8m-8 4h5',
        'lock' => 'M7 11V7a5 5 0 0 1 10 0v4M6 11h12a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2m6 5v2',
        default => 'M5 12h14m-6-6 6 6-6 6',
    };
@endphp
<svg {{ $attributes->merge(['class' => 'h-5 w-5 shrink-0']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="{{ $path }}" stroke-linecap="round" stroke-linejoin="round" /></svg>
