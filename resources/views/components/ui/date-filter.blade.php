@props([
    'label' => null,
    // Livewire property to bind.
    'model' => 'asOf',
])

<label {{ $attributes->merge(['class' => 'flex items-center gap-2 text-[0.8125rem] text-muted-foreground']) }}>
    <span class="whitespace-nowrap">{{ $label ?? __('erp.report_date') }}</span>
    <input type="date" wire:model.live="{{ $model }}" class="erp-control w-auto" dir="ltr" />
</label>
