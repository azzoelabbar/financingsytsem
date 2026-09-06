@props([
    'variant' => 'info',
    'title' => null,
])

@php
    $variants = [
        'info' => ['cls' => 'border-[color:var(--info-color)]/20 bg-[var(--info-muted)] text-[var(--info-color)]', 'icon' => 'M10 18a8 8 0 100-16 8 8 0 000 16zM11 9a1 1 0 10-2 0v4a1 1 0 102 0V9zm-1-4.5a1 1 0 100 2 1 1 0 000-2z'],
        'success' => ['cls' => 'border-[color:var(--success-color)]/20 bg-[var(--success-muted)] text-[var(--success-color)]', 'icon' => 'M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 011.4-1.4l3.3 3.3 6.8-6.8a1 1 0 011.4 0z'],
        'warning' => ['cls' => 'border-[color:var(--warning-color)]/20 bg-[var(--warning-muted)] text-[var(--warning-color)]', 'icon' => 'M8.5 3.6a1.7 1.7 0 013 0l6.3 11.2A1.7 1.7 0 0116.3 17H3.7a1.7 1.7 0 01-1.5-2.2L8.5 3.6zM10 7a1 1 0 00-1 1v3a1 1 0 102 0V8a1 1 0 00-1-1zm0 7.5a1 1 0 100-2 1 1 0 000 2z'],
        'danger' => ['cls' => 'border-[color:var(--danger)]/20 bg-[var(--danger-muted)] text-[var(--danger)]', 'icon' => 'M10 18a8 8 0 100-16 8 8 0 000 16zM8.7 7.3a1 1 0 00-1.4 1.4L8.6 10l-1.3 1.3a1 1 0 101.4 1.4L10 11.4l1.3 1.3a1 1 0 001.4-1.4L11.4 10l1.3-1.3a1 1 0 00-1.4-1.4L10 8.6 8.7 7.3z'],
    ];
    $v = $variants[$variant] ?? $variants['info'];
@endphp

<div {{ $attributes->merge(['class' => 'flex items-start gap-3 rounded-lg border px-4 py-3 text-sm '.$v['cls'], 'role' => 'alert']) }}>
    <svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="{{ $v['icon'] }}" clip-rule="evenodd" /></svg>
    <div class="min-w-0 flex-1">
        @if ($title)
            <p class="mb-0.5 font-semibold">{{ $title }}</p>
        @endif
        <div class="leading-relaxed">{{ $slot }}</div>
    </div>
</div>
