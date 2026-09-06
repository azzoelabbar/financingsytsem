@props([
    'label' => null,
    'error' => null,
    'hint' => null,
    'required' => false,
])

@php
    $inputClasses = 'erp-control'.($error ? ' erp-control-invalid' : '');
@endphp

@if ($label === null && $error === null && $hint === null)
    {{-- Bare control: the caller is providing its own <x-ui.field> wrapper. --}}
    <input {{ $attributes->merge(['class' => $inputClasses]) }} />
@else
    <x-ui.field :label="$label" :hint="$hint" :error="$error" :required="$required" :for="$attributes->get('id')" :class="$attributes->get('class')">
        <input {{ $attributes->except('class')->merge(['class' => $inputClasses]) }} @if ($error) aria-invalid="true" @endif />
    </x-ui.field>
@endif
