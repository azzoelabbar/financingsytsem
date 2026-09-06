@props([
    'showTagline' => true,
    'inverted' => false,
])

<span {{ $attributes->class(['flex min-w-0 items-center gap-2.5']) }}>
    <x-brand.mark class="h-9 w-9" />
    <span class="flex min-w-0 flex-col gap-0.5">
        <span @class([
            'truncate text-[0.95rem] font-semibold leading-tight',
            'text-white' => $inverted,
            'text-foreground' => ! $inverted,
        ])>{{ __('erp.app_name') }}</span>
        @if($showTagline)
            <span @class([
                'truncate text-[0.67rem] leading-tight',
                'text-white/65' => $inverted,
                'text-muted-foreground' => ! $inverted,
            ])>{{ __('erp.app_tagline') }}</span>
        @endif
    </span>
</span>
