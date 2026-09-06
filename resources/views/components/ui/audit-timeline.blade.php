@props([
    // list<array{label:string, actor?:?string, at?:mixed, done?:bool, tone?:string}>
    'events' => [],
])

@php
    $events = collect($events)->filter(fn ($e) => ($e['done'] ?? true))->values();
@endphp

@if ($events->isEmpty())
    <p class="text-sm text-muted-foreground">{{ __('erp.audit.empty') }}</p>
@else
    <ol class="relative space-y-5 ps-6">
        {{-- vertical rail; sits on the start edge so it mirrors correctly in RTL --}}
        <span class="absolute inset-y-1 start-[5px] w-px bg-border" aria-hidden="true"></span>
        @foreach ($events as $event)
            @php
                $tone = $event['tone'] ?? 'default';
                $dot = match ($tone) {
                    'success' => 'bg-[var(--success-color)]',
                    'danger' => 'bg-[var(--danger)]',
                    'warning' => 'bg-[var(--warning-color)]',
                    default => 'bg-muted-foreground',
                };
                $at = $event['at'] ?? null;
            @endphp
            <li class="relative">
                <span class="absolute -start-6 top-1 flex h-2.5 w-2.5 items-center justify-center rounded-full {{ $dot }} ring-2 ring-card" aria-hidden="true"></span>
                <div class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-0.5">
                    <span class="text-sm font-medium text-foreground">{{ $event['label'] }}</span>
                    @if ($at)
                        <time class="font-mono text-xs text-muted-foreground" dir="ltr">{{ $at instanceof \Illuminate\Support\Carbon ? $at->format('Y-m-d H:i') : $at }}</time>
                    @endif
                </div>
                @if (! empty($event['actor']))
                    <p class="mt-0.5 text-xs text-muted-foreground">{{ __('erp.audit.by') }} {{ $event['actor'] }}</p>
                @endif
            </li>
        @endforeach
    </ol>
@endif
