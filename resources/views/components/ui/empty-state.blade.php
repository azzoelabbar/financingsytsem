@props([
    'title' => null,
    'message' => null,
    // 'panel' sits inside a card; 'page' is the standalone bordered block.
    'variant' => 'page',
])

<div {{ $attributes->merge(['class' => $variant === 'panel'
    ? 'flex flex-col items-center justify-center px-6 py-12 text-center'
    : 'flex flex-col items-center justify-center rounded-lg border border-dashed border-border-strong bg-card px-6 py-14 text-center']) }}>
    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-[var(--brand-50)] text-[var(--brand-600)] ring-1 ring-inset ring-[color:var(--info-line)]">
        @isset($icon)
            {{ $icon }}
        @else
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
        @endif
    </div>

    @if ($title)
        <h3 class="text-[0.9375rem] font-semibold text-foreground">{{ $title }}</h3>
    @endif

    <p class="mt-1.5 max-w-md text-[0.8125rem] leading-relaxed text-muted-foreground">
        {{ $message ?? __('erp.no_data') }}
    </p>

    @isset($actions)
        <div class="mt-5 flex flex-wrap items-center justify-center gap-2">
            {{ $actions }}
        </div>
    @endisset
</div>
