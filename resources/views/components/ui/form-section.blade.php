@props([
    'title',
    'description' => null,
    // Field columns on desktop.
    'columns' => 2,
])

@php
    $grid = match ((int) $columns) {
        1 => 'grid-cols-1',
        3 => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3',
        default => 'grid-cols-1 sm:grid-cols-2',
    };
@endphp

{{--
    One business section of a form. The label column explains what the section is
    for; the field grid holds the inputs. Sections stack inside a single bordered
    form card so a document reads as one sheet rather than a pile of boxes.
--}}
<section {{ $attributes->class(['mizan-form-section border-b border-border last:border-b-0', 'mizan-form-section-wide' => (int) $columns === 1, 'lg:grid' => (int) $columns !== 1]) }}>
    <div class="mb-4 lg:mb-0">
        <h2 class="text-[0.875rem] font-semibold text-foreground">{{ $title }}</h2>
        @if ($description)
            <p class="mt-1 text-xs leading-relaxed text-muted-foreground">{{ $description }}</p>
        @endif
    </div>

    <div class="grid gap-x-5 gap-y-4 {{ $grid }}">
        {{ $slot }}
    </div>
</section>
