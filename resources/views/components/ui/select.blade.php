@props([
    'label' => null,
    'error' => null,
    'hint' => null,
    'required' => false,
])

@php
    $selectClasses = 'erp-control appearance-none pe-9'.($error ? ' erp-control-invalid' : '');
@endphp

@if ($label === null && $error === null && $hint === null)
    <div class="relative">
        <select {{ $attributes->merge(['class' => $selectClasses]) }}>{{ $slot }}</select>
        <svg class="pointer-events-none absolute inset-y-0 end-3 my-auto h-4 w-4 text-muted-foreground" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 8l4 4 4-4" /></svg>
    </div>
@else
    <x-ui.field :label="$label" :hint="$hint" :error="$error" :required="$required" :for="$attributes->get('id')" :class="$attributes->get('class')">
        <div class="relative">
            <select {{ $attributes->except('class')->merge(['class' => $selectClasses]) }} @if ($error) aria-invalid="true" @endif>{{ $slot }}</select>
            <svg class="pointer-events-none absolute inset-y-0 end-3 my-auto h-4 w-4 text-muted-foreground" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 8l4 4 4-4" /></svg>
        </div>
    </x-ui.field>
@endif
