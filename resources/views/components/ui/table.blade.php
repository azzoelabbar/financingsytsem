@props([
    // Drop the outer border/rounding when the table already sits inside a card.
    'flush' => false,
    // 'compact' for ledger-style listings, 'default' for everything else.
    'density' => 'default',
])

@php
    $rowPad = $density === 'compact' ? '[&_tbody_td]:py-1.5' : '[&_tbody_td]:py-2.5';
@endphp

<div {{ $attributes->merge(['class' => ($flush ? '' : 'mizan-table-shell rounded-lg border border-border bg-card ').'min-w-0 overflow-x-auto', 'aria-label' => __('erp.mizan.data_table')]) }} tabindex="0" role="region">
    <table class="mizan-table w-full min-w-full border-separate border-spacing-0 text-[0.8125rem]
        [&_thead_th]:sticky [&_thead_th]:top-0 [&_thead_th]:z-[1] [&_thead_th]:whitespace-nowrap [&_thead_th]:bg-surface-sunken [&_thead_th]:px-4 [&_thead_th]:py-2 [&_thead_th]:text-start [&_thead_th]:text-[0.6875rem] [&_thead_th]:font-semibold [&_thead_th]:uppercase [&_thead_th]:tracking-[0.06em] [&_thead_th]:text-muted-foreground [&_thead_th]:border-b [&_thead_th]:border-border
        [&_tbody_td]:border-b [&_tbody_td]:border-border [&_tbody_td]:px-4 {{ $rowPad }} [&_tbody_td]:align-middle
        [&_tbody_tr:last-child_td]:border-b-0 [&_tbody_tr]:transition-colors [&_tbody_tr:hover]:bg-[var(--brand-50)]
        [&_tfoot_td]:border-t [&_tfoot_td]:border-border-strong [&_tfoot_td]:bg-surface-sunken [&_tfoot_td]:px-4 [&_tfoot_td]:py-2.5 [&_tfoot_td]:font-semibold">
        {{ $slot }}
    </table>
</div>
