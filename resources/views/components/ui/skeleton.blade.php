@props([
    'lines' => 3,
])

<div {{ $attributes->merge(['class' => 'space-y-3']) }} aria-hidden="true">
    @for ($i = 0; $i < $lines; $i++)
        <div @class([
            'h-4 animate-skeleton rounded-md bg-muted',
            'w-full' => $i % 3 !== 2,
            'w-3/4' => $i % 3 === 2,
        ])></div>
    @endfor
</div>
