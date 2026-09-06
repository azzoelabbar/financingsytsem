@props([
    'title',
    'description' => null,
    'eyebrow' => null,
    // list<array{label: string, href?: string|null}>
    'breadcrumbs' => [],
])

<div {{ $attributes->merge(['class' => 'mizan-page-header']) }}>
    @if ($breadcrumbs !== [])
        <x-ui.breadcrumbs :items="$breadcrumbs" class="mb-2" />
    @endif

    <div class="mizan-page-heading">
        <div class="min-w-0">
            @if ($eyebrow)
                <p class="erp-eyebrow mb-1">{{ $eyebrow }}</p>
            @endif

            <div class="flex flex-wrap items-center gap-x-3 gap-y-1.5">
                <h1 class="erp-page-title">{{ $title }}</h1>
                @isset($badges)
                    {{ $badges }}
                @endisset
            </div>

            @if ($description)
                <p class="mt-1.5 max-w-3xl text-[0.8125rem] leading-relaxed text-muted-foreground">{{ $description }}</p>
            @endif
        </div>

        @isset($actions)
            <div class="flex flex-wrap items-center gap-2">
                {{ $actions }}
            </div>
        @endisset
    </div>
</div>
