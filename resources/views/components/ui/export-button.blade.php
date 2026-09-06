@props([
    // Livewire action to call. Screens using ExportsToExcel keep the default.
    'action' => 'exportExcel',
    'variant' => 'secondary',
    'size' => 'md',
    'label' => null,
])

{{-- Downloads the screen's full result set as a .xlsx file. --}}
<x-ui.button
    :variant="$variant"
    :size="$size"
    wire:click="{{ $action }}"
    wire:loading.attr="disabled"
    wire:target="{{ $action }}"
    {{ $attributes }}
>
    <svg wire:loading.remove wire:target="{{ $action }}" class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M10 3v9m0 0l3.25-3.25M10 12L6.75 8.75M3.5 13.5v1.75A1.75 1.75 0 005.25 17h9.5a1.75 1.75 0 001.75-1.75V13.5" />
    </svg>
    <span wire:loading wire:target="{{ $action }}" class="h-4 w-4 animate-spin rounded-full border-2 border-muted border-t-[var(--brand-600)]"></span>
    {{ $label ?? __('erp.export.excel') }}
</x-ui.button>
