{{-- Compatibility shim: older pages include this partial. New pages use <x-ui.toolbar> directly. --}}
<x-ui.toolbar :summary="$summary ?? null" />
