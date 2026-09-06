@props([
    // list<array{key: string, label: string}> in lifecycle order.
    'steps' => [],
    // Index of the step the document has reached.
    'current' => 0,
    // Set when the document left the happy path (rejected, void).
    'failed' => null,
])

{{-- Where a document sits in its approval lifecycle, and what comes next. --}}
<ol {{ $attributes->merge(['class' => 'mb-5 flex flex-wrap items-center gap-x-1 gap-y-2 rounded-lg border border-border bg-card px-3 py-2.5']) }}>
    @foreach ($steps as $i => $step)
        @php
            $done = $i < $current;
            $active = $i === $current && $failed === null;
        @endphp
        <li class="flex items-center gap-1">
            <span @class([
                'flex items-center gap-1.5 rounded-md px-2.5 py-1 text-xs font-medium',
                'bg-[var(--success-muted)] text-[var(--success-color)]' => $done,
                'bg-[var(--brand-600)] text-white' => $active,
                'text-muted-foreground' => ! $done && ! $active,
            ])>
                @if ($done)
                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 011.4-1.4l3.3 3.3 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
                @endif
                {{ $step['label'] }}
            </span>
            @if (! $loop->last)
                <svg class="h-3 w-3 shrink-0 text-border-strong rtl:rotate-180" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.3 4.3a1 1 0 011.4 0l5 5a1 1 0 010 1.4l-5 5a1 1 0 01-1.4-1.4L11.6 10 7.3 5.7a1 1 0 010-1.4z" clip-rule="evenodd"/></svg>
            @endif
        </li>
    @endforeach

    @if ($failed !== null)
        <li class="ms-2">
            <span class="rounded-md bg-[var(--danger-muted)] px-2.5 py-1 text-xs font-semibold text-[var(--danger)]">{{ $failed }}</span>
        </li>
    @endif
</ol>
