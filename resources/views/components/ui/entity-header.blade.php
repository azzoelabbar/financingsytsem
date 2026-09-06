@props([
    // Big identifying line: a document number, a customer name, an account code.
    'title',
    // What kind of thing this is (rendered above the title).
    'eyebrow' => null,
    // Secondary identity, e.g. the party on a document.
    'subtitle' => null,
    // Short code shown in a monogram tile; falls back to the first letter of the title.
    'code' => null,
    'status' => null,
    'breadcrumbs' => [],
    // Semantic tone for the monogram tile.
    'tone' => 'brand',
])

@php
    $monogram = $code !== null && $code !== ''
        ? mb_substr($code, 0, 2)
        : mb_substr($title, 0, 1);

    $toneClasses = match ($tone) {
        'success' => 'bg-[var(--success-muted)] text-[var(--success-color)] ring-[color:var(--success-line)]',
        'warning' => 'bg-[var(--warning-muted)] text-[var(--warning-color)] ring-[color:var(--warning-line)]',
        'danger' => 'bg-[var(--danger-muted)] text-[var(--danger)] ring-[color:var(--danger-line)]',
        'neutral' => 'bg-[var(--neutral-muted)] text-muted-foreground ring-[color:var(--neutral-line)]',
        default => 'bg-[var(--brand-50)] text-[var(--brand-700)] ring-[color:var(--info-line)]',
    };
@endphp

<div {{ $attributes->merge(['class' => 'mb-5']) }}>
    @if ($breadcrumbs !== [])
        <x-ui.breadcrumbs :items="$breadcrumbs" class="mb-2.5" />
    @endif

    <div class="mizan-entity">
        <div class="mizan-entity-top flex flex-wrap items-start justify-between gap-x-6 gap-y-4">
            <div class="flex min-w-0 items-start gap-3.5">
                <span class="mizan-monogram mt-0.5 flex shrink-0 items-center justify-center text-sm font-bold uppercase ring-1 ring-inset {{ $toneClasses }}" aria-hidden="true" dir="ltr">
                    {{ $monogram }}
                </span>

                <div class="min-w-0">
                    @if ($eyebrow)
                        <p class="erp-section-title">{{ $eyebrow }}</p>
                    @endif
                    <div class="mt-0.5 flex flex-wrap items-center gap-x-2.5 gap-y-1">
                        <h1 class="break-words text-xl font-semibold leading-tight tracking-tight text-foreground sm:text-2xl">{{ $title }}</h1>
                        @if ($status !== null)
                            <x-ui.status-badge :status="$status" size="md" />
                        @endif
                        @isset($badges)
                            {{ $badges }}
                        @endisset
                    </div>
                    @if ($subtitle)
                        <p class="mt-1 truncate text-[0.8125rem] text-muted-foreground">{{ $subtitle }}</p>
                    @endif
                </div>
            </div>

            @isset($actions)
                <div class="flex flex-wrap items-center gap-2">
                    {{ $actions }}
                </div>
            @endisset
        </div>

        @isset($metrics)
            {{-- Headline figures for the entity: balance, total, open amount, and so on. --}}
            <dl class="mizan-entity-metrics grid grid-cols-2 border-t border-border sm:grid-cols-3 lg:grid-cols-4">
                {{ $metrics }}
            </dl>
        @endisset
    </div>
</div>
