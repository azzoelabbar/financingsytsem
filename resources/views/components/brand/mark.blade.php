@props([
    'alt' => '',
])

<img
    src="{{ asset('brand/mizan-mark.png') }}"
    alt="{{ $alt }}"
    width="512"
    height="512"
    @if($alt === '') aria-hidden="true" @endif
    {{ $attributes->merge(['class' => 'block shrink-0 object-contain']) }}
>
