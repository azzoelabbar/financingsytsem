@props([
    // Livewire property bound to the search box. Pass null to hide the search field.
    'search' => 'search',
    'placeholder' => null,
    // Right-hand summary text, e.g. "24 results".
    'summary' => null,
])

{{--
    List-page control bar: search on the leading edge, filters in the middle,
    result summary on the trailing edge. One bordered band so the table below
    reads as the result of these controls.
--}}
<div {{ $attributes->merge(['class' => 'mizan-toolbar mb-4 flex flex-wrap items-center gap-3 border border-border bg-card']) }}>
    @if ($search)
        <div class="relative min-w-[12rem] flex-1 sm:max-w-xs">
            <svg class="pointer-events-none absolute inset-y-0 start-2.5 my-auto h-4 w-4 text-muted-foreground" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 3a6 6 0 104.472 10.03l3.249 3.248a.75.75 0 101.06-1.06l-3.248-3.249A6 6 0 009 3z" />
            </svg>
            <input
                type="search"
                wire:model.live.debounce.300ms="{{ $search }}"
                placeholder="{{ $placeholder ?? __('erp.search_placeholder') }}"
                aria-label="{{ $placeholder ?? __('erp.search_placeholder') }}"
                class="erp-control h-9 ps-8 pe-8"
            />
            <div wire:loading wire:target="{{ $search }}" class="absolute inset-y-0 end-2.5 my-auto h-4 w-4 animate-spin rounded-full border-2 border-muted border-t-[var(--brand-600)]"></div>
        </div>
    @endif

    @isset($filters)
        <div class="flex flex-wrap items-center gap-2">
            {{ $filters }}
        </div>
    @endisset

    <div class="ms-auto flex items-center gap-3">
        @if ($summary)
            <span class="hidden text-xs text-muted-foreground sm:inline">{{ $summary }}</span>
        @endif
        {{ $slot }}
    </div>
</div>
