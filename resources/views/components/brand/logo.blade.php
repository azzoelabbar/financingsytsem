@props([
    'alt' => null,
])

<picture {{ $attributes->only('class') }}>
    <source srcset="{{ asset('brand/mizan-logo.webp') }}" type="image/webp">
    <img
        src="{{ asset('brand/mizan-logo.png') }}"
        alt="{{ $alt ?? __('erp.app_name') }}"
        width="1000"
        height="1000"
        {{ $attributes->except('class')->merge(['class' => 'h-auto w-full']) }}
    >
</picture>
