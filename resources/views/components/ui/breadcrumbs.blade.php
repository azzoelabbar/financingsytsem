@props([
    // list<array{label: string, href?: string|null}> - the last entry renders as the current page.
    'items' => [],
])

@if ($items !== [])
    <nav aria-label="{{ __('erp.breadcrumb') }}" {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-1.5 text-xs text-muted-foreground']) }}>
        @foreach ($items as $i => $item)
            @if ($i > 0)
                <svg class="h-3 w-3 shrink-0 text-border-strong rtl:rotate-180" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M7.3 4.3a1 1 0 011.4 0l5 5a1 1 0 010 1.4l-5 5a1 1 0 01-1.4-1.4L11.6 10 7.3 5.7a1 1 0 010-1.4z" clip-rule="evenodd" />
                </svg>
            @endif

            @if (($item['href'] ?? null) && ! $loop->last)
                <a href="{{ $item['href'] }}" wire:navigate class="rounded-sm transition-colors hover:text-[var(--brand-600)]">{{ $item['label'] }}</a>
            @else
                <span @class(['font-medium text-foreground' => $loop->last]) @if ($loop->last) aria-current="page" @endif>{{ $item['label'] }}</span>
            @endif
        @endforeach
    </nav>
@endif
