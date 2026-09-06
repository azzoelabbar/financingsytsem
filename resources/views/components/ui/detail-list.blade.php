@props([
    // list<array{label: string, value: string|null, mono?: bool, dir?: string|null, tone?: string|null}>
    // Rows with a null/'' value are dropped, so callers can pass optional fields freely.
    'rows' => [],
    // 2 renders label/value side by side; 1 stacks them (useful in narrow columns).
    'columns' => 2,
])

@php
    $visible = array_values(array_filter(
        $rows,
        fn (array $row): bool => ($row['value'] ?? null) !== null && ($row['value'] ?? '') !== '',
    ));
@endphp

<dl {{ $attributes->merge(['class' => 'divide-y divide-border']) }}>
    @forelse ($visible as $row)
        @php
            $tone = match ($row['tone'] ?? null) {
                'success' => 'text-[var(--success-color)]',
                'warning' => 'text-[var(--warning-color)]',
                'danger' => 'text-[var(--danger)]',
                default => 'text-foreground',
            };
        @endphp
        <div @class([
            'gap-3 py-2',
            'flex items-baseline justify-between' => $columns === 2,
        ])>
            <dt class="shrink-0 text-[0.8125rem] text-muted-foreground">{{ $row['label'] }}</dt>
            <dd @class([
                'text-[0.8125rem] font-medium '.$tone,
                'font-mono text-xs' => $row['mono'] ?? false,
                'text-end' => $columns === 2,
                'mt-0.5' => $columns !== 2,
            ]) @if ($row['dir'] ?? null) dir="{{ $row['dir'] }}" @endif>{{ $row['value'] }}</dd>
        </div>
    @empty
        <p class="py-2 text-[0.8125rem] text-muted-foreground">{{ __('erp.no_data') }}</p>
    @endforelse

    {{ $slot }}
</dl>
