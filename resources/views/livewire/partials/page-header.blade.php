{{-- Compatibility shim: older pages include this partial. New pages use <x-ui.page-header> directly. --}}
<x-ui.page-header :title="$title" :description="$description ?? null">
    @if (! empty($action))
        <x-slot:actions>{!! $action !!}</x-slot:actions>
    @endif
</x-ui.page-header>
